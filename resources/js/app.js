import './bootstrap';
import 'flowbite';
import './home/dynamic-display-value';
import { initNavigationPageCache } from './navigation-page-cache';
import { bindDeferredOnchainRechargeLoad, loadOnchainRechargeIfNeeded } from './onchain-recharge-loader';

initNavigationPageCache();
void loadOnchainRechargeIfNeeded();
bindDeferredOnchainRechargeLoad();
