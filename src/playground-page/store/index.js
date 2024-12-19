/**
 * WordPress dependencies
 */
import { createReduxStore, register } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { STORE_NAME } from './name';
import capabilitiesStoreConfig from './capabilities';
import serviceModelStoreConfig from './service-model';
import messagesStoreConfig from './messages';
import combineStores from '../../utils/combine-stores';

const storeConfig = combineStores(
	capabilitiesStoreConfig,
	serviceModelStoreConfig,
	messagesStoreConfig
);

export const store = createReduxStore( STORE_NAME, storeConfig );
register( store );
