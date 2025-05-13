/**
 * WordPress dependencies
 */
import { createReduxStore, register } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { STORE_NAME } from './name';
import servicesStoreConfig from './services';
import settingsStoreConfig from './settings';
import combineStoreConfigs from '../utils/combine-store-configs';

const storeConfig = combineStoreConfigs(
	servicesStoreConfig,
	settingsStoreConfig
);

export const store = createReduxStore( STORE_NAME, storeConfig );
register( store );
