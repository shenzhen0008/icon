import { BrowserProvider, Contract, parseUnits } from 'ethers';

const connectWalletButton = document.getElementById('connect-wallet-btn');
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

const connectWallet = async () => {
  if (!window.ethereum) {
    throw new Error('wallet_provider_missing');
  }

  const provider = new BrowserProvider(window.ethereum);
  await provider.send('eth_requestAccounts', []);
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

      const { signer } = await connectWallet();
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
      await tx.wait();

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
        } else {
          setFeedback(payFeedbackNode, '付款失败，请重试');
        }
      } else {
        setFeedback(payFeedbackNode, '付款失败，请重试');
      }
      console.error(error);
    } finally {
      payDirectButton.classList.remove('pointer-events-none', 'opacity-70');
      payDirectButton.textContent = '拉起钱包直接付款（USDT）';
    }
  });
}
