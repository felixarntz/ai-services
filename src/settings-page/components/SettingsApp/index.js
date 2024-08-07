/**
 * WordPress dependencies
 */
import { Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import App from '../../../components/App';
import Header from '../../../components/Header';
import Footer from '../../../components/Footer';
import SettingsCards from '../SettingsCards';

export default function SettingsApp() {
	const handleSave = () => {
		window.console.log( 'Save clicked!' );
	};

	return (
		<App>
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
			<SettingsCards />
			<Footer>
				<p>
					{ __(
						'All settings are up to date.',
						'wp-oop-plugin-lib-example'
					) }
				</p>
			</Footer>
		</App>
	);
}
