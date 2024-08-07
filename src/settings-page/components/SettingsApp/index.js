/**
 * Internal dependencies
 */
import App from '../../../components/App';
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
