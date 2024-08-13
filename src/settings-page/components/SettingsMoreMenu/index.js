/**
 * External dependencies
 */
import {
	DistractionFreePreferenceToggleMenuItem,
	KeyboardShortcutsMenuItem,
} from '@wp-oop-plugin-lib-example/components';

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
 * @return {Component} The component to be rendered.
 */
export default function SettingsMoreMenu() {
	const showIconLabels = useSelect(
		( select ) =>
			select( preferencesStore ).get(
				'wp-oop-plugin-lib-example',
				'showIconLabels'
			),
		[]
	);

	return (
		<DropdownMenu
			icon={ moreVertical }
			label={ __( 'Options', 'wp-oop-plugin-lib-example' ) }
			popoverProps={ {
				placement: 'bottom-end',
				className: 'more-menu-dropdown__content',
			} }
			toggleProps={ {
				showTooltip: ! showIconLabels,
				...( showIconLabels && { variant: 'tertiary' } ),
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
							href={ __(
								'https://wordpress.org/support/plugin/wp-oop-plugin-lib-example/',
								'wp-oop-plugin-lib-example'
							) }
							target="_blank"
							rel="noopener noreferrer"
						>
							{ __( 'Support', 'wp-oop-plugin-lib-example' ) }
							<VisuallyHidden as="span">
								{
									/* translators: accessibility text */
									__(
										'(opens in a new tab)',
										'wp-oop-plugin-lib-example'
									)
								}
							</VisuallyHidden>
						</MenuItem>
					</MenuGroup>
				</>
			) }
		</DropdownMenu>
	);
}
