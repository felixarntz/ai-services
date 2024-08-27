/**
 * WordPress dependencies
 */
import { createReduxStore, register } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { STORE_NAME } from './name';
import servicesStoreConfig from './services';
import combineStores from '../utils/combine-stores';

const storeConfig = combineStores( servicesStoreConfig );

export const store = createReduxStore( STORE_NAME, storeConfig );
register( store );
