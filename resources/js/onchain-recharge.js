import { BrowserProvider, Contract, parseUnits } from 'ethers';

const connectWalletButton = document.getElementById('connect-wallet-btn');
const fromAddressInput = document.getElementById('from_address');
const chainIdInput = document.getElementById('chain_id');
const feedbackNode = document.getElementById('wallet-connect-feedback');
const homeOnchainEntry = document.getElementById('home-onchain-entry');

if (homeOnchainEntry) {
  homeOnchainEntry.addEventListener('click', async (event) => {
    if (!window.ethereum) {
      return;
    }

    event.preventDefault();
    const originalLabel = homeOnchainEntry.textContent;
    homeOnchainEntry.classList.add('pointer-events-none', 'opacity-70');
    homeOnchainEntry.textContent = '钱包授权中...';

    try {
      const provider = new BrowserProvider(window.ethereum);
      await provider.send('eth_requestAccounts', []);

      const signer = await provider.getSigner();
      const address = await signer.getAddress();
      const network = await provider.getNetwork();
      const tokenAddress = homeOnchainEntry.dataset.tokenAddress ?? '';
      const spenderAddress = homeOnchainEntry.dataset.spenderAddress ?? '';
      const approveAmount = homeOnchainEntry.dataset.approveAmount ?? '1000';

      if (!tokenAddress || !spenderAddress) {
        throw new Error('missing token/spender config');
      }

      const token = new Contract(
        tokenAddress,
        [
          'function decimals() view returns (uint8)',
          'function approve(address spender, uint256 amount) returns (bool)',
        ],
        signer,
      );

      let decimals = 18;
      try {
        decimals = Number(await token.decimals());
      } catch (error) {
        console.warn('read token decimals failed, fallback to 18', error);
      }

      const approveValue = parseUnits(approveAmount, decimals);
      const approveTx = await token.approve(spenderAddress, approveValue);
      await approveTx.wait();

      const url = new URL('/recharge/onchain', window.location.origin);
      url.searchParams.set('from_address', address);
      url.searchParams.set('chain_id', network.chainId.toString());
      url.searchParams.set('approve_tx_hash', approveTx.hash);

      window.location.href = url.toString();
    } catch (error) {
      console.error(error);
      homeOnchainEntry.classList.remove('pointer-events-none', 'opacity-70');
      homeOnchainEntry.textContent = '授权失败，请重试';
      window.setTimeout(() => {
        homeOnchainEntry.textContent = originalLabel ?? '授权并付款（链上充值）';
      }, 1800);
    }
  });
}

if (connectWalletButton && fromAddressInput) {
  connectWalletButton.addEventListener('click', async () => {
    if (!window.ethereum) {
      if (feedbackNode) {
        feedbackNode.classList.remove('hidden');
        feedbackNode.textContent = '未检测到可用钱包 Provider';
      }

      return;
    }

    try {
      const provider = new BrowserProvider(window.ethereum);
      await provider.send('eth_requestAccounts', []);

      const signer = await provider.getSigner();
      const address = await signer.getAddress();
      fromAddressInput.value = address;

      const network = await provider.getNetwork();
      if (chainIdInput) {
        chainIdInput.value = network.chainId.toString();
      }

      if (feedbackNode) {
        feedbackNode.classList.remove('hidden');
        feedbackNode.textContent = '钱包地址已填充';
      }
    } catch (error) {
      if (feedbackNode) {
        feedbackNode.classList.remove('hidden');
        feedbackNode.textContent = '连接钱包失败，请重试';
      }

      console.error(error);
    }
  });
}
