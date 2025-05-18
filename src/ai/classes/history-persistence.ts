/**
 * WordPress dependencies
 */
import apiFetch from '@wordpress/api-fetch';

/**
 * Internal dependencies
 */
import type { History } from '../types';

/**
 * Class for the history persistence layer.
 *
 * @since 0.5.0
 */
export default class HistoryPersistence {
	/**
	 * Checks whether there is a history for a given feature and history slug.
	 *
	 * @since 0.5.0
	 *
	 * @param feature - Unique identifier of the feature. Must only contain lowercase letters, numbers, hyphens.
	 * @param slug    - Unique identifier of the history within the feature. Must only contain lowercase letters, numbers, hyphens.
	 * @returns True if there is a history, false otherwise.
	 */
	async hasHistory( feature: string, slug: string ): Promise< boolean > {
		const history = await this.loadHistory( feature, slug );
		return history !== null;
	}

	/**
	 * Loads the history for a given feature and history slug.
	 *
	 * @since 0.5.0
	 *
	 * @param feature - Unique identifier of the feature. Must only contain lowercase letters, numbers, hyphens.
	 * @param slug    - Unique identifier of the history within the feature. Must only contain lowercase letters, numbers, hyphens.
	 * @returns The history, or null if there is no history.
	 */
	async loadHistory(
		feature: string,
		slug: string
	): Promise< History | null > {
		try {
			return await apiFetch< History >( {
				path: `/ai-services/v1/features/${ feature }/histories/${ slug }`,
			} );
		} catch ( error ) {
			return null;
		}
	}

	/**
	 * Saves the history for a given feature and history slug.
	 *
	 * @since 0.5.0
	 *
	 * @param history - The history to save. Must have a unique feature and history slug set.
	 * @returns True on success, false on failure.
	 */
	async saveHistory( history: History ): Promise< boolean > {
		if ( ! history.feature || ! history.slug || ! history.entries ) {
			return false;
		}
		try {
			await apiFetch( {
				path: `/ai-services/v1/features/${ history.feature }/histories/${ history.slug }`,
				method: 'POST',
				data: {
					entries: history.entries,
				},
			} );
			return true;
		} catch ( error ) {
			return false;
		}
	}

	/**
	 * Clears the history for a given feature and history slug.
	 *
	 * @since 0.5.0
	 *
	 * @param feature - Unique identifier of the feature. Must only contain lowercase letters, numbers, hyphens.
	 * @param slug    - Unique identifier of the history within the feature. Must only contain lowercase letters, numbers, hyphens.
	 * @returns True on success, false on failure.
	 */
	async clearHistory( feature: string, slug: string ): Promise< boolean > {
		try {
			await apiFetch( {
				path: `/ai-services/v1/features/${ feature }/histories/${ slug }`,
				method: 'DELETE',
			} );
			return true;
		} catch ( error ) {
			return false;
		}
	}

	/**
	 * Loads all histories for a given feature.
	 *
	 * @since 0.5.0
	 *
	 * @param feature - Unique identifier of the feature. Must only contain lowercase letters, numbers, hyphens.
	 * @returns All histories for the feature.
	 */
	async loadHistoriesForFeature( feature: string ): Promise< History[] > {
		try {
			return await apiFetch( {
				path: `/ai-services/v1/features/${ feature }`,
			} );
		} catch ( error ) {
			return [];
		}
	}
}
