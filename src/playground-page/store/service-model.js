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
		return Object.keys( availableModels )
			.filter( ( modelSlug ) => {
				return requiredCapabilities.every( ( capability ) =>
					availableModels[ modelSlug ].capabilities.includes(
						capability
					)
				);
			} )
			.map( ( modelSlug ) => {
				return {
					identifier: modelSlug,
					label: modelSlug,
				};
			} );
	}
);

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
};

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
};

const storeConfig = {
	actions,
	selectors,
};

export default storeConfig;
