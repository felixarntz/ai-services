/**
 * WordPress dependencies
 */
import apiFetch from '@wordpress/api-fetch';

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
	 * @param {string} feature Unique identifier of the feature. Must only contain lowercase letters, numbers, hyphens.
	 * @param {string} slug    Unique identifier of the history within the feature. Must only contain lowercase
	 *                         letters, numbers, hyphens.
	 * @return {boolean} True if there is a history, false otherwise.
	 */
	async hasHistory( feature, slug ) {
		const history = await this.loadHistory( feature, slug );
		return history !== null;
	}

	/**
	 * Loads the history for a given feature and history slug.
	 *
	 * @since 0.5.0
	 *
	 * @param {string} feature Unique identifier of the feature. Must only contain lowercase letters, numbers, hyphens.
	 * @param {string} slug    Unique identifier of the history within the feature. Must only contain lowercase
	 *                         letters, numbers, hyphens.
	 * @return {Object|null} The history, or null if there is no history.
	 */
	async loadHistory( feature, slug ) {
		try {
			return await apiFetch( {
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
	 * @param {Object} history The history to save. Must have a unique feature and history slug set.
	 * @return {boolean} True on success, false on failure.
	 */
	async saveHistory( history ) {
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
	 * @param {string} feature Unique identifier of the feature. Must only contain lowercase letters, numbers, hyphens.
	 * @param {string} slug    Unique identifier of the history within the feature. Must only contain lowercase
	 *                         letters, numbers, hyphens.
	 * @return {boolean} True on success, false on failure.
	 */
	async clearHistory( feature, slug ) {
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
	 * @param {string} feature Unique identifier of the feature. Must only contain lowercase letters, numbers, hyphens.
	 * @return {Object[]} All histories for the feature.
	 */
	async loadHistoriesForFeature( feature ) {
		try {
			return await apiFetch( {
				path: `/ai-services/v1/features/${ feature }`,
			} );
		} catch ( error ) {
			return [];
		}
	}
}
