<?php
/**
 * Class Felix_Arntz\AI_Services\Services\Components\API_Key_Control
 *
 * @since 0.6.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\Components;

use Felix_Arntz\AI_Services\Services\Authentication\API_Key_Authentication;
use Felix_Arntz\AI_Services\Services\Entities\Service_Entity;

/**
 * Class for an API key input control component.
 *
 * @since 0.6.0
 */
class API_Key_Control {

	/**
	 * The service entity.
	 *
	 * @since 0.6.0
	 * @var Service_Entity
	 */
	private $service_entity;

	/**
	 * The API key.
	 *
	 * @since 0.6.0
	 * @var string
	 */
	private $api_key;

	/**
	 * Additional configuration arguments for the control.
	 *
	 * @since 0.6.0
	 * @var array<string, mixed>
	 */
	private $args;

	/**
	 * Constructor.
	 *
	 * @since 0.6.0
	 *
	 * @param Service_Entity       $service_entity The service entity.
	 * @param string               $api_key       The API key.
	 * @param array<string, mixed> $args          {
	 *     Optional. Additional configuration arguments for the control.
	 *
	 *     @type string $id_attr               The input ID attribute. Default none.
	 *     @type string $name_attr             The input name attribute. Default is the relevant option slug.
	 *     @type string $class_attr            The input class attribute. Default none.
	 *     @type bool   $omit_credentials_link Whether to omit the credentials link. Default false.
	 * }
	 */
	public function __construct( Service_Entity $service_entity, string $api_key, array $args = array() ) {
		$this->service_entity = $service_entity;
		$this->api_key        = $api_key;

		$this->args = wp_parse_args(
			$args,
			array(
				'id_attr'               => '',
				'name_attr'             => '',
				'class_attr'            => '',
				'omit_credentials_link' => false,
			)
		);
		if ( ! $this->args['name_attr'] ) {
			$option_definitions = API_Key_Authentication::get_option_definitions( $this->service_entity->get_field_value( 'slug' ) );
			if ( count( $option_definitions ) > 0 ) {
				$this->args['name_attr'] = key( $option_definitions );
			}
		}
	}

	/**
	 * Renders the entire API key input control, including wrapper element and label.
	 *
	 * @since 0.6.0
	 */
	public function render(): void {
		?>
		<div class="ais-api-key-control">
			<?php $this->render_label(); ?>
			<?php $this->render_input(); ?>
		</div>
		<?php
	}

	/**
	 * Renders the label for the API key input.
	 *
	 * @since 0.6.0
	 */
	public function render_label(): void {
		?>
		<label for="<?php echo esc_attr( $this->args['id_attr'] ); ?>">
			<?php echo esc_html( $this->service_entity->get_field_value( 'name' ) ); ?>
		</label>
		<?php
	}

	/**
	 * Renders the API key input, but not the label.
	 *
	 * It also renders the description text, and is such is suitable as a callback for `add_settings_field()`.
	 *
	 * @since 0.6.0
	 */
	public function render_input(): void {
		$service_name     = $this->service_entity->get_field_value( 'name' );
		$credentials_url  = $this->service_entity->get_field_value( 'credentials_url' );
		$has_forced_value = $this->service_entity->get_field_value( 'has_forced_api_key' );

		?>
		<input
			type="password"
			id="<?php echo esc_attr( $this->args['id_attr'] ); ?>"
			name="<?php echo esc_attr( $this->args['name_attr'] ); ?>"
			class="<?php echo esc_attr( $this->args['class_attr'] ); ?>"
			value="<?php echo esc_attr( $this->api_key ); ?>"
			<?php echo $has_forced_value ? 'readonly' : ''; ?>
		/>
		<p class="description">
			<?php
			if ( $has_forced_value ) {
				echo esc_html(
					sprintf(
						/* translators: %s: service name */
						__( 'The API key for %s cannot be modified as its value is enforced via filter.', 'ai-services' ),
						$service_name
					)
				);
			} else {
				echo esc_html(
					sprintf(
						/* translators: %s: service name */
						__( 'Enter the API key for %s.', 'ai-services' ),
						$service_name
					)
				);
			}
			?>
			<?php
			if ( ! $this->args['omit_credentials_link'] && $credentials_url ) {
				?>
				<a href="<?php echo esc_url( $credentials_url ); ?>" target="_blank">
					<?php
					if ( $this->api_key ) {
						/* translators: %s: service name */
						$credentials_url_text = __( 'Manage<span> %s</span> API keys', 'ai-services' );
					} else {
						/* translators: %s: service name */
						$credentials_url_text = __( 'Get<span> %s</span> API key', 'ai-services' );
					}
					$credentials_url_text .= '<span> ' . __( '(opens in a new tab)', 'ai-services' ) . '</span>';
					$credentials_url_text  = str_replace( '<span>', '<span class="screen-reader-text">', $credentials_url_text );
					echo wp_kses(
						sprintf( $credentials_url_text, $service_name ),
						array(
							'span' => array( 'class' => array() ),
						)
					);
					?>
				</a>
				<?php
			}
			?>
		</p>
		<?php
	}
}
