<dialog id="pending-popup-modal" class="theme-modal" aria-labelledby="pending-popup-salutation" aria-describedby="pending-popup-body" closedby="none">
    <div class="p-5 md:p-6 text-scale-body text-theme-secondary">
        <p id="pending-popup-salutation" class="font-semibold"></p>
        <p id="pending-popup-body" class="mt-1 whitespace-pre-wrap font-normal"></p>
        <p id="pending-popup-error" class="mt-3 text-theme" role="alert" hidden>{{ __('pages/home.stats.popup_confirm_failed') }}</p>
        <div class="mt-5 flex justify-end">
            <button id="pending-popup-confirm" type="button" autofocus class="rounded-lg bg-[rgb(var(--theme-primary))] px-3 py-2 text-scale-body font-semibold text-theme-on-primary disabled:opacity-50">{{ __('pages/home.stats.popup_confirm') }}</button>
        </div>
    </div>
</dialog>

<script>
    (() => {
        const init = () => {
            const context = document.getElementById('top-nav')?.dataset.pageCacheContext;
            if (!context?.startsWith('user:')) return;

            const modal = document.getElementById('pending-popup-modal');
            const salutation = document.getElementById('pending-popup-salutation');
            const body = document.getElementById('pending-popup-body');
            const error = document.getElementById('pending-popup-error');
            const confirm = document.getElementById('pending-popup-confirm');
            const csrfToken = @json(csrf_token());
            const storageKey = `popup_push_shown_campaign_ids:${context}`;
            const shownIds = new Set();
            let inFlight = false;
            let activeId = null;

            const loadShownIds = () => {
                try {
                    const ids = JSON.parse(localStorage.getItem(storageKey) || '[]');
                    if (Array.isArray(ids)) ids.forEach((id) => shownIds.add(String(id)));
                } catch (_) {
                    // Keep current-document deduplication when storage is unavailable.
                }
            };

            const postReceipt = async (id, action) => {
                const response = await fetch(`/popup/${id}/${action}`, {
                    method: 'POST',
                    headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken },
                });
                if (!response.ok || (await response.json())?.ok !== true) {
                    throw new Error('Popup receipt failed');
                }
            };

            const checkPending = async () => {
                if (inFlight || modal.open) return;
                inFlight = true;
                try {
                    const response = await fetch('/popup/pending', {
                        headers: { Accept: 'application/json' },
                    });
                    if (!response.ok) return;
                    const { popup } = await response.json();
                    if (!popup || !Number.isSafeInteger(popup.campaign_id) || popup.campaign_id <= 0
                        || typeof popup.content !== 'string' || shownIds.has(String(popup.campaign_id))) return;

                    salutation.textContent = `${(typeof popup.username === 'string' && popup.username.trim()) || @json(__('pages/home.stats.popup_salutation_default'))}：`;
                    body.textContent = `    ${popup.content.replace(/\n/g, '\n    ')}`;
                    error.hidden = true;
                    modal.showModal();
                    activeId = popup.campaign_id;
                    shownIds.add(String(activeId));
                    try {
                        localStorage.setItem(storageKey, JSON.stringify([...shownIds]));
                    } catch (_) {
                        // The in-memory record still prevents duplicates in this document.
                    }
                    postReceipt(activeId, 'shown').catch(() => {});
                } catch (_) {
                    // Retry on the next page load or custom page-cache restoration.
                } finally {
                    inFlight = false;
                }
            };

            confirm.addEventListener('click', async () => {
                if (activeId === null || confirm.disabled) return;
                confirm.disabled = true;
                error.hidden = true;
                try {
                    await postReceipt(activeId, 'confirm');
                    modal.close();
                    activeId = null;
                } catch (_) {
                    error.hidden = false;
                } finally {
                    confirm.disabled = false;
                }
            });

            modal.addEventListener('cancel', (event) => event.preventDefault());
            // ponytail: storage events reduce duplicates, use atomic coordination only if simultaneous-tab delivery must be exclusive.
            window.addEventListener('storage', (event) => {
                if (event.key === storageKey) loadShownIds();
            });
            window.addEventListener('page-cache:restored', checkPending);
            loadShownIds();
            checkPending();
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init, { once: true });
        } else {
            init();
        }
    })();
</script>
