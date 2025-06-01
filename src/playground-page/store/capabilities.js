/**
 * External dependencies
 */
import { enums } from '@ai-services/ai';
import memoize from 'memize';

/**
 * WordPress dependencies
 */
import { createRegistrySelector } from '@wordpress/data';
import { __, _x } from '@wordpress/i18n';
import { store as preferencesStore } from '@wordpress/preferences';

/**
 * Internal dependencies
 */
import { STORE_NAME } from './name';

const EMPTY_ARRAY = [];

const RECEIVE_FOUNDATIONAL_CAPABILITIES = 'RECEIVE_FOUNDATIONAL_CAPABILITIES';
const RECEIVE_ADDITIONAL_CAPABILITIES = 'RECEIVE_ADDITIONAL_CAPABILITIES';
const RECEIVE_MODALITIES = 'RECEIVE_MODALITIES';

const combineCapabilities = memoize(
	( foundationalCapability, additionalCapabilities ) => {
		return [ foundationalCapability, ...additionalCapabilities ];
	}
);

const filterAdditionalCapabilities = memoize(
	( additionalCapabilities, availableAdditionalCapabilities ) => {
		const availableValues = availableAdditionalCapabilities.map(
			( cap ) => cap.identifier
		);
		return additionalCapabilities.filter( ( cap ) =>
			availableValues.includes( cap )
		);
	}
);

const initialState = {
	availableFoundationalCapabilities: [],
	availableAdditionalCapabilities: [],
	availableModalities: [],
};

const actions = {
	/**
	 * Sets the foundational capability.
	 *
	 * @since 0.4.0
	 *
	 * @param {string} capability Foundational capability identifier.
	 * @return {Function} Action creator.
	 */
	setFoundationalCapability( capability ) {
		return ( { registry } ) => {
			registry
				.dispatch( preferencesStore )
				.set(
					'ai-services-playground',
					'foundationalCapability',
					capability
				);
		};
	},

	/**
	 * Toggles one of the additional capabilities
	 *
	 * @since 0.4.0
	 *
	 * @param {string} capability Additional capability identifier.
	 * @return {Function} Action creator.
	 */
	toggleAdditionalCapability( capability ) {
		return ( { registry } ) => {
			const caps = registry
				.select( preferencesStore )
				.get( 'ai-services-playground', 'additionalCapabilities' );
			if ( ! caps ) {
				registry
					.dispatch( preferencesStore )
					.set( 'ai-services-playground', 'additionalCapabilities', [
						capability,
					] );
				return;
			}

			if ( caps.includes( capability ) ) {
				registry.dispatch( preferencesStore ).set(
					'ai-services-playground',
					'additionalCapabilities',
					caps.filter( ( cap ) => cap !== capability )
				);
			} else {
				registry
					.dispatch( preferencesStore )
					.set( 'ai-services-playground', 'additionalCapabilities', [
						...caps,
						capability,
					] );
			}
		};
	},

	/**
	 * Receives available foundational capabilities.
	 *
	 * @since 0.4.0
	 *
	 * @param {Object[]} capabilities Foundational capabilities, as array of objects with `identifier` and `label` properties.
	 * @return {Function} Action creator.
	 */
	receiveFoundationalCapabilities( capabilities ) {
		return ( { dispatch } ) => {
			dispatch( {
				type: RECEIVE_FOUNDATIONAL_CAPABILITIES,
				payload: {
					capabilities,
				},
			} );
		};
	},

	/**
	 * Receives available additional capabilities.
	 *
	 * @since 0.4.0
	 *
	 * @param {Object[]} capabilities Additional capabilities, as array of objects with `identifier` and `label` properties.
	 * @return {Function} Action creator.
	 */
	receiveAdditionalCapabilities( capabilities ) {
		return ( { dispatch } ) => {
			dispatch( {
				type: RECEIVE_ADDITIONAL_CAPABILITIES,
				payload: {
					capabilities,
				},
			} );
		};
	},

	/**
	 * Receives available modalities.
	 *
	 * @since n.e.x.t
	 *
	 * @param {Object[]} modalities Modalities, as array of objects with `identifier` and `label` properties.
	 * @return {Function} Action creator.
	 */
	receiveModalities( modalities ) {
		return ( { dispatch } ) => {
			dispatch( {
				type: RECEIVE_MODALITIES,
				payload: {
					modalities,
				},
			} );
		};
	},
};

/**
 * Reducer for the store mutations.
 *
 * @since 0.4.0
 *
 * @param {Object} state  Current state.
 * @param {Object} action Action object.
 * @return {Object} New state.
 */
function reducer( state = initialState, action ) {
	switch ( action.type ) {
		case RECEIVE_FOUNDATIONAL_CAPABILITIES: {
			const { capabilities } = action.payload;
			return {
				...state,
				availableFoundationalCapabilities: capabilities,
			};
		}
		case RECEIVE_ADDITIONAL_CAPABILITIES: {
			const { capabilities } = action.payload;
			return {
				...state,
				availableAdditionalCapabilities: capabilities,
			};
		}
		case RECEIVE_MODALITIES: {
			const { modalities } = action.payload;
			return {
				...state,
				availableModalities: modalities,
			};
		}
	}

	return state;
}

const resolvers = {
	/**
	 * Loads foundational capabilities.
	 *
	 * @since 0.4.0
	 *
	 * @return {Function} Action creator.
	 */
	getAvailableFoundationalCapabilities() {
		return async ( { dispatch } ) => {
			const capabilities = [
				{
					identifier: enums.AiCapability.TEXT_GENERATION,
					label: __( 'Text generation', 'ai-services' ),
				},
				{
					identifier: enums.AiCapability.IMAGE_GENERATION,
					label: __( 'Image generation', 'ai-services' ),
				},
				{
					identifier: enums.AiCapability.TEXT_TO_SPEECH,
					label: __( 'Text to speech', 'ai-services' ),
				},
			];

			dispatch.receiveFoundationalCapabilities( capabilities );
		};
	},

	/**
	 * Loads additional capabilities.
	 *
	 * @since 0.4.0
	 *
	 * @return {Function} Action creator.
	 */
	getAvailableAdditionalCapabilities() {
		return async ( { dispatch } ) => {
			const capabilities = [
				{
					identifier: enums.AiCapability.CHAT_HISTORY,
					label: __( 'Chat history', 'ai-services' ),
				},
				{
					identifier: enums.AiCapability.FUNCTION_CALLING,
					label: __( 'Function calling', 'ai-services' ),
				},
				{
					identifier: enums.AiCapability.WEB_SEARCH,
					label: __( 'Web search', 'ai-services' ),
				},
				{
					identifier: enums.AiCapability.MULTIMODAL_INPUT,
					label: __( 'Multimodal input', 'ai-services' ),
				},
				{
					identifier: enums.AiCapability.MULTIMODAL_OUTPUT,
					label: __( 'Multimodal output', 'ai-services' ),
				},
			];

			dispatch.receiveAdditionalCapabilities( capabilities );
		};
	},
	/**
	 * Loads modalities.
	 *
	 * @since n.e.x.t
	 *
	 * @return {Function} Action creator.
	 */
	getAvailableModalities() {
		return async ( { dispatch } ) => {
			const modalities = [
				{
					identifier: enums.Modality.TEXT,
					label: _x( 'Text', 'modality', 'ai-services' ),
				},
				{
					identifier: enums.Modality.IMAGE,
					label: _x( 'Image', 'modality', 'ai-services' ),
				},
				{
					identifier: enums.Modality.AUDIO,
					label: _x( 'Audio', 'modality', 'ai-services' ),
				},
			];

			dispatch.receiveModalities( modalities );
		};
	},
};

const selectors = {
	getFoundationalCapability: createRegistrySelector( ( select ) => () => {
		const cap = select( preferencesStore ).get(
			'ai-services-playground',
			'foundationalCapability'
		);
		if ( ! cap ) {
			return enums.AiCapability.TEXT_GENERATION;
		}
		return cap;
	} ),

	getAdditionalCapabilities: createRegistrySelector( ( select ) => () => {
		const caps = select( preferencesStore ).get(
			'ai-services-playground',
			'additionalCapabilities'
		);
		if ( ! caps ) {
			return EMPTY_ARRAY;
		}

		const availableAdditionalCapabilities =
			select( STORE_NAME ).getAvailableAdditionalCapabilities();
		if ( ! availableAdditionalCapabilities ) {
			return EMPTY_ARRAY;
		}
		return filterAdditionalCapabilities(
			caps,
			availableAdditionalCapabilities
		);
	} ),

	getCapabilities: createRegistrySelector( ( select ) => () => {
		return combineCapabilities(
			select( STORE_NAME ).getFoundationalCapability(),
			select( STORE_NAME ).getAdditionalCapabilities()
		);
	} ),

	getAvailableFoundationalCapabilities: ( state ) =>
		state.availableFoundationalCapabilities,

	getAvailableAdditionalCapabilities: ( state ) =>
		state.availableAdditionalCapabilities,

	getAvailableModalities: ( state ) => state.availableModalities,
};

const storeConfig = {
	initialState,
	actions,
	reducer,
	resolvers,
	selectors,
};

export default storeConfig;
