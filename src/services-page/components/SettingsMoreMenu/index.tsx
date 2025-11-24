/**
 * External dependencies
 */
import { store as aiStore } from '@ai-services/ai';
import { MoreMenu } from 'wp-interface';

/**
 * WordPress dependencies
 */
import { __, _x } from '@wordpress/i18n';
import { useSelect } from '@wordpress/data';

/**
 * Renders the More menu to display in the header of the settings app.
 *
 * @since 0.7.0
 *
 * @returns The component to be rendered.
 */
export default function SettingsMoreMenu() {
	const { playgroundUrl, homepageUrl, supportUrl, contributingUrl } =
		useSelect( ( select ) => {
			const {
				getPluginPlaygroundUrl,
				getPluginHomepageUrl,
				getPluginSupportUrl,
				getPluginContributingUrl,
			} = select( aiStore );

			return {
				playgroundUrl: getPluginPlaygroundUrl(),
				homepageUrl: getPluginHomepageUrl(),
				supportUrl: getPluginSupportUrl(),
				contributingUrl: getPluginContributingUrl(),
			};
		}, [] );

	return (
		<MoreMenu
			menuLabel={ __( 'Options', 'ai-services' ) }
			externalLinkA11yHint={
				/* translators: accessibility text */
				__( '(opens in a new tab)', 'ai-services' )
			}
		>
			{ () => (
				<>
					<MoreMenu.MenuGroup
						label={ _x( 'View', 'noun', 'ai-services' ) }
					>
						<MoreMenu.DistractionFreePreferenceToggleMenuItem
							menuItemLabel={ __(
								'Distraction free',
								'ai-services'
							) }
							menuItemInfo={ __(
								'Hide secondary interface to help focus',
								'ai-services'
							) }
							messageActivated={ __(
								'Distraction free mode activated',
								'ai-services'
							) }
							messageDeactivated={ __(
								'Distraction free mode deactivated',
								'ai-services'
							) }
						/>
					</MoreMenu.MenuGroup>
					<MoreMenu.MenuGroup label={ __( 'Tools', 'ai-services' ) }>
						<MoreMenu.KeyboardShortcutsMenuItem
							menuItemLabel={ __(
								'Keyboard shortcuts',
								'ai-services'
							) }
						/>
						{ !! playgroundUrl && (
							<MoreMenu.InternalLinkMenuItem
								href={ playgroundUrl }
							>
								{ __(
									'AI Services Playground',
									'ai-services'
								) }
							</MoreMenu.InternalLinkMenuItem>
						) }
					</MoreMenu.MenuGroup>
					<MoreMenu.MenuGroup
						label={ __( 'Resources', 'ai-services' ) }
					>
						{ !! supportUrl && (
							<MoreMenu.ExternalLinkMenuItem href={ supportUrl }>
								{ __( 'Support', 'ai-services' ) }
							</MoreMenu.ExternalLinkMenuItem>
						) }
						{ !! homepageUrl && (
							<MoreMenu.ExternalLinkMenuItem href={ homepageUrl }>
								{ __( 'Homepage', 'ai-services' ) }
							</MoreMenu.ExternalLinkMenuItem>
						) }
						{ !! contributingUrl && (
							<MoreMenu.ExternalLinkMenuItem
								href={ contributingUrl }
							>
								{ __( 'Contributing', 'ai-services' ) }
							</MoreMenu.ExternalLinkMenuItem>
						) }
					</MoreMenu.MenuGroup>
				</>
			) }
		</MoreMenu>
	);
}
