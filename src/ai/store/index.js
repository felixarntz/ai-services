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
import combineStores from '../../utils/combine-stores';

const storeConfig = combineStores(
	selfStoreConfig,
	servicesStoreConfig,
	chatStoreConfig
);

export const store = createReduxStore( STORE_NAME, storeConfig );
register( store );
