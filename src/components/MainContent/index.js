/**
 * WordPress dependencies
 */
import { Card, CardHeader, CardBody } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import './style.scss';

export default function MainContent() {
	return (
		<div className="wpoopple-main-content">
			<Card>
				<CardHeader>
					<h2 className="wpoopple-main-content__heading">
						{ __( 'Advanced', 'wp-oop-plugin-lib-example' ) }
					</h2>
				</CardHeader>
				<CardBody>
					<p>Checkbox here</p>
				</CardBody>
			</Card>
		</div>
	);
}
