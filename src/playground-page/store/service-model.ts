/**
 * External dependencies
 */
import { store as aiStore } from '@ai-services/ai';
import memoize from 'memize';
import type {
	ServiceResource,
	AiCapability,
	FunctionDeclaration,
} from '@ai-services/ai/types';
import type { StoreConfig, Action, ThunkArgs } from 'wp-store-utils';

/**
 * WordPress dependencies
 */
import { createRegistrySelector } from '@wordpress/data';
import { store as preferencesStore } from '@wordpress/preferences';

/**
 * Internal dependencies
 */
import { STORE_NAME } from './name';
import type { AiServiceOption, AiModelOption } from '../types';

const EMPTY_FUNCTION_DECLARATION_ARRAY: FunctionDeclaration[] = [];
const EMPTY_AI_MODEL_OPTION_ARRAY: AiModelOption[] = [];
const EMPTY_STRING_ARRAY: string[] = [];

const filterAvailableServices = memoize(
	(
		registeredServices: Record< string, ServiceResource >,
		requiredCapabilities: AiCapability[]
	) => {
		return Object.values( registeredServices )
			.filter( ( service ) => {
				if ( ! service.is_available ) {
					return false;
				}
				return requiredCapabilities.every( ( capability ) =>
					( service.metadata?.capabilities || [] ).includes(
						capability
					)
				);
			} )
			.map( ( { slug, metadata } ): AiServiceOption => {
				return {
					identifier: slug,
					label: metadata?.name || slug,
				};
			} );
	}
);

const filterAvailableModels = memoize(
	(
		availableModels: ServiceResource[ 'available_models' ],
		requiredCapabilities: AiCapability[]
	) => {
		return Object.values( availableModels )
			.filter( ( modelMetadata ) => {
				return requiredCapabilities.every( ( capability ) =>
					modelMetadata.capabilities.includes( capability )
				);
			} )
			.map( ( modelMetadata ): AiModelOption => {
				return {
					identifier: modelMetadata.slug,
					label: modelMetadata.name,
				};
			} );
	}
);

export enum ActionType {
	Unknown = 'REDUX_UNKNOWN',
	SetActiveFunctionDeclaration = 'SET_ACTIVE_FUNCTION_DECLARATION',
}

type UnknownAction = Action< ActionType.Unknown >;
type SetActiveFunctionDeclarationAction = Action<
	ActionType.SetActiveFunctionDeclaration,
	{ name: string }
>;

export type CombinedAction = UnknownAction | SetActiveFunctionDeclarationAction;

export type State = {
	activeFunctionDeclaration: string | null;
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
	activeFunctionDeclaration: null,
};

const actions = {
	/**
	 * Sets the service.
	 *
	 * @since 0.4.0
	 *
	 * @param service - Service identifier.
	 * @returns Action creator.
	 */
	setService( service: string ) {
		return ( { registry, dispatch, select }: DispatcherArgs ) => {
			registry
				.dispatch( preferencesStore )
				.set( 'ai-services-playground', 'service', service );

			const availableModels = select.getAvailableModels();
			if ( availableModels && availableModels.length === 1 ) {
				dispatch.setModel( availableModels[ 0 ].identifier );
			} else {
				dispatch.setModel( '' );
			}
		};
	},

	/**
	 * Sets the model.
	 *
	 * @since 0.4.0
	 *
	 * @param model - Model identifier.
	 * @returns Action creator.
	 */
	setModel( model: string ) {
		return ( { registry }: DispatcherArgs ) => {
			registry
				.dispatch( preferencesStore )
				.set( 'ai-services-playground', 'model', model );
		};
	},

	/**
	 * Sets a model configuration parameter.
	 *
	 * @since 0.4.0
	 *
	 * @param key   - Parameter key.
	 * @param value - Parameter value.
	 * @returns Action creator.
	 */
	setModelParam( key: string, value: unknown ) {
		return ( { registry }: DispatcherArgs ) => {
			registry
				.dispatch( preferencesStore )
				.set( 'ai-services-playground', `model_param_${ key }`, value );
		};
	},

	/**
	 * Sets the system instruction.
	 *
	 * @since 0.4.0
	 *
	 * @param systemInstruction - System instruction.
	 * @returns Action creator.
	 */
	setSystemInstruction( systemInstruction: string ) {
		return ( { registry }: DispatcherArgs ) => {
			registry
				.dispatch( preferencesStore )
				.set(
					'ai-services-playground',
					'system-instruction',
					systemInstruction
				);
		};
	},

	/**
	 * Shows the system instruction.
	 *
	 * @since 0.4.0
	 *
	 * @returns Action creator.
	 */
	showSystemInstruction() {
		return ( { registry }: DispatcherArgs ) => {
			registry
				.dispatch( preferencesStore )
				.set(
					'ai-services-playground',
					'system-instruction-visible',
					true
				);
		};
	},

	/**
	 * Hides the system instruction.
	 *
	 * @since 0.4.0
	 *
	 * @returns Action creator.
	 */
	hideSystemInstruction() {
		return ( { registry }: DispatcherArgs ) => {
			registry
				.dispatch( preferencesStore )
				.set(
					'ai-services-playground',
					'system-instruction-visible',
					false
				);
		};
	},

	/**
	 * Adds or updates a function declaration.
	 *
	 * If a function with the given name is already present, it will be updated. Otherwise a new function with the name
	 * will be added. This ensures that every function declaration is unique by name.
	 *
	 * @since 0.5.0
	 *
	 * @param name         - Unique function name.
	 * @param description  - Function description.
	 * @param parameters   - Function parameters as JSON schema. It must be of type 'object'.
	 * @param existingName - Name of an existing function to replace, if this is an update and the name differs.
	 * @returns Action creator.
	 */
	setFunctionDeclaration(
		name: string,
		description: string,
		parameters: Record< string, unknown >,
		existingName: string
	) {
		return ( { registry }: DispatcherArgs ) => {
			// Some basic sanity checks on the input.
			if (
				typeof name !== 'string' ||
				name === '' ||
				typeof description !== 'string' ||
				typeof parameters !== 'object' ||
				parameters === null ||
				parameters.type !== 'object'
			) {
				return;
			}

			if ( typeof existingName !== 'string' || existingName === '' ) {
				existingName = name;
			}

			const functionDeclarations =
				( registry
					.select( preferencesStore )
					.get(
						'ai-services-playground',
						'function-declarations'
					) as FunctionDeclaration[] ) ??
				EMPTY_FUNCTION_DECLARATION_ARRAY;

			const newFunctionDeclarations =
				name !== existingName
					? functionDeclarations.filter(
							( declaration ) => declaration.name !== name
					  )
					: [ ...functionDeclarations ];
			const existingIndex = newFunctionDeclarations.findIndex(
				( declaration ) => declaration.name === existingName
			);

			if ( existingIndex === -1 ) {
				newFunctionDeclarations.push( {
					name,
					description,
					parameters,
				} );
			} else {
				newFunctionDeclarations[ existingIndex ] = {
					name,
					description,
					parameters,
				};
			}
			newFunctionDeclarations.sort( ( a, b ) =>
				a.name < b.name ? -1 : 1
			);

			registry
				.dispatch( preferencesStore )
				.set(
					'ai-services-playground',
					'function-declarations',
					newFunctionDeclarations
				);
		};
	},

	/**
	 * Deletes a function declaration.
	 *
	 * Deletion is irreversible.
	 *
	 * @since 0.5.0
	 *
	 * @param name - Unique function name of the function declaration to delete.
	 * @returns Action creator.
	 */
	deleteFunctionDeclaration( name: string ) {
		return ( { registry }: DispatcherArgs ) => {
			const functionDeclarations =
				( registry
					.select( preferencesStore )
					.get(
						'ai-services-playground',
						'function-declarations'
					) as FunctionDeclaration[] ) ??
				EMPTY_FUNCTION_DECLARATION_ARRAY;

			const newFunctionDeclarations = functionDeclarations.filter(
				( declaration ) => declaration.name !== name
			);
			if (
				functionDeclarations.length === newFunctionDeclarations.length
			) {
				return;
			}

			registry
				.dispatch( preferencesStore )
				.set(
					'ai-services-playground',
					'function-declarations',
					newFunctionDeclarations
				);
		};
	},

	/**
	 * Toggles whether a specific function declaration is selected.
	 *
	 * @since 0.5.0
	 *
	 * @param name - Function declaration name.
	 * @returns Action creator.
	 */
	toggleSelectedFunctionDeclaration( name: string ) {
		return ( { registry }: DispatcherArgs ) => {
			const selected =
				( registry
					.select( preferencesStore )
					.get(
						'ai-services-playground',
						'selected-function-declaration-names'
					) as string[] ) ?? EMPTY_STRING_ARRAY;

			const newSelected = selected.includes( name )
				? selected.filter( ( selectedName ) => selectedName !== name )
				: [ ...selected, name ];

			registry
				.dispatch( preferencesStore )
				.set(
					'ai-services-playground',
					'selected-function-declaration-names',
					newSelected
				);
		};
	},

	/**
	 * Sets the active function declaration (currently being edited in the modal).
	 *
	 * @since 0.5.0
	 *
	 * @param name - Active function name being edited, or empty string to clear.
	 * @returns Action creator.
	 */
	setActiveFunctionDeclaration( name: string ) {
		return ( { dispatch }: DispatcherArgs ) => {
			dispatch( {
				type: ActionType.SetActiveFunctionDeclaration,
				payload: { name },
			} );
		};
	},
};

/**
 * Reducer for the store mutations.
 *
 * @since 0.5.0
 *
 * @param state  - Current state.
 * @param action - Action object.
 * @returns New state.
 */
function reducer( state: State = initialState, action: CombinedAction ): State {
	switch ( action.type ) {
		case ActionType.SetActiveFunctionDeclaration: {
			const { name } = action.payload;
			return {
				...state,
				activeFunctionDeclaration: name,
			};
		}
	}

	return state;
}

const selectors = {
	getService: createRegistrySelector( ( select ) => () => {
		const service = select( preferencesStore ).get(
			'ai-services-playground',
			'service'
		) as string | undefined;
		if ( ! service ) {
			return false;
		}
		const availableServices = select(
			STORE_NAME
		).getAvailableServices() as AiServiceOption[] | undefined;
		if (
			! availableServices ||
			! availableServices.find(
				( { identifier } ) => identifier === service
			)
		) {
			return false;
		}
		return service;
	} ),

	getModel: createRegistrySelector( ( select ) => () => {
		const model = select( preferencesStore ).get(
			'ai-services-playground',
			'model'
		) as string | undefined;
		if ( ! model ) {
			return false;
		}
		const availableModels = select( STORE_NAME ).getAvailableModels() as
			| AiModelOption[]
			| undefined;
		if (
			! availableModels ||
			! availableModels.find( ( { identifier } ) => identifier === model )
		) {
			return false;
		}
		return model;
	} ),

	getServiceName: createRegistrySelector( ( select ) => () => {
		const service = select( preferencesStore ).get(
			'ai-services-playground',
			'service'
		) as string | undefined;
		if ( ! service ) {
			return false;
		}
		const serviceMetadata = select( aiStore ).getServiceMetadata( service );
		if ( ! serviceMetadata ) {
			return false;
		}
		return serviceMetadata.name;
	} ),

	getModelName: createRegistrySelector( ( select ) => () => {
		const model = select( preferencesStore ).get(
			'ai-services-playground',
			'model'
		) as string | undefined;
		if ( ! model ) {
			return false;
		}
		const availableModels = select( STORE_NAME ).getAvailableModels() as
			| AiModelOption[]
			| undefined;
		if ( ! availableModels ) {
			return false;
		}
		const modelData = availableModels.find(
			( { identifier } ) => identifier === model
		);
		return modelData?.label || false;
	} ),

	getModelParam: createRegistrySelector(
		( select ) => ( _state: State, key: string ) => {
			return select( preferencesStore ).get(
				'ai-services-playground',
				`model_param_${ key }`
			);
		}
	),

	getAvailableServices: createRegistrySelector( ( select ) => () => {
		const registeredServices = select( aiStore ).getServices();
		if ( ! registeredServices ) {
			return undefined;
		}

		const requiredCapabilities = select(
			STORE_NAME
		).getCapabilities() as AiCapability[];
		return filterAvailableServices(
			registeredServices,
			requiredCapabilities
		);
	} ),

	getAvailableModels: createRegistrySelector( ( select ) => () => {
		const registeredServices = select( aiStore ).getServices();
		if ( ! registeredServices ) {
			return undefined;
		}

		const service = select( STORE_NAME ).getService() as string | false;
		if ( ! service || ! registeredServices[ service ] ) {
			return EMPTY_AI_MODEL_OPTION_ARRAY;
		}

		const requiredCapabilities = select(
			STORE_NAME
		).getCapabilities() as AiCapability[];
		return filterAvailableModels(
			registeredServices[ service ].available_models,
			requiredCapabilities
		);
	} ),

	getSystemInstruction: createRegistrySelector( ( select ) => () => {
		const systemInstruction = select( preferencesStore ).get(
			'ai-services-playground',
			'system-instruction'
		) as string | undefined;
		if ( ! systemInstruction ) {
			return '';
		}
		return systemInstruction;
	} ),

	isSystemInstructionVisible: createRegistrySelector( ( select ) => () => {
		const isVisible = select( preferencesStore ).get(
			'ai-services-playground',
			'system-instruction-visible'
		);
		return !! isVisible;
	} ),

	getFunctionDeclarations: createRegistrySelector( ( select ) => () => {
		return (
			( select( preferencesStore ).get(
				'ai-services-playground',
				'function-declarations'
			) as FunctionDeclaration[] ) ?? EMPTY_FUNCTION_DECLARATION_ARRAY
		);
	} ),

	getSelectedFunctionDeclarations: createRegistrySelector(
		( select ) => () => {
			return (
				( select( preferencesStore ).get(
					'ai-services-playground',
					'selected-function-declaration-names'
				) as string[] ) ?? EMPTY_STRING_ARRAY
			);
		}
	),

	getActiveFunctionDeclaration: ( state: State ) => {
		return state.activeFunctionDeclaration;
	},
};

const storeConfig: StoreConfig<
	State,
	ActionCreators,
	CombinedAction,
	Selectors
> = {
	actions,
	reducer,
	selectors,
};

export default storeConfig;
