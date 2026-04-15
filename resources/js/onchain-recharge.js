import { BrowserProvider, Contract, parseUnits } from 'ethers';

const connectWalletButton = document.getElementById('connect-wallet-btn');
const homeOnchainEntry = document.getElementById('home-onchain-entry');
const fromAddressInput = document.getElementById('from_address');
const chainIdInput = document.getElementById('chain_id');
const feedbackNode = document.getElementById('wallet-connect-feedback');
const payDirectButton = document.getElementById('pay-direct-btn');
const payFeedbackNode = document.getElementById('pay-feedback');
const paymentAmountInput = document.getElementById('payment_amount');
const txHashInput = document.getElementById('tx_hash');
const assetSelect = document.getElementById('asset_code');
const toAddressDisplay = document.getElementById('to_address_display');
const receiverAddressPreview = document.getElementById('receiver-address-preview');
const onchainRechargeForm = document.querySelector('[data-onchain-recharge-form]');
let homePayInFlight = false;
let pagePayInFlight = false;

const getSelectedReceiverAddress = () => {
  if (!assetSelect) {
    return '';
  }

  const selected = assetSelect.options[assetSelect.selectedIndex];
  return selected?.dataset.address ?? '';
};

const syncSelectedReceiverAddress = () => {
  const address = getSelectedReceiverAddress();
  if (toAddressDisplay) {
    toAddressDisplay.value = address;
  }
  if (receiverAddressPreview) {
    receiverAddressPreview.textContent = address;
  }
};

if (assetSelect) {
  assetSelect.addEventListener('change', syncSelectedReceiverAddress);
  syncSelectedReceiverAddress();
}

const setFeedback = (node, text) => {
  if (!node) {
    return;
  }

  node.classList.remove('hidden');
  node.textContent = text;
};

const withTimeout = async (promise, timeoutMs = 20000) => {
  let timer = null;
  try {
    return await Promise.race([
      promise,
      new Promise((_, reject) => {
        timer = window.setTimeout(() => reject(new Error('wallet_request_timeout')), timeoutMs);
      }),
    ]);
  } finally {
    if (timer !== null) {
      window.clearTimeout(timer);
    }
  }
};

const asHexChainId = (chainIdText) => `0x${BigInt(chainIdText).toString(16)}`;

const ensureWalletChain = async (provider, expectedChainIdText) => {
  if (!expectedChainIdText) {
    return;
  }

  const network = await provider.getNetwork();
  if (network.chainId.toString() === expectedChainIdText) {
    return;
  }

  try {
    await provider.send('wallet_switchEthereumChain', [{ chainId: asHexChainId(expectedChainIdText) }]);
  } catch (error) {
    throw new Error('switch_chain_failed');
  }
};

const getReadableWalletError = (error) => {
  const code = error && typeof error === 'object' && 'code' in error ? Number(error.code) : null;
  const message = error instanceof Error ? error.message : '';

  if (code === 4001) {
    return '你已取消钱包确认';
  }
  if (message === 'wallet_provider_missing') {
    return '未检测到钱包';
  }
  if (message === 'switch_chain_failed') {
    return '请先切换到 BSC 再重试';
  }
  if (code === -32000 || /insufficient funds|gas/i.test(message)) {
    return 'Gas 余额不足';
  }
  if (message === 'wallet_request_timeout') {
    return '钱包响应超时，请重试';
  }

  return '付款失败，请重试';
};

const connectWallet = async () => {
  if (!window.ethereum) {
    throw new Error('wallet_provider_missing');
  }

  const provider = new BrowserProvider(window.ethereum);
  const accounts = await withTimeout(provider.send('eth_accounts', []));
  if (!Array.isArray(accounts) || accounts.length === 0) {
    await withTimeout(provider.send('eth_requestAccounts', []));
  }
  const signer = await provider.getSigner();
  const network = await provider.getNetwork();
  const address = await signer.getAddress();

  if (fromAddressInput) {
    fromAddressInput.value = address;
  }
  if (chainIdInput) {
    chainIdInput.value = network.chainId.toString();
  }

  return { provider, signer, network, address };
};

if (homeOnchainEntry) {
  homeOnchainEntry.addEventListener('click', async (event) => {
    if (homePayInFlight) {
      event.preventDefault();
      return;
    }

    if (!window.ethereum) {
      return;
    }

    const tokenAddress = homeOnchainEntry.dataset.tokenAddress ?? '';
    const toAddress = homeOnchainEntry.dataset.toAddress ?? '';
    const amountText = homeOnchainEntry.dataset.paymentAmount ?? '10';
    const requiredChainId = homeOnchainEntry.dataset.chainId ?? '';
    const amount = Number(amountText);

    if (!tokenAddress || !toAddress || !Number.isFinite(amount) || amount <= 0) {
      return;
    }

    event.preventDefault();
    homePayInFlight = true;
    let redirected = false;

    const originalLabel = homeOnchainEntry.textContent;
    homeOnchainEntry.classList.add('pointer-events-none', 'opacity-70');
    homeOnchainEntry.textContent = '付款处理中...';

    try {
      const { provider, signer, address } = await connectWallet();
      await ensureWalletChain(provider, requiredChainId);
      const network = await provider.getNetwork();
      const token = new Contract(
        tokenAddress,
        [
          'function decimals() view returns (uint8)',
          'function transfer(address to, uint256 value) returns (bool)',
        ],
        signer,
      );

      let decimals = 18;
      try {
        decimals = Number(await token.decimals());
      } catch (error) {
        console.warn('read token decimals failed, fallback to 18', error);
      }

      const txValue = parseUnits(amountText, decimals);
      const tx = await token.transfer(toAddress, txValue);

      const url = new URL('/recharge/onchain', window.location.origin);
      url.searchParams.set('from_address', address);
      url.searchParams.set('chain_id', network.chainId.toString());
      url.searchParams.set('tx_hash', tx.hash);
      url.searchParams.set('payment_amount', amountText);

      const assetCode = homeOnchainEntry.dataset.assetCode ?? '';
      if (assetCode !== '') {
        url.searchParams.set('asset_code', assetCode);
      }

      redirected = true;
      window.location.href = url.toString();
    } catch (error) {
      console.error(error);
      homeOnchainEntry.textContent = getReadableWalletError(error);
      window.setTimeout(() => {
        homeOnchainEntry.classList.remove('pointer-events-none', 'opacity-70');
        homeOnchainEntry.textContent = originalLabel ?? '直接付款（链上充值）';
      }, 1800);
    } finally {
      if (!redirected) {
        homePayInFlight = false;
        homeOnchainEntry.classList.remove('pointer-events-none', 'opacity-70');
      }
    }
  });
}

if (connectWalletButton && fromAddressInput) {
  connectWalletButton.addEventListener('click', async () => {
    try {
      await connectWallet();
      setFeedback(feedbackNode, '钱包地址已填充');
    } catch (error) {
      setFeedback(feedbackNode, '连接钱包失败，请重试');
      console.error(error);
    }
  });
}

if (payDirectButton) {
  payDirectButton.addEventListener('click', async () => {
    if (pagePayInFlight) {
      return;
    }

    const tokenAddress = onchainRechargeForm?.dataset.tokenAddress ?? '';
    const toAddress = getSelectedReceiverAddress();
    const amountText = paymentAmountInput?.value?.trim() ?? '';
    const amount = Number(amountText);

    if (!tokenAddress) {
      setFeedback(payFeedbackNode, '未配置 Token 合约地址');
      return;
    }
    try {
      pagePayInFlight = true;
      if (!toAddress) {
        throw new Error('receiver_address_missing');
      }
      if (!Number.isFinite(amount) || amount <= 0) {
        throw new Error('payment_amount_invalid');
      }

      payDirectButton.classList.add('pointer-events-none', 'opacity-70');
      payDirectButton.textContent = '付款处理中...';

      const { provider, signer } = await connectWallet();
      await ensureWalletChain(provider, chainIdInput?.value?.trim() ?? '');
      const token = new Contract(
        tokenAddress,
        [
          'function decimals() view returns (uint8)',
          'function transfer(address to, uint256 value) returns (bool)',
        ],
        signer,
      );

      let decimals = 18;
      try {
        decimals = Number(await token.decimals());
      } catch (error) {
        console.warn('read token decimals failed, fallback to 18', error);
      }

      const txValue = parseUnits(amountText, decimals);
      const tx = await withTimeout(token.transfer(toAddress, txValue));
      await withTimeout(tx.wait());

      if (txHashInput) {
        txHashInput.value = tx.hash;
      }
      setFeedback(payFeedbackNode, '付款交易已上链，交易哈希已自动填充，请提交申请。');
    } catch (error) {
      if (error instanceof Error) {
        if (error.message === 'wallet_provider_missing') {
          setFeedback(payFeedbackNode, '未检测到可用钱包 Provider');
        } else if (error.message === 'receiver_address_missing') {
          setFeedback(payFeedbackNode, '未找到收款地址，请联系管理员');
        } else if (error.message === 'payment_amount_invalid') {
          setFeedback(payFeedbackNode, '请输入正确的付款金额');
        } else if (error.message === 'switch_chain_failed') {
          setFeedback(payFeedbackNode, '请先切换到 BSC 再重试');
        } else {
          setFeedback(payFeedbackNode, getReadableWalletError(error));
        }
      } else {
        setFeedback(payFeedbackNode, '付款失败，请重试');
      }
      console.error(error);
    } finally {
      pagePayInFlight = false;
      payDirectButton.classList.remove('pointer-events-none', 'opacity-70');
      payDirectButton.textContent = '拉起钱包直接付款（USDT）';
    }
  });
}
