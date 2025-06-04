/**
 * WordPress dependencies
 */
import apiFetch from '@wordpress/api-fetch';
import { createRegistrySelector } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { STORE_NAME } from './name';
import { getBrowserServiceData } from './browser';
import GenerativeAiService from '../classes/generative-ai-service';
import BrowserGenerativeAiService from '../classes/browser-generative-ai-service';
import type { StoreConfig, Action, ThunkArgs } from '../../utils/store-types';
import type { ServiceResource, AvailableServicesArgs } from '../types';

const serviceInstances: Record< string, GenerativeAiService > = {};

/**
 * Gets the generative AI service instance for the given service data.
 *
 * The service data must be an object received from the services REST endpoint.
 *
 * @since 0.1.0
 *
 * @param serviceData - Service data.
 * @returns Generative AI service instance.
 */
function getGenerativeAiService(
	serviceData: ServiceResource
): GenerativeAiService {
	const slug = serviceData.metadata.slug;

	if ( ! serviceInstances[ slug ] ) {
		if ( slug === 'browser' ) {
			serviceInstances[ slug ] = new BrowserGenerativeAiService(
				serviceData
			);
		} else {
			serviceInstances[ slug ] = new GenerativeAiService( serviceData );
		}
	}

	return serviceInstances[ slug ];
}

/**
 * Gets the first available service slug, optionally satisfying the given criteria.
 *
 * @since 0.1.0
 *
 * @param services - Service objects, keyed by slug.
 * @param args     - Optional. Arguments to filter the services to consider.
 * @returns The first available service slug, or empty string if no service is available.
 */
function getAvailableServiceSlug(
	services: Record< string, ServiceResource >,
	args?: AvailableServicesArgs
): string {
	const slugs = args?.slugs || Object.keys( services );

	for ( const slug of slugs ) {
		if ( ! services[ slug ] || ! services[ slug ].is_available ) {
			continue;
		}

		if ( args?.capabilities ) {
			const missingCapabilities = args.capabilities.filter(
				( capability ) =>
					! (
						services[ slug ].metadata?.capabilities || []
					).includes( capability )
			);
			if ( missingCapabilities.length ) {
				continue;
			}
		}

		return slug;
	}

	return '';
}

export enum ActionType {
	Unknown = 'REDUX_UNKNOWN',
	ReceiveServices = 'RECEIVE_SERVICES',
}

type UnknownAction = Action< ActionType.Unknown >;
type ReceiveServicesAction = Action<
	ActionType.ReceiveServices,
	{ services: ServiceResource[] }
>;

export type CombinedAction = UnknownAction | ReceiveServicesAction;

export type State = {
	services: Record< string, ServiceResource > | undefined;
	serviceSlugs: string[] | undefined;
};

export type ActionCreators = typeof actions;
export type Selectors = typeof selectors;

type DispatcherArgs = ThunkArgs<
	State,
	ActionCreators,
	CombinedAction,
	Selectors
>;

const initialState = {
	services: undefined,
	serviceSlugs: undefined,
};

const actions = {
	/**
	 * Receives services from the server.
	 *
	 * @since 0.1.0
	 *
	 * @param services - Services received from the server, as key value pairs.
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
			const servicesMap = services.reduce(
				( acc, service ) => {
					acc[ service.slug ] = service;
					return acc;
				},
				{} as Record< string, ServiceResource >
			);
			return {
				...state,
				services: servicesMap,
				serviceSlugs: Object.keys( servicesMap ),
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
				path: '/ai-services/v1/services',
			} );
			services.push( await getBrowserServiceData() );
			dispatch.receiveServices( services );
		};
	},
};

const selectors = {
	getServices: ( state: State ) => {
		return state.services;
	},

	isServiceRegistered: createRegistrySelector(
		( select ) => ( _state: State, slug: string ) => {
			const services: Record< string, ServiceResource > | undefined =
				select( STORE_NAME ).getServices();
			if ( ! services ) {
				return undefined;
			}
			return services[ slug ] !== undefined;
		}
	),

	isServiceAvailable: createRegistrySelector(
		( select ) => ( _state: State, slug: string ) => {
			const services: Record< string, ServiceResource > | undefined =
				select( STORE_NAME ).getServices();
			if ( ! services ) {
				return undefined;
			}
			return (
				services[ slug ] !== undefined && services[ slug ].is_available
			);
		}
	),

	hasAvailableServices: createRegistrySelector(
		( select ) => ( _state: State, args?: AvailableServicesArgs ) => {
			const services: Record< string, ServiceResource > | undefined =
				select( STORE_NAME ).getServices();
			if ( ! services ) {
				return undefined;
			}

			const slug = getAvailableServiceSlug( services, args );
			return !! slug;
		}
	),

	getAvailableService: createRegistrySelector(
		( select ) =>
			( _state: State, args?: string | AvailableServicesArgs ) => {
				const services: Record< string, ServiceResource > | undefined =
					select( STORE_NAME ).getServices();
				if ( ! services ) {
					return undefined;
				}

				if ( typeof args === 'string' ) {
					const slug = args;
					if (
						! services[ slug ] ||
						! services[ slug ].is_available
					) {
						return null;
					}
					return getGenerativeAiService( services[ slug ] );
				}

				const slug = getAvailableServiceSlug( services, args );
				if ( ! slug ) {
					return null;
				}
				return getGenerativeAiService( services[ slug ] );
			}
	),

	getServiceMetadata: createRegistrySelector(
		( select ) => ( _state: State, slug: string ) => {
			const services: Record< string, ServiceResource > | undefined =
				select( STORE_NAME ).getServices();
			if ( ! services ) {
				return undefined;
			}
			if ( ! services[ slug ] ) {
				return null;
			}
			return services[ slug ].metadata;
		}
	),

	getRegisteredServiceSlugs: createRegistrySelector(
		( select ) => ( state: State ) => {
			select( STORE_NAME ).getServices(); // Trigger resolver if not already loaded.
			return state.serviceSlugs;
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
