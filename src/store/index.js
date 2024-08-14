/**
 * WordPress dependencies
 */
import { createReduxStore, register } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { STORE_NAME } from './name';
import settingsStoreConfig from './settings';
import { combineStores } from '../utils/combine-stores';

const storeConfig = combineStores( settingsStoreConfig );

export const store = createReduxStore( STORE_NAME, storeConfig );
register( store );
