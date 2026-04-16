import { BrowserProvider, Contract, Interface, parseUnits } from 'ethers';

const homeOnchainEntry = document.getElementById('home-onchain-entry');
const homeQuickPayPanel = document.getElementById('home-quick-pay-panel');
const homePayConfirmButton = document.getElementById('home-pay-confirm-btn');
const homePaymentAmountInput = document.getElementById('home-payment-amount');
const homePayFeedbackNode = document.getElementById('home-pay-feedback');
const homeSelectedAssetNode = document.getElementById('home-selected-asset');
const homeIsGuestInput = document.getElementById('home-is-guest');
const homeActivateModal = document.getElementById('home-activate-modal');
const fromAddressInput = document.getElementById('from_address');
const chainIdInput = document.getElementById('chain_id');
const feedbackNode = document.getElementById('wallet-connect-feedback');
const payDirectButton = document.getElementById('pay-direct-btn');
const payFeedbackNode = document.getElementById('pay-feedback');
const paymentAmountInput = document.getElementById('payment_amount');
const txHashInput = document.getElementById('tx_hash');
const assetSelect = document.getElementById('asset_code');
const assetQuickPicker = document.getElementById('asset-quick-picker');
const toAddressDisplay = document.getElementById('to_address_display');
const receiverAddressPreview = document.getElementById('receiver-address-preview');
const onchainRechargeForm = document.querySelector('[data-onchain-recharge-form]');

let connectedWallet = null;
let walletConnectProvider = null;
let homeSelectedAsset = null;
let walletConnectProviderClass = null;

const getWalletConnectProviderClass = async () => {
  if (walletConnectProviderClass) {
    return walletConnectProviderClass;
  }

  const module = await import('@walletconnect/ethereum-provider');
  walletConnectProviderClass = module.default;

  return walletConnectProviderClass;
};

const requireGuestActivationBeforeHomePay = () => {
  const isGuest = homeIsGuestInput?.value === '1';
  if (!isGuest) {
    return false;
  }

  homeActivateModal?.showModal();
  return true;
};

const getCsrfToken = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

const reportClientEvent = async (stage, error, details = {}) => {
  const code = error && typeof error === 'object' && 'code' in error ? String(error.code) : '';
  let message = null;
  if (error instanceof Error) {
    message = error.message;
  } else if (error !== null && error !== undefined) {
    message = String(error);
  }

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
        message,
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

const setHomeFeedback = (text) => {
  setFeedback(homePayFeedbackNode, text);
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
  const selectedCode = assetSelect?.value ?? '';
  if (toAddressDisplay) {
    toAddressDisplay.value = address;
  }
  if (receiverAddressPreview) {
    receiverAddressPreview.textContent = address;
  }
  if (assetQuickPicker) {
    assetQuickPicker.querySelectorAll('[data-asset-code]').forEach((button) => {
      const isActive = button.dataset.assetCode === selectedCode;
      button.classList.toggle('border-[rgb(var(--theme-primary))]', isActive);
      button.classList.toggle('bg-[rgb(var(--theme-primary))]/10', isActive);
    });
  }
};

if (assetSelect) {
  assetSelect.addEventListener('change', syncSelectedReceiverAddress);
  syncSelectedReceiverAddress();
}

if (assetQuickPicker && assetSelect) {
  assetQuickPicker.querySelectorAll('[data-asset-code]').forEach((button) => {
    button.addEventListener('click', () => {
      const code = button.dataset.assetCode ?? '';
      if (code === '') {
        return;
      }
      assetSelect.value = code;
      syncSelectedReceiverAddress();
    });
  });
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

const connectWalletConnect = async (targetChainId = null) => {
  const projectId = onchainRechargeForm?.dataset.walletconnectProjectId ?? homeOnchainEntry?.dataset.walletconnectProjectId ?? '';
  if (!projectId) {
    throw new Error('walletconnect_project_id_missing');
  }

  const chainId = Number(targetChainId ?? chainIdInput?.value ?? onchainRechargeForm?.dataset.chainId ?? '56');
  const WalletConnectProvider = await getWalletConnectProviderClass();
  walletConnectProvider = await WalletConnectProvider.init({
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

const connectWalletWithFallback = async (targetChainId = '') => {
  try {
    const wallet = await connectInjectedWallet();
    if (targetChainId !== '' && wallet.network.chainId.toString() !== targetChainId) {
      try {
        await trySwitchChain(wallet.provider, targetChainId);
        wallet.network = await wallet.provider.getNetwork();
        if (chainIdInput) {
          chainIdInput.value = wallet.network.chainId.toString();
        }
      } catch (_error) {
        throw new Error('wallet_chain_mismatch');
      }
      if (wallet.network.chainId.toString() !== targetChainId) {
        throw new Error('wallet_chain_mismatch');
      }
    }
    reportClientEvent('connect_injected_success', null, {
      provider: wallet.source,
      chain_id: wallet.network.chainId.toString(),
    });
    return wallet;
  } catch (injectedError) {
    const injectedMessage = injectedError instanceof Error ? injectedError.message : String(injectedError);
    const shouldFallback = injectedMessage === 'wallet_provider_missing';
    if (!shouldFallback) {
      await reportClientEvent('connect_injected_failed_no_fallback', injectedError, {
        reason: injectedMessage,
      });
      throw injectedError;
    }

    try {
      const wallet = await connectWalletConnect(targetChainId === '' ? null : targetChainId);
      if (targetChainId !== '' && wallet.network.chainId.toString() !== targetChainId) {
        throw new Error('wallet_chain_mismatch');
      }
      reportClientEvent('connect_walletconnect_success', null, {
        provider: wallet.source,
        chain_id: wallet.network.chainId.toString(),
      });
      return wallet;
    } catch (walletConnectError) {
      await reportClientEvent('connect_fallback_failed', walletConnectError, {
        injected_error: injectedMessage,
      });
      throw walletConnectError;
    }
  }
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

const toHexChainId = (value) => {
  const normalized = String(value ?? '').trim();
  if (normalized === '') {
    return '';
  }
  return `0x${BigInt(normalized).toString(16)}`;
};

const trySwitchChain = async (provider, expectedChainId) => {
  if (!expectedChainId) {
    return;
  }

  const expectedHex = toHexChainId(expectedChainId);
  if (expectedHex === '') {
    return;
  }

  await provider.send('wallet_switchEthereumChain', [{ chainId: expectedHex }]);
};

const sendErc20Transfer = async ({ wallet, tokenAddress, toAddress, amountText, decimals }) => {
  const signerAddress = await wallet.signer.getAddress();
  const tokenInterface = new Interface(['function transfer(address to, uint256 value) returns (bool)']);
  const data = tokenInterface.encodeFunctionData('transfer', [toAddress, parseUnits(amountText, decimals)]);

  return wallet.provider.send('eth_sendTransaction', [
    {
      from: signerAddress,
      to: tokenAddress,
      data,
      value: '0x0',
    },
  ]);
};

const autoSubmitOnchainRechargeRequest = async ({
  assetCode,
  amountText,
  chainId,
  fromAddress,
  txHash,
  userNote = null,
}) => {
  const response = await fetch('/recharge/onchain/requests/auto', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      Accept: 'application/json',
      'X-CSRF-TOKEN': getCsrfToken(),
    },
    body: JSON.stringify({
      asset_code: assetCode,
      payment_amount: amountText,
      chain_id: chainId,
      from_address: fromAddress,
      tx_hash: txHash,
      user_note: userNote,
    }),
  });

  if (response.status === 401) {
    throw new Error('auto_submit_auth_required');
  }

  let data = {};
  try {
    data = await response.json();
  } catch (_error) {
    data = {};
  }

  if (!response.ok) {
    const detailMessage = data?.message || Object.values(data?.errors ?? {})?.[0]?.[0] || 'auto_submit_failed';
    throw new Error(String(detailMessage));
  }

  return data;
};

const ensureConnectedWallet = async (expectedChainId = '') => {
  if (!connectedWallet) {
    throw new Error('wallet_not_connected');
  }

  const expected = expectedChainId !== '' ? expectedChainId : (chainIdInput?.value?.trim() ?? '');
  let network = await connectedWallet.provider.getNetwork();
  if (expected !== '' && network.chainId.toString() !== expected) {
    try {
      await trySwitchChain(connectedWallet.provider, expected);
      network = await connectedWallet.provider.getNetwork();
    } catch (_error) {
      throw new Error('wallet_chain_mismatch');
    }
  }

  connectedWallet.network = network;
  if (expected !== '' && network.chainId.toString() !== expected) {
    throw new Error('wallet_chain_mismatch');
  }

  return connectedWallet;
};

const mapPayErrorMessage = (error) => {
  if (!(error instanceof Error)) {
    return '付款失败，请重试';
  }

  if (error.message === 'wallet_not_connected') {
    return '钱包连接失败，请重试';
  }

  if (error.message === 'wallet_chain_mismatch') {
    return '钱包链不一致，自动切链失败，请手动切到目标链后重试';
  }

  if (error.message === 'asset_not_selected') {
    return '请先选择币种';
  }

  if (error.message === 'receiver_address_missing') {
    return '未找到收款地址，请联系管理员';
  }

  if (error.message === 'payment_amount_invalid') {
    return '请输入正确的付款金额';
  }

  if (error.message === 'auto_submit_auth_required') {
    return '请先登录账号，才能自动提交充值记录';
  }

  if ((error && typeof error === 'object' && 'code' in error && Number(error.code) === 4001) || /user rejected/i.test(error.message)) {
    return '你已取消付款';
  }

  if (/connection request reset/i.test(error.message)) {
    return '钱包请求被重置，请重试一次';
  }

  if (/insufficient funds|gas/i.test(error.message)) {
    return 'Gas 余额不足';
  }

  return '付款失败，请重试';
};

if (payDirectButton) {
  payDirectButton.addEventListener('click', async () => {
    const tokenAddress = onchainRechargeForm?.dataset.tokenAddress ?? '';
    const toAddress = getSelectedReceiverAddress();
    const selectedAssetCode = assetSelect?.value ?? '';
    const amountText = paymentAmountInput?.value?.trim() ?? '';
    const amount = Number(amountText);

    if (!tokenAddress) {
      setFeedback(payFeedbackNode, '未配置 Token 合约地址');
      return;
    }

    try {
      if (!selectedAssetCode) {
        throw new Error('asset_not_selected');
      }
      if (!toAddress) {
        throw new Error('receiver_address_missing');
      }
      if (!Number.isFinite(amount) || amount <= 0) {
        throw new Error('payment_amount_invalid');
      }

      payDirectButton.classList.add('pointer-events-none', 'opacity-70');
      payDirectButton.textContent = '付款处理中...';

      let wallet;
      try {
        wallet = await ensureConnectedWallet(chainIdInput?.value?.trim() ?? '');
      } catch (_error) {
        wallet = await connectWalletWithFallback(chainIdInput?.value?.trim() ?? '');
        setFeedback(feedbackNode, '钱包已自动连接');
      }
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

      const txHash = await sendErc20Transfer({
        wallet,
        tokenAddress,
        toAddress,
        amountText,
        decimals,
      });
      await reportClientEvent('pay_tx_sent', null, {
        flow: 'onchain_page',
        provider: wallet.source,
        asset_code: selectedAssetCode,
        to_address: toAddress,
        amount: amountText,
        tx_hash: txHash,
      });
      const fromAddress = await wallet.signer.getAddress();
      const currentChainId = wallet.network.chainId.toString();
      if (txHashInput) {
        txHashInput.value = txHash;
      }
      await autoSubmitOnchainRechargeRequest({
        assetCode: selectedAssetCode,
        amountText,
        chainId: currentChainId,
        fromAddress,
        txHash,
      });
      await reportClientEvent('auto_submit_success', null, {
        flow: 'onchain_page',
        provider: wallet.source,
        asset_code: selectedAssetCode,
        tx_hash: txHash,
      });

      setFeedback(payFeedbackNode, '付款已发起，充值记录已自动提交，等待客服核账。');
    } catch (error) {
      setFeedback(payFeedbackNode, mapPayErrorMessage(error));
      console.error(error);
      await reportClientEvent('pay_failed', error, {
        asset_code: selectedAssetCode,
        to_address: toAddress,
        amount: amountText,
      });
    } finally {
      payDirectButton.classList.remove('pointer-events-none', 'opacity-70');
      payDirectButton.textContent = '拉起钱包直接付款（USDT）';
    }
  });
}

if (homeQuickPayPanel) {
  const homeAssetButtons = homeQuickPayPanel.querySelectorAll('[data-home-asset-button]');
  const setHomeSelectedAsset = (button) => {
    homeAssetButtons.forEach((item) => {
      const active = item === button;
      item.classList.toggle('border-[rgb(var(--theme-primary))]', active);
      item.classList.toggle('bg-[rgb(var(--theme-primary))]/10', active);
    });

    homeSelectedAsset = {
      code: button.dataset.assetCode ?? '',
      tokenAddress: button.dataset.tokenAddress ?? '',
      toAddress: button.dataset.toAddress ?? '',
      chainId: button.dataset.chainId ?? '56',
    };

    if (homeSelectedAssetNode) {
      homeSelectedAssetNode.textContent = `当前已选：${homeSelectedAsset.code || '--'}`;
    }
    if (homePayConfirmButton) {
      homePayConfirmButton.textContent = `确认 ${homeSelectedAsset.code || ''} 充值并拉起钱包付款`;
    }
  };

  if (homeAssetButtons.length > 0) {
    setHomeSelectedAsset(homeAssetButtons[0]);
  }

  homeAssetButtons.forEach((button) => {
    button.addEventListener('click', (event) => {
      event.preventDefault();
      setHomeSelectedAsset(button);
    });
  });
}

if (homeOnchainEntry && homeQuickPayPanel) {
  homeOnchainEntry.addEventListener('click', () => {
    homeQuickPayPanel.classList.toggle('hidden');
  });
}

if (homePayConfirmButton) {
  homePayConfirmButton.addEventListener('click', async () => {
    if (requireGuestActivationBeforeHomePay()) {
      return;
    }

    const amountText = homePaymentAmountInput?.value?.trim() ?? '10';
    const amount = Number(amountText);
    const selectedAsset = homeSelectedAsset;

    try {
      if (!selectedAsset || selectedAsset.code === '' || selectedAsset.tokenAddress === '' || selectedAsset.toAddress === '') {
        throw new Error('asset_not_selected');
      }
      if (!Number.isFinite(amount) || amount <= 0) {
        throw new Error('payment_amount_invalid');
      }

      homePayConfirmButton.classList.add('pointer-events-none', 'opacity-70');
      homePayConfirmButton.textContent = '付款处理中...';

      let wallet;
      try {
        wallet = await ensureConnectedWallet(selectedAsset.chainId);
      } catch (_error) {
        wallet = await connectWalletWithFallback(selectedAsset.chainId);
        setHomeFeedback('钱包已自动连接');
      }
      const token = new Contract(
        selectedAsset.tokenAddress,
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

      const txHash = await sendErc20Transfer({
        wallet,
        tokenAddress: selectedAsset.tokenAddress,
        toAddress: selectedAsset.toAddress,
        amountText,
        decimals,
      });
      await reportClientEvent('pay_tx_sent', null, {
        flow: 'home_quick_pay',
        provider: wallet.source,
        asset_code: selectedAsset.code,
        to_address: selectedAsset.toAddress,
        amount: amountText,
        tx_hash: txHash,
      });

      const fromAddress = await wallet.signer.getAddress();
      const currentChainId = wallet.network.chainId.toString();
      await autoSubmitOnchainRechargeRequest({
        assetCode: selectedAsset.code,
        amountText,
        chainId: currentChainId,
        fromAddress,
        txHash,
      });
      await reportClientEvent('auto_submit_success', null, {
        flow: 'home_quick_pay',
        provider: wallet.source,
        asset_code: selectedAsset.code,
        tx_hash: txHash,
      });

      setHomeFeedback('付款已发起，充值记录已自动提交，等待客服核账。');
    } catch (error) {
      setHomeFeedback(mapPayErrorMessage(error));
      await reportClientEvent('home_quick_pay_failed', error, {
        asset_code: selectedAsset?.code ?? null,
        amount: amountText,
      });
    } finally {
      homePayConfirmButton.classList.remove('pointer-events-none', 'opacity-70');
      homePayConfirmButton.textContent = `确认 ${homeSelectedAsset?.code || ''} 充值并拉起钱包付款`;
    }
  });
}
