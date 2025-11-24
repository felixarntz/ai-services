/**
 * External dependencies
 */
import { MoreMenu } from 'wp-interface';

/**
 * WordPress dependencies
 */
import { __, _x } from '@wordpress/i18n';

/**
 * Renders the More menu to display in the header of the settings app.
 *
 * @since n.e.x.t
 *
 * @returns The component to be rendered.
 */
export default function SettingsMoreMenu() {
	return (
		<MoreMenu
			menuLabel={ __( 'Options', 'wp-starter-plugin' ) }
			externalLinkA11yHint={
				/* translators: accessibility text */
				__( '(opens in a new tab)', 'wp-starter-plugin' )
			}
		>
			{ () => (
				<>
					<MoreMenu.MenuGroup
						label={ _x( 'View', 'noun', 'wp-starter-plugin' ) }
					>
						<MoreMenu.DistractionFreePreferenceToggleMenuItem
							menuItemLabel={ __(
								'Distraction free',
								'wp-starter-plugin'
							) }
							menuItemInfo={ __(
								'Hide secondary interface to help focus',
								'wp-starter-plugin'
							) }
							messageActivated={ __(
								'Distraction free mode activated',
								'wp-starter-plugin'
							) }
							messageDeactivated={ __(
								'Distraction free mode deactivated',
								'wp-starter-plugin'
							) }
						/>
					</MoreMenu.MenuGroup>
					<MoreMenu.MenuGroup
						label={ __( 'Tools', 'wp-starter-plugin' ) }
					>
						<MoreMenu.KeyboardShortcutsMenuItem
							menuItemLabel={ __(
								'Keyboard shortcuts',
								'wp-starter-plugin'
							) }
						/>
						<MoreMenu.ExternalLinkMenuItem
							href={ __(
								'https://wordpress.org/support/plugin/wp-starter-plugin/',
								'wp-starter-plugin'
							) }
						>
							{ __( 'Support', 'wp-starter-plugin' ) }
						</MoreMenu.ExternalLinkMenuItem>
					</MoreMenu.MenuGroup>
				</>
			) }
		</MoreMenu>
	);
}
