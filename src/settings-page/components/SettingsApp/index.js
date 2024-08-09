/**
 * External dependencies
 */
import { App } from '@wp-oop-plugin-lib-example/components';

/**
 * Internal dependencies
 */
import SettingsHeader from '../SettingsHeader';
import SettingsCards from '../SettingsCards';
import SettingsFooter from '../SettingsFooter';

export default function SettingsApp() {
	return (
		<App>
			<SettingsHeader />
			<SettingsCards />
			<SettingsFooter />
		</App>
	);
}
