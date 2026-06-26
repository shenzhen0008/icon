const ONCHAIN_RECHARGE_SELECTORS = [
  '[data-onchain-recharge-form]',
];

const HOME_ONCHAIN_TRIGGER_SELECTORS = [
  '#home-onchain-entry',
  '#home-pay-confirm-btn',
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

export const bindDeferredOnchainRechargeLoad = (
  root = document,
  importer = () => import('./onchain-recharge')
) => {
  if (!root || typeof root.querySelector !== 'function') {
    return null;
  }

  let importPromise = null;
  const loadOnce = () => {
    importPromise ??= importer();

    return importPromise;
  };

  const triggers = HOME_ONCHAIN_TRIGGER_SELECTORS
    .map((selector) => root.querySelector(selector))
    .filter((element) => element && typeof element.addEventListener === 'function');

  triggers.forEach((trigger) => {
    trigger.addEventListener('click', loadOnce, { once: true });
  });

  return triggers.length > 0 ? loadOnce : null;
};
