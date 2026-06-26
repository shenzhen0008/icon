import './bootstrap';
import 'flowbite';
import './home/dynamic-display-value';
import { bindDeferredOnchainRechargeLoad, loadOnchainRechargeIfNeeded } from './onchain-recharge-loader';

void loadOnchainRechargeIfNeeded();
bindDeferredOnchainRechargeLoad();
