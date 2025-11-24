/**
 * External dependencies
 */
import type { ServiceResource } from '@ai-services/ai/types';
import type { StoreConfig, Action, ThunkArgs } from 'wp-store-utils';

/**
 * WordPress dependencies
 */
import apiFetch from '@wordpress/api-fetch';
import { createRegistrySelector } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { STORE_NAME } from './name';
import logError from '../utils/log-error';

export enum ActionType {
	Unknown = 'REDUX_UNKNOWN',
	ReceiveServices = 'RECEIVE_SERVICES',
	ReceiveService = 'RECEIVE_SERVICE',
}

type UnknownAction = Action< ActionType.Unknown >;
type ReceiveServicesAction = Action<
	ActionType.ReceiveServices,
	{ services: ServiceResource[] }
>;
type ReceiveServiceAction = Action<
	ActionType.ReceiveService,
	{ service: ServiceResource }
>;

export type CombinedAction =
	| UnknownAction
	| ReceiveServicesAction
	| ReceiveServiceAction;

export type State = {
	services: Record< string, ServiceResource > | undefined;
};

export type ActionCreators = typeof actions;
export type Selectors = typeof selectors;

type DispatcherArgs = ThunkArgs<
	State,
	ActionCreators,
	CombinedAction,
	Selectors
>;

const initialState: State = {
	services: undefined,
};

const actions = {
	/**
	 * Receives services from the server.
	 *
	 * @since 0.1.0
	 *
	 * @param services - Services received from the server.
	 * @returns Action creator.
	 */
	receiveServices( services: ServiceResource[] ) {
		return ( { dispatch }: DispatcherArgs ) => {
			dispatch( {
				type: ActionType.ReceiveServices,
				payload: {
					services,
				},
			} );
		};
	},

	/**
	 * Receives a service from the server.
	 *
	 * @since 0.1.0
	 *
	 * @param service - Service received from the server.
	 * @returns Action creator.
	 */
	receiveService( service: ServiceResource ) {
		return ( { dispatch }: DispatcherArgs ) => {
			dispatch( {
				type: ActionType.ReceiveService,
				payload: {
					service,
				},
			} );
		};
	},

	/**
	 * Refreshes a service from the server.
	 *
	 * @since 0.1.0
	 *
	 * @param slug - Service slug.
	 * @returns Action creator.
	 */
	refreshService( slug: string ) {
		return async ( { dispatch, select }: DispatcherArgs ) => {
			if ( select.getServices() === undefined ) {
				return;
			}

			const service: ServiceResource = await apiFetch( {
				path: `/ai-services/v1/services/${ slug }?context=edit`,
			} );
			dispatch.receiveService( service );
		};
	},
};

/**
 * Reducer for the store mutations.
 *
 * @since 0.1.0
 *
 * @param state  - Current state.
 * @param action - Action object.
 * @returns New state.
 */
function reducer( state: State = initialState, action: CombinedAction ): State {
	switch ( action.type ) {
		case ActionType.ReceiveServices: {
			const { services } = action.payload;
			return {
				...state,
				services: services.reduce(
					( acc, service ) => {
						acc[ service.slug ] = service;
						return acc;
					},
					{} as Record< string, ServiceResource >
				),
			};
		}
		case ActionType.ReceiveService: {
			const { service } = action.payload;
			return {
				...state,
				services: {
					...state.services,
					[ service.slug ]: service,
				},
			};
		}
	}

	return state;
}

const resolvers = {
	/**
	 * Fetches the services from the server.
	 *
	 * @since 0.1.0
	 *
	 * @returns Action creator.
	 */
	getServices() {
		return async ( { dispatch }: DispatcherArgs ) => {
			const services: ServiceResource[] = await apiFetch( {
				path: '/ai-services/v1/services?context=edit',
			} );
			dispatch.receiveServices( services );
		};
	},
};

const selectors = {
	getServices: ( state: State ) => {
		return state.services;
	},

	getService: createRegistrySelector(
		( select ) => ( _state: State, slug: string ) => {
			const services: Record< string, ServiceResource > | undefined =
				select( STORE_NAME ).getServices();
			if ( services === undefined ) {
				return undefined;
			}
			if ( services[ slug ] === undefined ) {
				logError( `Invalid service ${ slug }.` );
				return null;
			}
			return services[ slug ];
		}
	),
};

const storeConfig: StoreConfig<
	State,
	ActionCreators,
	CombinedAction,
	Selectors
> = {
	initialState,
	actions,
	reducer,
	resolvers,
	selectors,
};

export default storeConfig;
