/**
 * WordPress dependencies
 */
import { Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import './style.scss';

export default function Header() {
	const handleSave = () => {
		window.console.log( 'Save clicked!' );
	};

	return (
		<div className="wpoopple-header">
			<div className="wpoopple-header__left">
				<h1 className="wpoopple-header__title">
					{ __( 'Title', 'wp-oop-plugin-lib-example' ) }
				</h1>
			</div>
			<div className="wpoopple-header__right">
				<Button variant="primary" onClick={ handleSave }>
					{ __( 'Save', 'wp-oop-plugin-lib-example' ) }
				</Button>
			</div>
		</div>
	);
}
