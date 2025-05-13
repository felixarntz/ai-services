/**
 * External dependencies
 */
import {
	DistractionFreePreferenceToggleMenuItem,
	KeyboardShortcutsMenuItem,
} from '@ai-services/interface';

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
 * Renders the More menu to display in the header of the settings app.
 *
 * @since n.e.x.t
 *
 * @returns The component to be rendered.
 */
export default function SettingsMoreMenu() {
	const showIconLabels = useSelect(
		( select ) =>
			select( preferencesStore ).get( 'ai-services', 'showIconLabels' ),
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
					<MenuGroup label={ _x( 'View', 'noun' ) }>
						<DistractionFreePreferenceToggleMenuItem />
					</MenuGroup>
					<MenuGroup label={ __( 'Tools' ) }>
						<KeyboardShortcutsMenuItem />
						<MenuItem
							icon={ external }
							// @ts-expect-error This prop is valid, but is missing from the type definition.
							href={ __(
								'https://wordpress.org/support/plugin/ai-services/',
								'ai-services'
							) }
							target="_blank"
							rel="noopener noreferrer"
						>
							{ __( 'Support', 'ai-services' ) }
							<VisuallyHidden as="span">
								{
									/* translators: accessibility text */
									__( '(opens in a new tab)', 'ai-services' )
								}
							</VisuallyHidden>
						</MenuItem>
					</MenuGroup>
				</>
			) }
		</DropdownMenu>
	);
}
