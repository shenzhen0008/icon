const ONCHAIN_RECHARGE_SELECTORS = [
  '[data-onchain-recharge-form]',
  '#home-onchain-entry',
  '#home-quick-pay-panel',
];

export const shouldLoadOnchainRecharge = (root = document) => {
  if (!root || typeof root.querySelector !== 'function') {
    return false;
  }

  return ONCHAIN_RECHARGE_SELECTORS.some((selector) => root.querySelector(selector) !== null);
};

export const loadOnchainRechargeIfNeeded = async (
  root = document,
  importer = () => import('./onchain-recharge')
) => {
  if (!shouldLoadOnchainRecharge(root)) {
    return null;
  }

  return importer();
};
