/**
 * External dependencies
 */
import { combineStoreConfigs } from 'wp-store-utils';

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

const storeConfig = combineStoreConfigs(
	servicesStoreConfig,
	settingsStoreConfig
);

export const store = createReduxStore( STORE_NAME, storeConfig );
register( store );
