const MODE_STORAGE_KEY = 'home_hero_panel_mode';
const initializedPanels = new WeakSet();

export const shouldInitializeHomeHeroPanelAfterRestore = () => true;

const formatMoney = (value) => Number(value || 0).toLocaleString('en-US', {
  minimumFractionDigits: 2,
  maximumFractionDigits: 2,
});

const formatMoneyWithPrefix = (value) => `$${formatMoney(value)}`;

const parsePayloadCache = (root) => {
  const payloadScript = root.querySelector?.('#home-hero-panel-payloads');
  if (!payloadScript?.textContent) {
    return {};
  }

  try {
    const parsed = JSON.parse(payloadScript.textContent);
    return parsed && typeof parsed === 'object' ? parsed : {};
  } catch {
    return {};
  }
};

const canUseSeedPayload = (mode) => mode === 'demo';

export const initHomeHeroPanel = ({
  root = document,
  fetchFn = globalThis.window?.fetch?.bind(globalThis.window),
  storage = globalThis.window?.localStorage,
  alertFn = globalThis.window?.alert?.bind(globalThis.window) || (() => {}),
} = {}) => {
  const panel = root.querySelector?.('#home-data-panel');
  if (!panel || initializedPanels.has(panel)) {
    return null;
  }

  const modeBadge = root.querySelector?.('#hero-mode-badge');
  const availableBalance = root.querySelector?.('#hero-available-balance');
  const totalEarnings = root.querySelector?.('#hero-total-earnings');
  const earnings24h = root.querySelector?.('#hero-earnings-24h');
  const demoBtn = root.querySelector?.('#hero-damo-btn');
  const liveBtn = root.querySelector?.('#hero-live-btn');
  const tradeRecordBtn = root.querySelector?.('#hero-trade-record-btn');
  const incomeRecordBtn = root.querySelector?.('#hero-income-record-btn');
  const panelPayloadCache = parsePayloadCache(root);

  initializedPanels.add(panel);
  panel.dataset.homeHeroPanelStarted = 'true';

  const modeMap = {
    damo: 'demo',
    demo: 'demo',
    live: 'live',
  };
  const modeBadgeText = {
    demo: panel.dataset.modeBadgeDemo || '#demo',
    live: panel.dataset.modeBadgeLive || '#live',
  };
  const locale = panel.dataset.locale || 'en';

  const readSavedMode = () => {
    try {
      const savedMode = storage.getItem(MODE_STORAGE_KEY);
      return savedMode === 'live' ? 'live' : 'demo';
    } catch {
      return 'demo';
    }
  };

  const persistMode = (mode) => {
    try {
      storage.setItem(MODE_STORAGE_KEY, mode);
    } catch {
      // Ignore storage failures and keep the UI usable.
    }
  };

  const fetchPanelData = async (mode) => {
    const response = await fetchFn(`/home-hero-panel?mode=${encodeURIComponent(mode)}`, {
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
      },
    });

    if (!response.ok) {
      const errorBody = await response.json().catch(() => ({}));
      throw new Error(errorBody?.message || `HTTP ${response.status}`);
    }

    return response.json();
  };

  const renderPanel = (payload) => {
    if (!payload) return;

    if (modeBadge) modeBadge.textContent = modeBadgeText[payload.mode] ?? modeBadgeText.demo;
    if (availableBalance) availableBalance.textContent = formatMoneyWithPrefix(payload.available_balance);
    if (totalEarnings) totalEarnings.textContent = formatMoneyWithPrefix(payload.total_earnings);
    if (earnings24h) earnings24h.textContent = formatMoneyWithPrefix(payload.earnings_24h);
  };

  const syncRecordLinks = (mode) => {
    const suffix = `?mode=${encodeURIComponent(mode)}&locale=${encodeURIComponent(locale)}`;
    if (tradeRecordBtn) tradeRecordBtn.setAttribute('href', `/home/hero-panel/trade-records${suffix}`);
    if (incomeRecordBtn) incomeRecordBtn.setAttribute('href', `/home/hero-panel/income-records${suffix}`);
  };

  const setButtonActiveState = (button, active) => {
    button?.classList.toggle('bg-gradient-to-r', active);
    button?.classList.toggle('from-cyan-500', active);
    button?.classList.toggle('to-blue-500', active);
    button?.classList.toggle('text-slate-950', active);
    button?.classList.toggle('hover:from-cyan-400', active);
    button?.classList.toggle('hover:to-blue-400', active);

    button?.classList.toggle('bg-slate-700', !active);
    button?.classList.toggle('text-slate-100', !active);
    button?.classList.toggle('hover:bg-slate-600', !active);
  };

  const setMode = async (uiMode) => {
    const mode = modeMap[uiMode];
    if (!mode) return;

    persistMode(mode);
    syncRecordLinks(mode);

    const demoActive = mode === 'demo';
    setButtonActiveState(demoBtn, demoActive);
    setButtonActiveState(liveBtn, !demoActive);

    const cachedPayload = canUseSeedPayload(mode) ? panelPayloadCache[mode] : null;
    if (cachedPayload) {
      renderPanel(cachedPayload);
      return;
    }

    try {
      const payload = await fetchPanelData(mode);
      if (canUseSeedPayload(mode)) {
        panelPayloadCache[mode] = payload;
      }
      renderPanel(payload);
    } catch {
      if (mode === 'live') {
        alertFn(panel.dataset.liveLoadFailed || 'Failed to load live data.');
        await setMode('demo');
      }
    }
  };

  demoBtn?.addEventListener('click', () => setMode('demo'));
  liveBtn?.addEventListener('click', () => setMode('live'));

  void setMode(readSavedMode());

  return { setMode };
};

if (typeof window !== 'undefined') {
  window.initHomeHeroPanel = initHomeHeroPanel;
  window.addEventListener('page-cache:restored', (event) => {
    if (shouldInitializeHomeHeroPanelAfterRestore(event.detail?.pathname)) {
      initHomeHeroPanel();
    }
  });
  initHomeHeroPanel();
}
