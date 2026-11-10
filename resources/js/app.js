import './bootstrap';
import 'flowbite';
import './home/dynamic-display-value';
import './home/hero-panel';
import { initAccountCopyButton } from './me/account-copy';
import { initNavigationPageCache, resolvePageCacheContext } from './navigation-page-cache';
import { bindDeferredOnchainRechargeLoad, loadOnchainRechargeIfNeeded } from './onchain-recharge-loader';
import { initStreamChatUnreadBadge } from './stream-chat-unread';

initAccountCopyButton();
initNavigationPageCache();
void initStreamChatUnreadBadge({ accountContext: resolvePageCacheContext() });
void loadOnchainRechargeIfNeeded();
bindDeferredOnchainRechargeLoad();
