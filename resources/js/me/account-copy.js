const ACCOUNT_COPY_SELECTOR = '[data-copy-account-button]';

export const handleAccountCopyClick = async (event, options = {}) => {
  const button = event.target?.closest?.(ACCOUNT_COPY_SELECTOR);
  if (!button) {
    return false;
  }

  const copyText = button.dataset.copyText;
  if (!copyText) {
    return false;
  }

  event.preventDefault?.();

  const clipboard = options.clipboard ?? globalThis.navigator?.clipboard;
  const alertUser = options.alert ?? globalThis.alert;
  const scheduleReset = options.setTimeout ?? globalThis.setTimeout;
  const originalText = button.textContent;
  const successLabel = button.dataset.copySuccessLabel || originalText;

  try {
    if (clipboard?.writeText) {
      await clipboard.writeText(copyText);
      button.textContent = successLabel;
      scheduleReset?.(() => {
        button.textContent = originalText;
      }, 1500);
      return true;
    }
  } catch {
    // Fall through to visible fallback below.
  }

  if (typeof alertUser === 'function') {
    alertUser(copyText);
  }

  return true;
};

export const initAccountCopyButton = (root = document, options = {}) => {
  root.addEventListener('click', (event) => {
    void handleAccountCopyClick(event, options);
  });
};
