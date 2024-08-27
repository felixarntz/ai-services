/**
 * WordPress dependencies
 */
import apiFetch from '@wordpress/api-fetch';
import { createRegistrySelector } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { STORE_NAME } from './name';

const RECEIVE_SERVICES = 'RECEIVE_SERVICES';

const initialState = {
	services: undefined,
};

const actions = {
	/**
	 * Receives services from the server.
	 *
	 * @since n.e.x.t
	 *
	 * @param {Object} services Services received from the server, as key value pairs.
	 * @return {Function} Action creator.
	 */
	receiveServices( services ) {
		return ( { dispatch } ) => {
			dispatch( {
				type: RECEIVE_SERVICES,
				payload: {
					services,
				},
			} );
		};
	},
};

/**
 * Reducer for the store mutations.
 *
 * @since n.e.x.t
 *
 * @param {Object} state  Current state.
 * @param {Object} action Action object.
 * @return {Object} New state.
 */
function reducer( state = initialState, action ) {
	switch ( action.type ) {
		case RECEIVE_SERVICES: {
			const { services } = action.payload;
			return {
				...state,
				services: services.reduce( ( acc, service ) => {
					acc[ service.slug ] = service;
					return acc;
				}, {} ),
			};
		}
	}

	return state;
}

const resolvers = {
	/**
	 * Fetches the services from the server.
	 *
	 * @since n.e.x.t
	 *
	 * @return {Function} Action creator.
	 */
	getServices() {
		return async ( { dispatch } ) => {
			const services = await apiFetch( {
				path: '/wp-starter-plugin/v1/services',
			} );
			dispatch.receiveServices( services );
		};
	},
};

const selectors = {
	getServices: ( state ) => {
		return state.services;
	},

	getService: createRegistrySelector( ( select ) => ( state, slug ) => {
		const services = select( STORE_NAME ).getServices();
		if ( services === undefined ) {
			return undefined;
		}
		if ( services[ slug ] === undefined ) {
			// eslint-disable-next-line no-console
			console.error( `Invalid service ${ slug }.` );
			return undefined;
		}
		return services[ slug ];
	} ),
};

const storeConfig = {
	initialState,
	actions,
	reducer,
	resolvers,
	selectors,
};

export default storeConfig;
