import { BrowserProvider, Contract, parseUnits } from 'ethers';
import EthereumProvider from '@walletconnect/ethereum-provider';

const connectWalletButton = document.getElementById('connect-wallet-btn');
const connectWalletConnectButton = document.getElementById('connect-walletconnect-btn');
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

let connectedWallet = null;
let walletConnectProvider = null;

const getCsrfToken = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

const reportClientEvent = async (stage, error, details = {}) => {
  const code = error && typeof error === 'object' && 'code' in error ? String(error.code) : '';
  const message = error instanceof Error ? error.message : String(error ?? 'unknown_error');

  try {
    await fetch('/recharge/onchain/client-events', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': getCsrfToken(),
      },
      body: JSON.stringify({
        stage,
        provider: connectedWallet?.source ?? details.provider ?? null,
        code: code || null,
        message: message || null,
        details,
        path: window.location.pathname,
        chain_id: chainIdInput?.value ?? null,
      }),
      keepalive: true,
    });
  } catch (logError) {
    console.warn('report client event failed', logError);
  }
};

const setFeedback = (node, text) => {
  if (!node) {
    return;
  }

  node.classList.remove('hidden');
  node.textContent = text;
};

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

const bindWalletContext = async (provider, signer, source) => {
  const network = await provider.getNetwork();
  const address = await signer.getAddress();

  connectedWallet = {
    provider,
    signer,
    network,
    source,
  };

  if (fromAddressInput) {
    fromAddressInput.value = address;
  }

  if (chainIdInput) {
    chainIdInput.value = network.chainId.toString();
  }

  if (payDirectButton) {
    payDirectButton.disabled = false;
    payDirectButton.classList.remove('opacity-60', 'pointer-events-none');
  }
};

const connectInjectedWallet = async () => {
  if (!window.ethereum) {
    throw new Error('wallet_provider_missing');
  }

  const provider = new BrowserProvider(window.ethereum);
  await provider.send('eth_requestAccounts', []);
  const signer = await provider.getSigner();
  await bindWalletContext(provider, signer, 'injected');

  return connectedWallet;
};

const connectWalletConnect = async () => {
  const projectId = onchainRechargeForm?.dataset.walletconnectProjectId ?? '';
  if (!projectId) {
    throw new Error('walletconnect_project_id_missing');
  }

  const chainId = Number(chainIdInput?.value ?? onchainRechargeForm?.dataset.chainId ?? '56');
  walletConnectProvider = await EthereumProvider.init({
    projectId,
    chains: [chainId],
    optionalChains: [chainId],
    showQrModal: true,
  });

  await walletConnectProvider.enable();
  const provider = new BrowserProvider(walletConnectProvider);
  const signer = await provider.getSigner();
  await bindWalletContext(provider, signer, 'walletconnect');

  return connectedWallet;
};

const mapConnectErrorMessage = (error) => {
  if (!(error instanceof Error)) {
    return '连接钱包失败，请重试';
  }

  if (error.message === 'wallet_provider_missing') {
    return '未检测到可用钱包 Provider';
  }

  if (error.message === 'walletconnect_project_id_missing') {
    return '缺少 WalletConnect Project ID 配置';
  }

  if ((error && typeof error === 'object' && 'code' in error && Number(error.code) === 4001) || /user rejected/i.test(error.message)) {
    return '你已取消钱包连接';
  }

  return '连接钱包失败，请重试';
};

const ensureConnectedWallet = async () => {
  if (!connectedWallet) {
    throw new Error('wallet_not_connected');
  }

  const network = await connectedWallet.provider.getNetwork();
  connectedWallet.network = network;

  if (chainIdInput && chainIdInput.value.trim() !== '' && network.chainId.toString() !== chainIdInput.value.trim()) {
    throw new Error('wallet_chain_mismatch');
  }

  return connectedWallet;
};

const mapPayErrorMessage = (error) => {
  if (!(error instanceof Error)) {
    return '付款失败，请重试';
  }

  if (error.message === 'wallet_not_connected') {
    return '请先点击“连接钱包”';
  }

  if (error.message === 'wallet_chain_mismatch') {
    return '钱包链与页面链ID不一致，请切换后重试';
  }

  if (error.message === 'receiver_address_missing') {
    return '未找到收款地址，请联系管理员';
  }

  if (error.message === 'payment_amount_invalid') {
    return '请输入正确的付款金额';
  }

  if ((error && typeof error === 'object' && 'code' in error && Number(error.code) === 4001) || /user rejected/i.test(error.message)) {
    return '你已取消付款';
  }

  if (/insufficient funds|gas/i.test(error.message)) {
    return 'Gas 余额不足';
  }

  return '付款失败，请重试';
};

if (connectWalletButton && fromAddressInput) {
  connectWalletButton.addEventListener('click', async () => {
    try {
      await connectInjectedWallet();
      setFeedback(feedbackNode, '钱包已连接（Injected）');
    } catch (error) {
      setFeedback(feedbackNode, mapConnectErrorMessage(error));
      console.error(error);
      await reportClientEvent('connect_injected_failed', error);
    }
  });
}

if (connectWalletConnectButton) {
  connectWalletConnectButton.addEventListener('click', async () => {
    try {
      await connectWalletConnect();
      setFeedback(feedbackNode, '钱包已连接（WalletConnect）');
    } catch (error) {
      setFeedback(feedbackNode, mapConnectErrorMessage(error));
      console.error(error);
      await reportClientEvent('connect_walletconnect_failed', error);
    }
  });
}

if (payDirectButton) {
  payDirectButton.disabled = true;
  payDirectButton.classList.add('opacity-60', 'pointer-events-none');

  payDirectButton.addEventListener('click', async () => {
    const tokenAddress = onchainRechargeForm?.dataset.tokenAddress ?? '';
    const toAddress = getSelectedReceiverAddress();
    const amountText = paymentAmountInput?.value?.trim() ?? '';
    const amount = Number(amountText);

    if (!tokenAddress) {
      setFeedback(payFeedbackNode, '未配置 Token 合约地址');
      return;
    }

    try {
      if (!toAddress) {
        throw new Error('receiver_address_missing');
      }
      if (!Number.isFinite(amount) || amount <= 0) {
        throw new Error('payment_amount_invalid');
      }

      payDirectButton.classList.add('pointer-events-none', 'opacity-70');
      payDirectButton.textContent = '付款处理中...';

      const wallet = await ensureConnectedWallet();
      const token = new Contract(
        tokenAddress,
        [
          'function decimals() view returns (uint8)',
          'function transfer(address to, uint256 value) returns (bool)',
        ],
        wallet.signer,
      );

      let decimals = 18;
      try {
        decimals = Number(await token.decimals());
      } catch (error) {
        console.warn('read token decimals failed, fallback to 18', error);
      }

      const txValue = parseUnits(amountText, decimals);
      const tx = await token.transfer(toAddress, txValue);
      await tx.wait();

      if (txHashInput) {
        txHashInput.value = tx.hash;
      }

      setFeedback(payFeedbackNode, '付款交易已上链，交易哈希已自动填充，请提交申请。');
    } catch (error) {
      setFeedback(payFeedbackNode, mapPayErrorMessage(error));
      console.error(error);
      await reportClientEvent('pay_failed', error, {
        to_address: toAddress,
        amount: amountText,
      });
    } finally {
      payDirectButton.classList.remove('pointer-events-none', 'opacity-70');
      payDirectButton.textContent = '拉起钱包直接付款（USDT）';
    }
  });
}

if (homeOnchainEntry) {
  homeOnchainEntry.addEventListener('click', async (event) => {
    if (!window.ethereum) {
      return;
    }

    const tokenAddress = homeOnchainEntry.dataset.tokenAddress ?? '';
    const toAddress = homeOnchainEntry.dataset.toAddress ?? '';
    const amountText = homeOnchainEntry.dataset.paymentAmount ?? '10';
    const amount = Number(amountText);

    if (!tokenAddress || !toAddress || !Number.isFinite(amount) || amount <= 0) {
      return;
    }

    event.preventDefault();

    const originalLabel = homeOnchainEntry.textContent;
    homeOnchainEntry.classList.add('pointer-events-none', 'opacity-70');
    homeOnchainEntry.textContent = '付款处理中...';

    try {
      const wallet = await connectInjectedWallet();
      const token = new Contract(
        tokenAddress,
        [
          'function decimals() view returns (uint8)',
          'function transfer(address to, uint256 value) returns (bool)',
        ],
        wallet.signer,
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
      url.searchParams.set('from_address', fromAddressInput?.value ?? '');
      url.searchParams.set('chain_id', wallet.network.chainId.toString());
      url.searchParams.set('tx_hash', tx.hash);
      url.searchParams.set('payment_amount', amountText);

      const assetCode = homeOnchainEntry.dataset.assetCode ?? '';
      if (assetCode !== '') {
        url.searchParams.set('asset_code', assetCode);
      }

      window.location.href = url.toString();
    } catch (error) {
      console.error(error);
      homeOnchainEntry.textContent = mapPayErrorMessage(error);
      await reportClientEvent('home_quick_pay_failed', error, {
        amount: amountText,
      });

      window.setTimeout(() => {
        homeOnchainEntry.classList.remove('pointer-events-none', 'opacity-70');
        homeOnchainEntry.textContent = originalLabel ?? '直接付款（链上充值）';
      }, 1800);
    }
  });
}
