import { BrowserProvider } from 'ethers';

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

    try {
      const provider = new BrowserProvider(window.ethereum);
      await provider.send('eth_requestAccounts', []);

      const signer = await provider.getSigner();
      const address = await signer.getAddress();
      const network = await provider.getNetwork();

      const url = new URL('/recharge/onchain', window.location.origin);
      url.searchParams.set('from_address', address);
      url.searchParams.set('chain_id', network.chainId.toString());

      window.location.href = url.toString();
    } catch (error) {
      // If user rejects wallet connection, keep fallback navigation behavior.
      console.error(error);
      window.location.href = '/recharge/onchain';
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
