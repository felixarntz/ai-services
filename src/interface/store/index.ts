/**
 * WordPress dependencies
 */
import { createReduxStore, register } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { STORE_NAME } from './name';
import sidebarStoreConfig from './sidebar';
import modalStoreConfig from './modal';
import panelStoreConfig from './panel';
import preferencesStoreConfig from './preferences';
import combineStoreConfigs from '../../utils/combine-store-configs';

const storeConfig = combineStoreConfigs(
	sidebarStoreConfig,
	modalStoreConfig,
	panelStoreConfig,
	preferencesStoreConfig
);

export const store = createReduxStore( STORE_NAME, storeConfig );
register( store );
