<section class="mb-8 rounded-2xl border border-theme bg-theme-card p-5">
    <h2 class="text-scale-display font-semibold text-theme">Open transaction!</h2>
    <p class="mt-2 text-scale-body text-theme-secondary">2000+ base factor library with AI support to more catch derivative factors, one step ahead!</p>

    <div class="mt-5 rounded-xl border border-theme bg-theme-secondary/20 p-4">
        <div class="flex items-center justify-between border-b border-theme pb-3">
            <p class="text-scale-body text-theme-secondary">Number of people</p>
            <p class="text-scale-title font-semibold text-[rgb(var(--theme-primary))]" id="summary-participant-count">{{ $summary['participant_count'] }}</p>
        </div>
        <div class="mt-3 flex items-center justify-between">
            <p class="text-scale-body text-theme-secondary">总盘获利值</p>
            <p class="text-scale-title font-semibold text-[rgb(var(--theme-accent))]" id="summary-total-profit">{{ $summary['total_profit'] }} USDT</p>
        </div>
    </div>
</section>

<dialog id="home-popup-modal" class="theme-modal">
    <div class="p-5 md:p-6">
        <div id="home-popup-content" class="text-scale-body whitespace-pre-wrap text-theme-secondary"></div>
        <div class="mt-5 flex justify-end gap-2">
            <button id="home-popup-confirm" type="button" class="rounded-lg bg-[rgb(var(--theme-primary))] px-3 py-2 text-scale-body font-semibold text-theme-on-primary">我已知晓</button>
        </div>
    </div>
</dialog>

<script>
    (() => {
        const participant = document.getElementById('summary-participant-count');
        const totalProfit = document.getElementById('summary-total-profit');
        const popupModal = document.getElementById('home-popup-modal');
        const popupContent = document.getElementById('home-popup-content');
        const popupConfirm = document.getElementById('home-popup-confirm');
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

        if (!participant || !totalProfit || !popupModal || !popupContent || !popupConfirm) {
            return;
        }

        const shownStorageKey = 'home_popup_shown_campaign_ids';
        let shownCampaignIds = new Set();
        let activePopup = null;

        const loadShownCampaignIds = () => {
            try {
                const raw = localStorage.getItem(shownStorageKey);
                const parsed = raw ? JSON.parse(raw) : [];
                if (!Array.isArray(parsed)) {
                    shownCampaignIds = new Set();
                    return;
                }

                shownCampaignIds = new Set(parsed.map((value) => String(value)));
            } catch (_) {
                shownCampaignIds = new Set();
            }
        };

        const saveShownCampaignIds = () => {
            try {
                localStorage.setItem(shownStorageKey, JSON.stringify(Array.from(shownCampaignIds)));
            } catch (_) {
                // Ignore storage failures.
            }
        };

        const markShownCampaign = (campaignId) => {
            shownCampaignIds.add(String(campaignId));
            saveShownCampaignIds();
        };

        const hasShownCampaign = (campaignId) => shownCampaignIds.has(String(campaignId));

        const postReceipt = async (campaignId, action) => {
            if (!campaignId || !action) return;

            try {
                await fetch(`/popup/${campaignId}/${action}`, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken,
                        Accept: 'application/json',
                    },
                });
            } catch (_) {
                // Keep silent in MVP, next polling can retry.
            }
        };

        const closePopup = (action) => {
            if (!activePopup) return;
            const campaignId = activePopup.campaign_id;

            if (popupModal.open) {
                popupModal.close();
            }

            if (action) {
                postReceipt(campaignId, action);
            }

            activePopup = null;
        };

        const showPopup = (popup) => {
            if (!popup || typeof popup.campaign_id !== 'number') return;
            if (activePopup !== null) return;
            if (hasShownCampaign(popup.campaign_id)) return;

            activePopup = popup;
            const salutation = (typeof popup.username === 'string' && popup.username.trim() !== '')
                ? popup.username.trim()
                : '用户';
            const body = typeof popup.content === 'string' ? popup.content : '';
            const indentedBody = body.replace(/\n/g, '\n    ');
            popupContent.innerHTML = '';

            const salutationNode = document.createElement('p');
            salutationNode.className = 'font-semibold';
            salutationNode.textContent = `${salutation}：`;

            const bodyNode = document.createElement('p');
            bodyNode.className = 'mt-1 whitespace-pre-wrap font-normal';
            bodyNode.textContent = `    ${indentedBody}`;

            popupContent.appendChild(salutationNode);
            popupContent.appendChild(bodyNode);

            markShownCampaign(popup.campaign_id);
            postReceipt(popup.campaign_id, 'shown');

            if (!popupModal.open) {
                popupModal.showModal();
            }
        };

        const refreshSummary = async () => {
            try {
                const response = await fetch('/home-summary', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });
                if (!response.ok) return;

                const payload = await response.json();

                if (typeof payload?.participant_count === 'string') {
                    participant.textContent = payload.participant_count;
                }

                if (typeof payload?.total_profit === 'string') {
                    totalProfit.textContent = `${payload.total_profit} USDT`;
                }

                if (payload?.popup && typeof payload.popup === 'object') {
                    showPopup(payload.popup);
                }
            } catch (_) {
                // Keep silent in MVP, next interval will retry.
            }
        };

        popupConfirm.addEventListener('click', () => closePopup('confirm'));

        popupModal.addEventListener('cancel', (event) => {
            event.preventDefault();
        });

        popupModal.addEventListener('click', (event) => {
            const rect = popupModal.getBoundingClientRect();
            const inside =
                event.clientX >= rect.left &&
                event.clientX <= rect.right &&
                event.clientY >= rect.top &&
                event.clientY <= rect.bottom;

            if (!inside) {
                event.preventDefault();
            }
        });

        window.addEventListener('storage', (event) => {
            if (event.key !== shownStorageKey) return;
            loadShownCampaignIds();
        });

        loadShownCampaignIds();
        setInterval(refreshSummary, 3000);
    })();
</script>
