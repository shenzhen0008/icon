const CACHE_PREFIX = 'icon-market:page-cache:';
const CACHE_TTL_MS = 10 * 60 * 1000;
const CACHEABLE_PATHS = new Set(['/', '/help', '/products', '/referral', '/me']);

export const isPageCacheablePath = (pathname) => CACHEABLE_PATHS.has(pathname);

export const buildPageCacheKey = (href, origin = window.location.origin) => {
  if (!href) {
    return null;
  }

  let url;
  try {
    url = new URL(href, origin);
  } catch {
    return null;
  }

  if (url.origin !== origin || !isPageCacheablePath(url.pathname)) {
    return null;
  }

  return `${url.pathname}${url.search}`;
};

const canHandlePathForContext = (pathname, context) => {
  if (pathname === '/me') {
    return typeof context === 'string' && context.startsWith('user:');
  }

  return true;
};

export const shouldHandlePageCacheClick = (event, anchor, origin = window.location.origin, context = 'guest') => {
  if (!anchor || event.defaultPrevented || event.button !== 0) {
    return false;
  }

  if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
    return false;
  }

  if (anchor.target && anchor.target !== '_self') {
    return false;
  }

  if (anchor.hasAttribute?.('download')) {
    return false;
  }

  const key = buildPageCacheKey(anchor.href, origin);
  if (key === null) {
    return false;
  }

  const url = new URL(anchor.href, origin);

  return canHandlePathForContext(url.pathname, context);
};

export const canUseSnapshotForContext = (snapshot, context) => (
  typeof snapshot?.context === 'string'
  && snapshot.context !== ''
  && snapshot.context === context
);

const storageKey = (key) => `${CACHE_PREFIX}${key}`;

const readSnapshot = (key, storage = window.sessionStorage) => {
  try {
    const raw = storage.getItem(storageKey(key));
    if (!raw) {
      return null;
    }

    const snapshot = JSON.parse(raw);
    if (!snapshot.cachedAt || Date.now() - snapshot.cachedAt > CACHE_TTL_MS) {
      storage.removeItem(storageKey(key));
      return null;
    }

    return snapshot;
  } catch {
    return null;
  }
};

const writeSnapshot = (key, snapshot, storage = window.sessionStorage) => {
  try {
    storage.setItem(storageKey(key), JSON.stringify({
      ...snapshot,
      cachedAt: Date.now(),
    }));
  } catch {
    // Storage can be unavailable or full; navigation should still work.
  }
};

const currentPageRoot = () => document.querySelector('[data-page-cache-root]') || document.querySelector('main');

const currentPageContext = () => {
  const root = document.querySelector('[data-page-cache-root]');

  return root?.dataset.pageCacheContext || 'guest';
};

const snapshotFromDocument = (doc) => {
  const root = doc.querySelector('[data-page-cache-root]');
  if (!root) {
    return null;
  }

  return {
    title: doc.title,
    bodyClass: doc.body?.className || '',
    context: root.dataset.pageCacheContext || 'guest',
    rootHtml: root.outerHTML,
    scrollY: 0,
  };
};

const captureCurrentPage = () => {
  const key = buildPageCacheKey(window.location.href);
  const root = document.querySelector('[data-page-cache-root]');
  if (!key || !root) {
    return;
  }

  writeSnapshot(key, {
    title: document.title,
    bodyClass: document.body.className,
    context: currentPageContext(),
    rootHtml: root.outerHTML,
    scrollY: window.scrollY,
  });
};

const updateNavigationState = (pathname) => {
  document.querySelectorAll('#top-nav nav a[href]').forEach((anchor) => {
    const url = new URL(anchor.getAttribute('href'), window.location.origin);
    const active = url.pathname === pathname;
    anchor.classList.toggle('text-white', active);
    anchor.classList.toggle('text-white/75', !active);
    anchor.classList.toggle('hover:text-white', !active);
  });

  document.querySelectorAll('#mobile-nav a[href]').forEach((anchor) => {
    const url = new URL(anchor.getAttribute('href'), window.location.origin);
    const active = url.pathname === pathname;
    anchor.classList.toggle('text-[rgb(var(--theme-primary))]', active);
    anchor.classList.toggle('text-theme-secondary', !active);
    anchor.classList.toggle('hover:text-[rgb(var(--theme-primary))]', !active);
  });
};

const restoreSnapshot = (key, snapshot, url, pushState = true) => {
  const root = currentPageRoot();
  if (!root || !snapshot.rootHtml || !canUseSnapshotForContext(snapshot, currentPageContext())) {
    window.location.assign(url.toString());
    return;
  }

  root.outerHTML = snapshot.rootHtml;
  document.title = snapshot.title || document.title;
  document.body.className = snapshot.bodyClass || document.body.className;
  updateNavigationState(url.pathname);

  if (pushState) {
    window.history.pushState({ pageCacheKey: key }, '', url.toString());
  }

  window.scrollTo(0, snapshot.scrollY || 0);
  window.dispatchEvent(new CustomEvent('page-cache:restored', {
    detail: {
      key,
      pathname: url.pathname,
      url: url.toString(),
    },
  }));
};

const fetchSnapshot = async (url) => {
  const response = await fetch(url.toString(), {
    credentials: 'same-origin',
    headers: {
      'X-Requested-With': 'XMLHttpRequest',
    },
  });

  if (!response.ok) {
    throw new Error(`Unable to fetch page: ${response.status}`);
  }

  const html = await response.text();
  const doc = new DOMParser().parseFromString(html, 'text/html');
  return snapshotFromDocument(doc);
};

export const initNavigationPageCache = () => {
  if (typeof document === 'undefined') {
    return;
  }

  window.history.replaceState({ pageCacheKey: buildPageCacheKey(window.location.href) }, '', window.location.href);

  document.addEventListener('click', async (event) => {
    const anchor = event.target.closest?.('a[href]');
    if (!shouldHandlePageCacheClick(event, anchor, window.location.origin, currentPageContext())) {
      return;
    }

    const url = new URL(anchor.href);
    const key = buildPageCacheKey(anchor.href);
    if (!key) {
      return;
    }

    event.preventDefault();
    captureCurrentPage();

    const cached = readSnapshot(key);
    if (cached && canUseSnapshotForContext(cached, currentPageContext())) {
      restoreSnapshot(key, cached, url);
      return;
    }

    document.documentElement.dataset.pageCacheLoading = 'true';

    try {
      const snapshot = await fetchSnapshot(url);
      if (!snapshot) {
        window.location.assign(url.toString());
        return;
      }

      if (!canUseSnapshotForContext(snapshot, currentPageContext())) {
        window.location.assign(url.toString());
        return;
      }

      writeSnapshot(key, snapshot);
      restoreSnapshot(key, snapshot, url);
    } catch {
      window.location.assign(url.toString());
    } finally {
      delete document.documentElement.dataset.pageCacheLoading;
    }
  });

  window.addEventListener('popstate', (event) => {
    const key = event.state?.pageCacheKey || buildPageCacheKey(window.location.href);
    if (!key) {
      window.location.reload();
      return;
    }

    const snapshot = readSnapshot(key);
    if (!snapshot || !canUseSnapshotForContext(snapshot, currentPageContext())) {
      window.location.reload();
      return;
    }

    restoreSnapshot(key, snapshot, new URL(window.location.href), false);
  });

  window.addEventListener('beforeunload', captureCurrentPage);
};
