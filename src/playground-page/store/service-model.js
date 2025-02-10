/**
 * External dependencies
 */
import { store as aiStore } from '@ai-services/ai';
import memoize from 'memize';

/**
 * WordPress dependencies
 */
import { createRegistrySelector } from '@wordpress/data';
import { store as preferencesStore } from '@wordpress/preferences';

/**
 * Internal dependencies
 */
import { STORE_NAME } from './name';

const EMPTY_ARRAY = [];

const filterAvailableServices = memoize(
	( registeredServices, requiredCapabilities ) => {
		return Object.values( registeredServices )
			.filter( ( service ) => {
				if ( ! service.is_available ) {
					return false;
				}
				return requiredCapabilities.every( ( capability ) =>
					service.capabilities.includes( capability )
				);
			} )
			.map( ( { slug, name } ) => {
				return {
					identifier: slug,
					label: name,
				};
			} );
	}
);

const filterAvailableModels = memoize(
	( availableModels, requiredCapabilities ) => {
		return Object.values( availableModels )
			.filter( ( modelData ) => {
				return requiredCapabilities.every( ( capability ) =>
					modelData.capabilities.includes( capability )
				);
			} )
			.map( ( modelData ) => {
				return {
					identifier: modelData.slug,
					label: modelData.name,
				};
			} );
	}
);

const SET_ACTIVE_FUNCTION_DECLARATION = 'SET_ACTIVE_FUNCTION_DECLARATION';

const initialState = {
	activeFunctionDeclaration: null,
};

const actions = {
	/**
	 * Sets the service.
	 *
	 * @since 0.4.0
	 *
	 * @param {string} service Service identifier.
	 * @return {Function} Action creator.
	 */
	setService( service ) {
		return ( { registry, dispatch, select } ) => {
			registry
				.dispatch( preferencesStore )
				.set( 'ai-services-playground', 'service', service );

			const availableModels = select.getAvailableModels();
			if ( availableModels.length === 1 ) {
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
	 * @param {string} model Model identifier.
	 * @return {Function} Action creator.
	 */
	setModel( model ) {
		return ( { registry } ) => {
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
	 * @param {string} key   Parameter key.
	 * @param {*}      value Parameter value.
	 * @return {Function} Action creator.
	 */
	setModelParam( key, value ) {
		return ( { registry } ) => {
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
	 * @param {string} systemInstruction System instruction.
	 * @return {Function} Action creator.
	 */
	setSystemInstruction( systemInstruction ) {
		return ( { registry } ) => {
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
	 * @return {Function} Action creator.
	 */
	showSystemInstruction() {
		return ( { registry } ) => {
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
	 * @return {Function} Action creator.
	 */
	hideSystemInstruction() {
		return ( { registry } ) => {
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
	 * @since n.e.x.t
	 *
	 * @param {string} name         Unique function name.
	 * @param {string} description  Function description.
	 * @param {Object} parameters   Function parameters as JSON schema. It must be of type 'object', with the actual
	 *                              parameters as properties.
	 * @param {string} existingName Name of an existing function if this is an update and the name differs. If so, the
	 *                              existing function will be replaced.
	 * @return {Function} Action creator.
	 */
	setFunctionDeclaration( name, description, parameters, existingName ) {
		return ( { registry } ) => {
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
				registry
					.select( preferencesStore )
					.get( 'ai-services-playground', 'function-declarations' ) ??
				EMPTY_ARRAY;

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
	 * @since n.e.x.t
	 *
	 * @param {string} name Unique function name of the function declaration to delete.
	 * @return {Function} Action creator.
	 */
	deleteFunctionDeclaration( name ) {
		return ( { registry } ) => {
			const functionDeclarations =
				registry
					.select( preferencesStore )
					.get( 'ai-services-playground', 'function-declarations' ) ??
				EMPTY_ARRAY;

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
	 * @since n.e.x.t
	 *
	 * @param {string} name Function declaration name.
	 * @return {Function} Action creator.
	 */
	toggleSelectedFunctionDeclaration( name ) {
		return ( { registry } ) => {
			const selected =
				registry
					.select( preferencesStore )
					.get(
						'ai-services-playground',
						'selected-function-declaration-names'
					) ?? EMPTY_ARRAY;

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
	 * @since n.e.x.t
	 *
	 * @param {Object} name Active function name being edited, or empty string to clear.
	 * @return {Object} Action creator.
	 */
	setActiveFunctionDeclaration( name ) {
		return {
			type: SET_ACTIVE_FUNCTION_DECLARATION,
			payload: { name },
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
		case SET_ACTIVE_FUNCTION_DECLARATION: {
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
		);
		if ( ! service ) {
			return false;
		}
		const availableServices = select( STORE_NAME ).getAvailableServices();
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
		);
		if ( ! model ) {
			return false;
		}
		const availableModels = select( STORE_NAME ).getAvailableModels();
		if (
			! availableModels ||
			! availableModels.find( ( { identifier } ) => identifier === model )
		) {
			return false;
		}
		return model;
	} ),

	getModelParam: createRegistrySelector( ( select ) => ( state, key ) => {
		return select( preferencesStore ).get(
			'ai-services-playground',
			`model_param_${ key }`
		);
	} ),

	getAvailableServices: createRegistrySelector( ( select ) => () => {
		const registeredServices = select( aiStore ).getServices();
		if ( ! registeredServices ) {
			return EMPTY_ARRAY;
		}

		const requiredCapabilities = select( STORE_NAME ).getCapabilities();
		return filterAvailableServices(
			registeredServices,
			requiredCapabilities
		);
	} ),

	getAvailableModels: createRegistrySelector( ( select ) => () => {
		const service = select( STORE_NAME ).getService();
		if ( ! service ) {
			return EMPTY_ARRAY;
		}

		const registeredServices = select( aiStore ).getServices();
		if ( ! registeredServices || ! registeredServices[ service ] ) {
			return EMPTY_ARRAY;
		}

		const requiredCapabilities = select( STORE_NAME ).getCapabilities();
		return filterAvailableModels(
			registeredServices[ service ].available_models,
			requiredCapabilities
		);
	} ),

	getSystemInstruction: createRegistrySelector( ( select ) => () => {
		const systemInstruction = select( preferencesStore ).get(
			'ai-services-playground',
			'system-instruction'
		);
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
			select( preferencesStore ).get(
				'ai-services-playground',
				'function-declarations'
			) ?? EMPTY_ARRAY
		);
	} ),

	getSelectedFunctionDeclarations: createRegistrySelector(
		( select ) => () => {
			return (
				select( preferencesStore ).get(
					'ai-services-playground',
					'selected-function-declaration-names'
				) ?? EMPTY_ARRAY
			);
		}
	),

	getActiveFunctionDeclaration: ( state ) => {
		return state.activeFunctionDeclaration;
	},
};

const storeConfig = {
	actions,
	reducer,
	selectors,
};

export default storeConfig;
