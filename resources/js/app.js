import './bootstrap';
import 'flowbite';
import './home/dynamic-display-value';
import './home/hero-panel';
import { initNavigationPageCache } from './navigation-page-cache';
import { bindDeferredOnchainRechargeLoad, loadOnchainRechargeIfNeeded } from './onchain-recharge-loader';
import { initStreamChatUnreadBadge } from './stream-chat-unread';

initNavigationPageCache();
void initStreamChatUnreadBadge();
void loadOnchainRechargeIfNeeded();
bindDeferredOnchainRechargeLoad();
