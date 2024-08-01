/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import './style.scss';

export default function Footer() {
	return (
		<div className="wpoopple-footer">
			<p>
				{ __(
					'All settings are up to date.',
					'wp-oop-plugin-lib-example'
				) }
			</p>
		</div>
	);
}
