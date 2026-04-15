import './bootstrap';
import 'flowbite';
import './home/dynamic-display-value';
import { loadOnchainRechargeIfNeeded } from './onchain-recharge-loader';

void loadOnchainRechargeIfNeeded();
