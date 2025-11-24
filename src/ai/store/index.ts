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
import selfStoreConfig from './self';
import servicesStoreConfig from './services';
import chatStoreConfig from './chat';

const storeConfig = combineStoreConfigs(
	selfStoreConfig,
	servicesStoreConfig,
	chatStoreConfig
);

export const store = createReduxStore( STORE_NAME, storeConfig );
register( store );
