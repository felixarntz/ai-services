/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import Footer from '../../../components/Footer';

export default function SettingsApp() {
	return (
		<Footer>
			<p>
				{ __(
					'All settings are up to date.',
					'wp-oop-plugin-lib-example'
				) }
			</p>
		</Footer>
	);
}
