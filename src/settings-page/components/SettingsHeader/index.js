/**
 * WordPress dependencies
 */
import { Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import Header from '../../../components/Header';

export default function SettingsHeader() {
	const handleSave = () => {
		window.console.log( 'Save clicked!' );
	};

	return (
		<Header>
			<Header.Left>
				<h1>{ __( 'Settings', 'wp-oop-plugin-lib-example' ) }</h1>
			</Header.Left>
			<Header.Right>
				<Button variant="primary" onClick={ handleSave }>
					{ __( 'Save', 'wp-oop-plugin-lib-example' ) }
				</Button>
			</Header.Right>
		</Header>
	);
}
