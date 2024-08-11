/**
 * External dependencies
 */
import { Header, HeaderActions } from '@wp-oop-plugin-lib-example/components';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import SettingsSaveButton from '../SettingsSaveButton';

export default function SettingsHeader() {
	return (
		<Header>
			<h1>{ __( 'Settings', 'wp-oop-plugin-lib-example' ) }</h1>
			<HeaderActions>
				<SettingsSaveButton />
			</HeaderActions>
		</Header>
	);
}
