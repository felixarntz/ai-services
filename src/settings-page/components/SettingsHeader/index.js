/**
 * External dependencies
 */
import { Header, HeaderActions } from '@wp-oop-plugin-lib-example/components';

/**
 * WordPress dependencies
 */
import { Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

export default function SettingsHeader() {
	const handleSave = () => {
		window.console.log( 'Save clicked!' );
	};

	return (
		<Header>
			<h1>{ __( 'Settings', 'wp-oop-plugin-lib-example' ) }</h1>
			<HeaderActions>
				<Button variant="primary" onClick={ handleSave }>
					{ __( 'Save', 'wp-oop-plugin-lib-example' ) }
				</Button>
			</HeaderActions>
		</Header>
	);
}
