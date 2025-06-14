/**
 * External dependencies
 */
import {
	DistractionFreePreferenceToggleMenuItem,
	KeyboardShortcutsMenuItem,
} from '@ai-services/interface';
import { store as aiStore } from '@ai-services/ai';

/**
 * WordPress dependencies
 */
import { __, _x } from '@wordpress/i18n';
import { useSelect } from '@wordpress/data';
import { external, moreVertical } from '@wordpress/icons';
import {
	MenuGroup,
	MenuItem,
	VisuallyHidden,
	DropdownMenu,
} from '@wordpress/components';
import { store as preferencesStore } from '@wordpress/preferences';

/**
 * Renders the More menu to display in the header of the playground app.
 *
 * @since 0.4.0
 *
 * @returns The component to be rendered.
 */
export default function PlaygroundMoreMenu() {
	const showIconLabels = useSelect(
		( select ) =>
			select( preferencesStore ).get( 'ai-services', 'showIconLabels' ),
		[]
	);

	const { settingsUrl, homepageUrl, supportUrl, contributingUrl } = useSelect(
		( select ) => {
			const {
				getPluginSettingsUrl,
				getPluginHomepageUrl,
				getPluginSupportUrl,
				getPluginContributingUrl,
			} = select( aiStore );

			return {
				settingsUrl: getPluginSettingsUrl(),
				homepageUrl: getPluginHomepageUrl(),
				supportUrl: getPluginSupportUrl(),
				contributingUrl: getPluginContributingUrl(),
			};
		},
		[]
	);

	return (
		<DropdownMenu
			icon={ moreVertical }
			label={ __( 'Options', 'ai-services' ) }
			popoverProps={ {
				placement: 'bottom-end',
				className: 'more-menu-dropdown__content',
			} }
			toggleProps={ {
				showTooltip: ! showIconLabels,
				...( showIconLabels ? { variant: 'tertiary' } : {} ),
				tooltipPosition: 'bottom',
				size: 'compact',
			} }
		>
			{ () => (
				<>
					<MenuGroup label={ _x( 'View', 'noun', 'ai-services' ) }>
						<DistractionFreePreferenceToggleMenuItem />
					</MenuGroup>
					<MenuGroup label={ __( 'Tools', 'ai-services' ) }>
						<KeyboardShortcutsMenuItem />
						{ !! settingsUrl && (
							// @ts-expect-error This prop is valid, but is missing from the type definition.
							<MenuItem href={ settingsUrl }>
								{ __( 'AI Services Settings', 'ai-services' ) }
							</MenuItem>
						) }
					</MenuGroup>
					<MenuGroup label={ __( 'Resources', 'ai-services' ) }>
						{ !! supportUrl && (
							<MenuItem
								icon={ external }
								// @ts-expect-error This prop is valid, but is missing from the type definition.
								href={ supportUrl }
								target="_blank"
								rel="noopener noreferrer"
							>
								{ __( 'Support', 'ai-services' ) }
								<VisuallyHidden as="span">
									{
										/* translators: accessibility text */
										__(
											'(opens in a new tab)',
											'ai-services'
										)
									}
								</VisuallyHidden>
							</MenuItem>
						) }
						{ !! homepageUrl && (
							<MenuItem
								icon={ external }
								// @ts-expect-error This prop is valid, but is missing from the type definition.
								href={ homepageUrl }
								target="_blank"
								rel="noopener noreferrer"
							>
								{ __( 'Homepage', 'ai-services' ) }
								<VisuallyHidden as="span">
									{
										/* translators: accessibility text */
										__(
											'(opens in a new tab)',
											'ai-services'
										)
									}
								</VisuallyHidden>
							</MenuItem>
						) }
						{ !! contributingUrl && (
							<MenuItem
								icon={ external }
								// @ts-expect-error This prop is valid, but is missing from the type definition.
								href={ contributingUrl }
								target="_blank"
								rel="noopener noreferrer"
							>
								{ __( 'Contributing', 'ai-services' ) }
								<VisuallyHidden as="span">
									{
										/* translators: accessibility text */
										__(
											'(opens in a new tab)',
											'ai-services'
										)
									}
								</VisuallyHidden>
							</MenuItem>
						) }
					</MenuGroup>
				</>
			) }
		</DropdownMenu>
	);
}
