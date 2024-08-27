/**
 * WordPress dependencies
 */
import apiFetch from '@wordpress/api-fetch';
import { createRegistrySelector } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { STORE_NAME } from './name';
import { getGenerativeAiService } from './generative-ai-service';

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

	isServiceRegistered: createRegistrySelector(
		( select ) => ( state, slug ) => {
			const services = select( STORE_NAME ).getServices();
			if ( services === undefined ) {
				return undefined;
			}
			return services[ slug ] !== undefined;
		}
	),

	isServiceAvailable: createRegistrySelector(
		( select ) => ( state, slug ) => {
			const services = select( STORE_NAME ).getServices();
			if ( services === undefined ) {
				return undefined;
			}
			return (
				services[ slug ] !== undefined && services[ slug ].is_available
			);
		}
	),

	hasAvailableServices: createRegistrySelector(
		( select ) => ( state, slugs ) => {
			const services = select( STORE_NAME ).getServices();
			if ( services === undefined ) {
				return undefined;
			}
			if ( ! slugs ) {
				slugs = Object.keys( services );
			}
			return slugs.some(
				( slug ) =>
					services[ slug ] !== undefined &&
					services[ slug ].is_available
			);
		}
	),

	getAvailableService: createRegistrySelector(
		( select ) => ( state, slugs ) => {
			const services = select( STORE_NAME ).getServices();
			if ( services === undefined ) {
				return undefined;
			}
			if ( typeof slugs === 'string' ) {
				slugs = [ slugs ];
			} else if ( ! slugs ) {
				slugs = Object.keys( services );
			}
			const availableSlug = slugs.find(
				( slug ) =>
					services[ slug ] !== undefined &&
					services[ slug ].is_available
			);
			if ( ! availableSlug ) {
				return null;
			}
			return getGenerativeAiService( services[ availableSlug ] );
		}
	),
};

const storeConfig = {
	initialState,
	actions,
	reducer,
	resolvers,
	selectors,
};

export default storeConfig;
