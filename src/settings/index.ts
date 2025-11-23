/**
 * External dependencies
 */
import { combineStoreConfigs } from '@felixarntz/wp-store-utils';

/**
 * WordPress dependencies
 */
import { createReduxStore, register } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { STORE_NAME } from './name';
import settingsStoreConfig from './settings';

const storeConfig = combineStoreConfigs( settingsStoreConfig );

export const store = createReduxStore( STORE_NAME, storeConfig );
register( store );
