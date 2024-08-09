/**
 * External dependencies
 */
import { Footer } from '@wp-oop-plugin-lib-example/components';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

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
