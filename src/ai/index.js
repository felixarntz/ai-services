/**
 * Internal dependencies
 */
import * as enums from './enums';
import * as helpers from './helpers';
import { store } from './store';

export { enums, helpers, store };

/*
 * For backward compatibility, expose the store object under 'aiServices.aiStore' as it used to be, while now it is
 * available under 'aiServices.ai'.
 * TODO: Remove this in the future.
 */
if ( ! window.aiServices ) {
	window.aiServices = {};
}
window.aiServices.aiStore = {
	/**
	 * BC wrapper to get the store object, while warning about deprecation.
	 *
	 * @return {Object} The store object.
	 */
	get store() {
		window.console.warn(
			'aiServices.aiStore is deprecated as of version 0.2.0. Use aiServices.ai instead.'
		);
		return store;
	},
};
