/**
 * External dependencies
 */
import { enums } from '@ai-services/ai';
import memoize from 'memize';
import type { AiCapability } from '@ai-services/ai/types';

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
import type { StoreConfig, Action, ThunkArgs } from '../../utils/store-types';
import type { AiCapabilityOption, ModalityOption } from '../types';

const EMPTY_CAPABILITY_ARRAY: AiCapability[] = [];

const combineCapabilities = memoize(
	(
		foundationalCapability: AiCapability,
		additionalCapabilities: AiCapability[]
	): AiCapability[] => {
		return [ foundationalCapability, ...additionalCapabilities ];
	}
);

const filterAdditionalCapabilities = memoize(
	(
		additionalCapabilities: AiCapability[],
		availableAdditionalCapabilities: AiCapabilityOption[]
	) => {
		const availableValues = availableAdditionalCapabilities.map(
			( cap ) => cap.identifier
		);
		return additionalCapabilities.filter( ( cap ) =>
			availableValues.includes( cap )
		);
	}
);

export enum ActionType {
	Unknown = 'REDUX_UNKNOWN',
	ReceiveFoundationalCapabilities = 'RECEIVE_FOUNDATIONAL_CAPABILITIES',
	ReceiveAdditionalCapabilities = 'RECEIVE_ADDITIONAL_CAPABILITIES',
	ReceiveModalities = 'RECEIVE_MODALITIES',
}

type UnknownAction = Action< ActionType.Unknown >;
type ReceiveFoundationalCapabilitiesAction = Action<
	ActionType.ReceiveFoundationalCapabilities,
	{ capabilities: AiCapabilityOption[] }
>;
type ReceiveAdditionalCapabilitiesAction = Action<
	ActionType.ReceiveAdditionalCapabilities,
	{ capabilities: AiCapabilityOption[] }
>;
type ReceiveModalitiesAction = Action<
	ActionType.ReceiveModalities,
	{ modalities: ModalityOption[] }
>;

export type CombinedAction =
	| UnknownAction
	| ReceiveFoundationalCapabilitiesAction
	| ReceiveAdditionalCapabilitiesAction
	| ReceiveModalitiesAction;

export type State = {
	availableFoundationalCapabilities: AiCapabilityOption[];
	availableAdditionalCapabilities: AiCapabilityOption[];
	availableModalities: ModalityOption[];
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
	 * @param capability - Foundational capability identifier.
	 * @returns Action creator.
	 */
	setFoundationalCapability( capability: AiCapability ) {
		return ( { registry }: DispatcherArgs ) => {
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
	 * @param capability - Additional capability identifier.
	 * @returns Action creator.
	 */
	toggleAdditionalCapability( capability: AiCapability ) {
		return ( { registry }: DispatcherArgs ) => {
			const caps: AiCapability[] | undefined = registry
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
	 * @param capabilities - Foundational capabilities, as array of objects with `identifier` and `label` properties.
	 * @returns Action creator.
	 */
	receiveFoundationalCapabilities( capabilities: AiCapabilityOption[] ) {
		return ( { dispatch }: DispatcherArgs ) => {
			dispatch( {
				type: ActionType.ReceiveFoundationalCapabilities,
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
	 * @param capabilities - Additional capabilities, as array of objects with `identifier` and `label` properties.
	 * @returns Action creator.
	 */
	receiveAdditionalCapabilities( capabilities: AiCapabilityOption[] ) {
		return ( { dispatch }: DispatcherArgs ) => {
			dispatch( {
				type: ActionType.ReceiveAdditionalCapabilities,
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
	 * @param modalities - Modalities, as array of objects with `identifier` and `label` properties.
	 * @returns Action creator.
	 */
	receiveModalities( modalities: ModalityOption[] ) {
		return ( { dispatch }: DispatcherArgs ) => {
			dispatch( {
				type: ActionType.ReceiveModalities,
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
 * @param state  - Current state.
 * @param action - Action object.
 * @returns New state.
 */
function reducer( state: State = initialState, action: CombinedAction ): State {
	switch ( action.type ) {
		case ActionType.ReceiveFoundationalCapabilities: {
			const { capabilities } = action.payload;
			return {
				...state,
				availableFoundationalCapabilities: capabilities,
			};
		}
		case ActionType.ReceiveAdditionalCapabilities: {
			const { capabilities } = action.payload;
			return {
				...state,
				availableAdditionalCapabilities: capabilities,
			};
		}
		case ActionType.ReceiveModalities: {
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
	 * @returns Action creator.
	 */
	getAvailableFoundationalCapabilities() {
		return async ( { dispatch }: DispatcherArgs ) => {
			const capabilities: AiCapabilityOption[] = [
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
	 * @returns Action creator.
	 */
	getAvailableAdditionalCapabilities() {
		return async ( { dispatch }: DispatcherArgs ) => {
			const capabilities: AiCapabilityOption[] = [
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
	 * @returns Action creator.
	 */
	getAvailableModalities() {
		return async ( { dispatch }: DispatcherArgs ) => {
			const modalities: ModalityOption[] = [
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
		) as AiCapability | undefined;
		if ( ! cap ) {
			return enums.AiCapability.TEXT_GENERATION;
		}
		return cap;
	} ),

	getAdditionalCapabilities: createRegistrySelector( ( select ) => () => {
		const caps = select( preferencesStore ).get(
			'ai-services-playground',
			'additionalCapabilities'
		) as AiCapability[] | undefined;
		if ( ! caps ) {
			return EMPTY_CAPABILITY_ARRAY;
		}

		const availableAdditionalCapabilities = select(
			STORE_NAME
		).getAvailableAdditionalCapabilities() as AiCapabilityOption[];
		if ( ! availableAdditionalCapabilities ) {
			return EMPTY_CAPABILITY_ARRAY;
		}
		return filterAdditionalCapabilities(
			caps,
			availableAdditionalCapabilities
		);
	} ),

	getCapabilities: createRegistrySelector( ( select ) => () => {
		return combineCapabilities(
			select( STORE_NAME ).getFoundationalCapability() as AiCapability,
			select( STORE_NAME ).getAdditionalCapabilities() as AiCapability[]
		);
	} ),

	getAvailableFoundationalCapabilities: ( state: State ) =>
		state.availableFoundationalCapabilities,

	getAvailableAdditionalCapabilities: ( state: State ) =>
		state.availableAdditionalCapabilities,

	getAvailableModalities: ( state: State ) => state.availableModalities,
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
