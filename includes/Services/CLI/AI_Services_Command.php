<?php
/**
 * Class Felix_Arntz\AI_Services\Services\CLI\AI_Services_Command
 *
 * @since 0.2.0
 * @package ai-services
 */

namespace Felix_Arntz\AI_Services\Services\CLI;

use Felix_Arntz\AI_Services\Services\API\Enums\AI_Capability;
use Felix_Arntz\AI_Services\Services\API\Helpers;
use Felix_Arntz\AI_Services\Services\API\Types\Content;
use Felix_Arntz\AI_Services\Services\API\Types\Image_Generation_Config;
use Felix_Arntz\AI_Services\Services\API\Types\Model_Metadata;
use Felix_Arntz\AI_Services\Services\API\Types\Parts\File_Data_Part;
use Felix_Arntz\AI_Services\Services\API\Types\Parts\Inline_Data_Part;
use Felix_Arntz\AI_Services\Services\API\Types\Parts\Text_Part;
use Felix_Arntz\AI_Services\Services\API\Types\Text_Generation_Config;
use Felix_Arntz\AI_Services\Services\API\Types\Tools;
use Felix_Arntz\AI_Services\Services\Contracts\Generative_AI_Model;
use Felix_Arntz\AI_Services\Services\Contracts\Generative_AI_Service;
use Felix_Arntz\AI_Services\Services\Contracts\With_Image_Generation;
use Felix_Arntz\AI_Services\Services\Contracts\With_Text_Generation;
use Felix_Arntz\AI_Services\Services\Entities\Service_Entity;
use Felix_Arntz\AI_Services\Services\Entities\Service_Entity_Query;
use Felix_Arntz\AI_Services\Services\Exception\Generative_AI_Exception;
use Felix_Arntz\AI_Services\Services\Services_API;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\Capabilities\Capability_Controller;
use Felix_Arntz\AI_Services_Dependencies\Felix_Arntz\WP_OOP_Plugin_Lib\General\Current_User;
use InvalidArgumentException;
use WP_CLI;
use WP_CLI\Formatter;

/**
 * AI Services command class for WP-CLI.
 *
 * @since 0.2.0
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
final class AI_Services_Command {

	/**
	 * The services API instance.
	 *
	 * @since 0.2.0
	 * @var Services_API
	 */
	private $services_api;

	/**
	 * The current user instance.
	 *
	 * @since 0.5.0
	 * @var Current_User
	 */
	private $current_user;

	/**
	 * The capability controller instance.
	 *
	 * @since 0.5.0
	 * @var Capability_Controller
	 */
	private $capability_controller;

	/**
	 * Default fields to display for each service.
	 *
	 * @since 0.2.0
	 * @var string[]
	 */
	private $service_default_fields = array(
		'slug',
		'name',
		'is_available',
		'capabilities',
	);

	/**
	 * Default fields to display for each service model.
	 *
	 * @since 0.2.0
	 * @var string[]
	 */
	private $model_default_fields = array(
		'slug',
		'name',
		'capabilities',
	);

	/**
	 * Arguments that are used for formatting.
	 *
	 * @since 0.2.0
	 * @var string[]
	 */
	private $formatter_args = array(
		'format',
		'fields',
		'field',
	);

	/**
	 * Constructor.
	 *
	 * @since 0.2.0
	 *
	 * @param Services_API          $services_api          The services API instance.
	 * @param Current_User          $current_user          The current user instance.
	 * @param Capability_Controller $capability_controller The capability controller instance.
	 */
	public function __construct( Services_API $services_api, Current_User $current_user, Capability_Controller $capability_controller ) {
		$this->services_api          = $services_api;
		$this->current_user          = $current_user;
		$this->capability_controller = $capability_controller;
	}

	/**
	 * Lists the registered AI services.
	 *
	 * ## OPTIONS
	 *
	 * [--slugs=<slugs>]
	 * : Limit displayed results to include only services with specific slugs.
	 *
	 * [--available-only]
	 * : Limit displayed results to include only services that are configured and thus available to use.
	 *
	 * [--orderby=<orderby>]
	 * : Which field to use for ordering the results. Currently the only option is 'slug'.
	 * ---
	 * default: slug
	 * options:
	 *   - slug
	 * ---
	 *
	 * [--order=<order>]
	 * : Whether to order the results in ascending or descending order. Default is ascending.
	 * ---
	 * default: ASC
	 * options:
	 *   - ASC
	 *   - DESC
	 * ---
	 *
	 * [--field=<field>]
	 * : Prints the value of a single field for each service.
	 *
	 * [--fields=<fields>]
	 * : Limit the output to specific object fields.
	 *
	 * [--format=<format>]
	 * : Format to display the results. Options are table, csv, and json. The default will be a table.
	 * ---
	 * default: table
	 * options:
	 *   - table
	 *   - csv
	 *   - json
	 * ---
	 *
	 * ## AVAILABLE FIELDS
	 *
	 * These fields will be displayed by default for each service:
	 *
	 * * slug
	 * * name
	 * * is_available
	 * * capabilities
	 *
	 * These fields are optionally available:
	 *
	 * * credentials_url
	 * * has_forced_api_key
	 *
	 * ## EXAMPLES
	 *
	 *   wp ai-services list
	 *   wp ai-services list --format=json
	 *   wp ai-services list --available-only --fields=slug,name,capabilities
	 *
	 * @subcommand list
	 *
	 * @since 0.2.0
	 *
	 * @param mixed[]              $args       List of the positional arguments.
	 * @param array<string, mixed> $assoc_args Map of the associative arguments and their values.
	 */
	public function list_( array $args, array $assoc_args ): void {
		$this->maybe_bypass_cap_requirements();

		$assoc_args = $this->parse_assoc_args(
			$assoc_args,
			array(
				'slugs'          => array(),
				'available-only' => false,
				'orderby'        => 'slug',
				'order'          => 'ASC',
			),
			$this->formatter_args
		);

		if ( $assoc_args['slugs'] && is_string( $assoc_args['slugs'] ) ) {
			$assoc_args['slugs'] = array_map( 'trim', explode( ',', $assoc_args['slugs'] ) );
		}

		$query_args = array(
			'slugs'   => $assoc_args['slugs'],
			'orderby' => $assoc_args['orderby'],
			'order'   => $assoc_args['order'],
			'number'  => 100, // Simply set this so high it will probably never be reached.
			'offset'  => 0,
		);

		// Perform query, including any filtering that is supported by the query class.
		$service_entity_query = new Service_Entity_Query( $this->services_api, $query_args );
		$service_entities     = $service_entity_query->get_entities();

		// Perform additional filtering.
		if ( $assoc_args['available-only'] ) {
			$service_entities = array_filter(
				$service_entities,
				static function ( Service_Entity $service_entity ) {
					return $service_entity->get_field_value( 'is_available' );
				}
			);
		}

		$services_data = array_map( array( $this, 'get_service_entity_data' ), $service_entities );

		$formatter = $this->get_formatter(
			$assoc_args,
			$this->service_default_fields
		);

		$formatter->display_items( $services_data );
	}

	/**
	 * Gets details about a registered AI service.
	 *
	 * ## OPTIONS
	 *
	 * <service>
	 * : The service to get.
	 *
	 * [--field=<field>]
	 * : Prints the value of a single field for the service.
	 *
	 * [--fields=<fields>]
	 * : Limit the output to specific object fields.
	 *
	 * [--format=<format>]
	 * : Format to display the results. Options are table, csv, and json. The default will be a table.
	 * ---
	 * default: table
	 * options:
	 *   - table
	 *   - csv
	 *   - json
	 * ---
	 *
	 * ## AVAILABLE FIELDS
	 *
	 * These fields will be displayed by default for the service:
	 *
	 * * slug
	 * * name
	 * * is_available
	 * * capabilities
	 *
	 * These fields are optionally available:
	 *
	 * * credentials_url
	 * * has_forced_api_key
	 *
	 * ## EXAMPLES
	 *
	 *   wp ai-services get google --format=json
	 *
	 * @since 0.2.0
	 *
	 * @param mixed[]              $args       List of the positional arguments.
	 * @param array<string, mixed> $assoc_args Map of the associative arguments and their values.
	 */
	public function get( array $args, array $assoc_args ): void {
		$this->maybe_bypass_cap_requirements();

		if ( ! isset( $args[0] ) ) {
			WP_CLI::error( 'Please provide a service slug as the first positional argument.' );
		}
		$slug = $args[0];
		if ( ! $this->services_api->is_service_registered( $slug ) ) {
			WP_CLI::error( sprintf( "The '%s' service could not be found.", $slug ) );
		}

		$assoc_args = $this->parse_assoc_args(
			$assoc_args,
			array(),
			$this->formatter_args
		);

		$service_entity = new Service_Entity( $this->services_api, $slug );
		$service_data   = $this->get_service_entity_data( $service_entity );

		$formatter = $this->get_formatter(
			$assoc_args,
			$this->service_default_fields
		);

		$formatter->display_item( $service_data );
	}

	/**
	 * Lists the models for a registered and available AI service.
	 *
	 * Only authorized users with sufficient permissions can use this command.
	 *
	 * ## OPTIONS
	 *
	 * <service>
	 * : The service to list models for. It must be configured and available.
	 *
	 * [--slugs=<slugs>]
	 * : Limit displayed results to include only models with specific slugs.
	 *
	 * [--orderby=<orderby>]
	 * : Which field to use for ordering the results. Currently the only option is 'slug'.
	 * ---
	 * default: slug
	 * options:
	 *   - slug
	 * ---
	 *
	 * [--order=<order>]
	 * : Whether to order the results in ascending or descending order. Default is ascending.
	 * ---
	 * default: ASC
	 * options:
	 *   - ASC
	 *   - DESC
	 * ---
	 *
	 * [--field=<field>]
	 * : Prints the value of a single field for each model.
	 *
	 * [--fields=<fields>]
	 * : Limit the output to specific object fields.
	 *
	 * [--format=<format>]
	 * : Format to display the results. Options are table, csv, and json. The default will be a table.
	 * ---
	 * default: table
	 * options:
	 *   - table
	 *   - csv
	 *   - json
	 * ---
	 *
	 * ## AVAILABLE FIELDS
	 *
	 * These fields will be displayed by default for each model:
	 *
	 * * slug
	 * * capabilities
	 *
	 * ## EXAMPLES
	 *
	 *   wp ai-services list-models google
	 *   wp ai-services list-models openai --format=json
	 *   wp ai-services list-models google --slugs=gemini-1.5-flash,gemini-1.5-pro
	 *
	 * @subcommand list-models
	 *
	 * @since 0.2.0
	 *
	 * @param mixed[]              $args       List of the positional arguments.
	 * @param array<string, mixed> $assoc_args Map of the associative arguments and their values.
	 */
	public function list_models( array $args, array $assoc_args ): void {
		$this->maybe_bypass_cap_requirements();

		if ( ! isset( $args[0] ) ) {
			WP_CLI::error( 'Please provide a service slug as the first positional argument.' );
		}
		$slug = $args[0];
		if ( ! $this->services_api->is_service_registered( $slug ) ) {
			WP_CLI::error( sprintf( "The '%s' service could not be found.", $slug ) );
		}
		if ( ! $this->services_api->is_service_available( $slug ) ) {
			WP_CLI::error( sprintf( "Cannot list models for the '%s' service as is not configured and thus not available.", $slug ) );
		}

		$assoc_args = $this->parse_assoc_args(
			$assoc_args,
			array(
				'slugs'   => array(),
				'orderby' => 'slug',
				'order'   => 'ASC',
			),
			$this->formatter_args
		);

		if ( $assoc_args['slugs'] && is_string( $assoc_args['slugs'] ) ) {
			$assoc_args['slugs'] = array_map( 'trim', explode( ',', $assoc_args['slugs'] ) );
		}

		$models = $this->get_service_models( $slug );
		$models = array_map(
			static function ( $model_metadata ) {
				if ( $model_metadata instanceof Model_Metadata ) {
					return $model_metadata->to_array();
				}
				return $model_metadata;
			},
			$models
		);
		if ( count( $assoc_args['slugs'] ) > 0 ) {
			$models = array_intersect_key( $models, array_flip( $assoc_args['slugs'] ) );
		}
		$models = array_values( $models );
		usort( $models, $this->get_model_sort_callback( $assoc_args['orderby'], $assoc_args['order'] ) );

		$formatter = $this->get_formatter(
			$assoc_args,
			$this->model_default_fields
		);

		$formatter->display_items( $models );
	}

	/**
	 * Generates text content using a generative model from an available AI service.
	 *
	 * Only authorized users with sufficient permissions can use this command.
	 *
	 * ## OPTIONS
	 *
	 * [<service>]
	 * : The service to use. Can be omitted to use any available service.
	 *
	 * [<model>]
	 * : The model to use from the service. Can be omitted to use any suitable model from the service.
	 *
	 * <prompt>
	 * : The text prompt to generate content for.
	 *
	 * [--feature=<feature>]
	 * : Required. Unique identifier of the feature that the model will be used for.
	 *
	 * [--attachment-id=<attachment-id>]
	 * : Numeric ID of an attachment to pass to the model as multimodal input alongside the text prompt.
	 *
	 * [--function-declarations=<function-declarations>]
	 * : JSON-encoded array of function declarations to pass to the model as tools.
	 *
	 * [--system-instruction=<system-instruction>]
	 * : System instruction for the model.
	 *
	 * [--<field>=<value>]
	 * : Model generation config arguments. For example, `--temperature=0.5`.
	 *
	 * ## EXAMPLES
	 *
	 *   wp ai-services generate-text google gemini-1.5-pro "What can I do with WordPress?" --feature=my-cli-test
	 *   wp ai-services generate-text openai "What can I do with WordPress?" --feature=cli-example
	 *   wp ai-services generate-text "Give me a list of categories for my blog about WordPress plugins." --feature=cli-category-generator --response-mime-type=application/json --response-schema='{"type":"object","properties":{"categories":{"type":"array","items":{"type":"string"}}}}'
	 *   wp ai-services generate-text "Generate alternative text for this image." --feature=alt-text-generator --attachment-id=123
	 *   wp ai-services generate-text "What is the weather today in Austin?" --feature=weather-info --function-declarations='[{"name":"get_weather", "description":"Returns the weather for today for a given location.", "parameters":{"type":"object", "properties":{"location":{"type":"string", "description": "The location to get the weather for, such as a city or region."}}}}]'
	 *
	 * @subcommand generate-text
	 *
	 * @since 0.2.0
	 *
	 * @param mixed[]              $args       List of the positional arguments.
	 * @param array<string, mixed> $assoc_args Map of the associative arguments and their values.
	 */
	public function generate_text( array $args, array $assoc_args ): void {
		$this->maybe_bypass_cap_requirements();

		list( $service_slug, $model_slug, $prompt ) = $this->parse_generate_positional_args( $args );

		$model_params = $this->get_text_model_params( $model_slug, $assoc_args );

		$attachment_id = isset( $assoc_args['attachment-id'] ) ? (int) $assoc_args['attachment-id'] : 0;

		if ( $service_slug ) {
			$service_args = $service_slug;
		} elseif ( isset( $model_params['capabilities'] ) ) {
			$service_args = array( 'capabilities' => $model_params['capabilities'] );
		} else {
			$service_args = array();
		}

		try {
			$service = $this->services_api->get_available_service( $service_args );
		} catch ( InvalidArgumentException $e ) {
			WP_CLI::error( html_entity_decode( $e->getMessage() ) );
		}

		$model = $this->get_model( $service, $model_params );

		if ( $attachment_id ) {
			$content = Helpers::text_and_attachment_to_content( $prompt, $attachment_id );
		} else {
			$content = Helpers::text_to_content( $prompt );
		}

		if ( $this->should_use_streaming( $model_params ) ) {
			$this->stream_generate_text_using_model( $model, $content );
		} else {
			$this->generate_text_using_model( $model, $content );
		}
	}

	/**
	 * Generates an image using a generative model from an available AI service.
	 *
	 * Only authorized users with sufficient permissions can use this command.
	 *
	 * ## OPTIONS
	 *
	 * [<service>]
	 * : The service to use. Can be omitted to use any available service.
	 *
	 * [<model>]
	 * : The model to use from the service. Can be omitted to use any suitable model from the service.
	 *
	 * <prompt>
	 * : The text prompt to generate an image.
	 *
	 * [--feature=<feature>]
	 * : Required. Unique identifier of the feature that the model will be used for.
	 *
	 * [--system-instruction=<system-instruction>]
	 * : System instruction for the model.
	 *
	 * [--<field>=<value>]
	 * : Model generation config arguments. For example, `--size=2048x2048`.
	 *
	 * ## EXAMPLES
	 *
	 *   wp ai-services generate-image google "Photorealistic image of a French bulldog wearing sunglasses in the forest." --feature=my-cli-test > img_output.txt
	 *   wp ai-services generate-image openai dall-e-3 "Photorealistic image of a French bulldog wearing sunglasses in the forest." --feature=cli-example > img_output.txt
	 *   wp ai-services generate-image openai "Photorealistic image of a French bulldog wearing sunglasses in the forest." --feature=cli-example --response-type=file_data
	 *
	 * @subcommand generate-image
	 *
	 * @since 0.5.0
	 *
	 * @param mixed[]              $args       List of the positional arguments.
	 * @param array<string, mixed> $assoc_args Map of the associative arguments and their values.
	 */
	public function generate_image( array $args, array $assoc_args ): void {
		$this->maybe_bypass_cap_requirements();

		list( $service_slug, $model_slug, $prompt ) = $this->parse_generate_positional_args( $args );

		$model_params = $this->get_image_model_params( $model_slug, $assoc_args );

		if ( $service_slug ) {
			$service_args = $service_slug;
		} elseif ( isset( $model_params['capabilities'] ) ) {
			$service_args = array( 'capabilities' => $model_params['capabilities'] );
		} else {
			$service_args = array();
		}

		try {
			$service = $this->services_api->get_available_service( $service_args );
		} catch ( InvalidArgumentException $e ) {
			WP_CLI::error( html_entity_decode( $e->getMessage() ) );
		}

		$model = $this->get_model( $service, $model_params );

		$content = Helpers::text_to_content( $prompt );

		$this->generate_image_using_model( $model, $content );
	}

	/**
	 * Parses the positional arguments for a generate command.
	 *
	 * Up to three positional arguments are supported, for the service slug, model slug, and prompt.
	 *
	 * @since 0.5.0
	 *
	 * @param mixed[] $args The positional arguments.
	 * @return array<?string> The parsed positional arguments, always containing three elements: service slug, model
	 *                        slug, and prompt. Each of them is either a string or `null` if not provided.
	 */
	private function parse_generate_positional_args( array $args ): array {
		if ( ! isset( $args[0] ) ) {
			WP_CLI::error( 'You must provide at least a prompt as positional argument.' );
		}

		if ( count( $args ) === 3 ) {
			return $args;
		}

		if ( count( $args ) === 2 ) {
			return array( $args[0], null, $args[1] );
		}

		return array( null, null, $args[0] );
	}

	/**
	 * Checks whether to use streaming for generating content in WP-CLI.
	 *
	 * @since 0.5.0
	 *
	 * @param array<string, mixed> $model_params The model parameters.
	 * @return bool Whether to use streaming for generating content in WP-CLI.
	 */
	private function should_use_streaming( array $model_params ): bool {
		// At the moment, streaming is only supported for plain text generation without tools.
		if ( isset( $model_params['tools'] ) ) {
			return false;
		}

		/**
		 * Filters whether to use streaming for generating text content in WP-CLI.
		 *
		 * Streaming will print the generated text as it comes in, providing more immediate feedback, which can be
		 * especially useful for long-running generation tasks.
		 *
		 * @since 0.3.0
		 *
		 * @param bool $use_streaming Whether to use streaming for generating text content in WP-CLI. Default is true.
		 */
		return (bool) apply_filters( 'ai_services_wp_cli_use_streaming', true );
	}

	/**
	 * Gets the data array for the given service entity.
	 *
	 * @since 0.2.0
	 *
	 * @param Service_Entity $service_entity The service entity.
	 * @return array<string, mixed> The service data.
	 */
	private function get_service_entity_data( Service_Entity $service_entity ): array {
		return array(
			'slug'               => $service_entity->get_field_value( 'slug' ),
			'name'               => $service_entity->get_field_value( 'name' ),
			'credentials_url'    => $service_entity->get_field_value( 'credentials_url' ),
			'type'               => $service_entity->get_field_value( 'type' ),
			'is_available'       => $service_entity->get_field_value( 'is_available' ) ? 'true' : 'false',
			'capabilities'       => $service_entity->get_field_value( 'capabilities' ),
			'has_forced_api_key' => $service_entity->get_field_value( 'has_forced_api_key' ) ? 'true' : 'false',
		);
	}

	/**
	 * Gets the models for the given service.
	 *
	 * The service must be available for this to work.
	 *
	 * @since 0.2.0
	 * @since 0.5.0 Return type changed to a map of model data shapes.
	 * @since n.e.x.t Return type changed to a map of model metadata objects.
	 *
	 * @param string $service_slug The service slug.
	 * @return array<string, Model_Metadata> Metadata for each model, mapped by model slug.
	 */
	private function get_service_models( string $service_slug ): array {
		$service = $this->services_api->get_available_service( $service_slug );
		try {
			return $service->list_models();
		} catch ( Generative_AI_Exception $e ) {
			WP_CLI::error( html_entity_decode( $e->getMessage() ) );
		}
	}

	/**
	 * Gets a callback to sort models by a specific field.
	 *
	 * @since 0.2.0
	 *
	 * @param string $orderby The field to order by. Only 'slug' is supported.
	 * @param string $order   The order direction (either 'ASC' or 'DESC').
	 */
	private function get_model_sort_callback( string $orderby, string $order ): callable {
		$orderby = 'slug'; // For now forced to this value.
		$order   = strtoupper( $order );

		return static function ( $a, $b ) use ( $orderby, $order ) {
			$a_value = $a[ $orderby ];
			$b_value = $b[ $orderby ];

			if ( $a_value === $b_value ) {
				return 0;
			}

			if ( 'DESC' === $order ) {
				return $a_value < $b_value ? 1 : -1;
			}

			return $a_value < $b_value ? -1 : 1;
		};
	}

	/**
	 * Gets the text generation model parameters for the given model slug and associative arguments.
	 *
	 * @since 0.5.0
	 *
	 * @param string|null          $model_slug The model slug.
	 * @param array<string, mixed> $assoc_args Map of the associative arguments and their values.
	 * @return array<string, mixed> The model parameters, to retrieve a model.
	 */
	private function get_text_model_params( ?string $model_slug, array $assoc_args ): array {
		// Assume any unknown arguments are generation configuration arguments.
		$generation_config_args = $assoc_args;
		$assoc_args             = $this->parse_assoc_args(
			$assoc_args,
			array(
				'feature'               => '',
				'system-instruction'    => '',
				'attachment-id'         => 0,
				'function-declarations' => '',
			),
			$this->formatter_args
		);
		$generation_config_args = $this->sanitize_generation_config_args(
			array_diff_key( $generation_config_args, $assoc_args )
		);
		$attachment_id          = (int) $assoc_args['attachment-id'];
		$function_declarations  = $assoc_args['function-declarations'] ? json_decode( $assoc_args['function-declarations'], true ) : array();

		$capabilities = array( AI_Capability::TEXT_GENERATION );
		if ( $attachment_id ) {
			$capabilities[] = AI_Capability::MULTIMODAL_INPUT;
		}
		if ( $function_declarations ) {
			$capabilities[] = AI_Capability::FUNCTION_CALLING;
		}

		return array(
			'feature'           => $assoc_args['feature'] ? $assoc_args['feature'] : null,
			'model'             => $model_slug,
			'capabilities'      => $capabilities,
			'generationConfig'  => Text_Generation_Config::from_array( $generation_config_args ),
			'tools'             => $function_declarations ? Tools::from_array(
				array(
					array( 'functionDeclarations' => $function_declarations ),
				)
			) : null,
			'systemInstruction' => $assoc_args['system-instruction'] ? $assoc_args['system-instruction'] : null,
		);
	}

	/**
	 * Gets the image generation model parameters for the given model slug and associative arguments.
	 *
	 * @since 0.5.0
	 *
	 * @param string|null          $model_slug The model slug.
	 * @param array<string, mixed> $assoc_args Map of the associative arguments and their values.
	 * @return array<string, mixed> The model parameters, to retrieve a model.
	 */
	private function get_image_model_params( ?string $model_slug, array $assoc_args ): array {
		// Assume any unknown arguments are generation configuration arguments.
		$generation_config_args = $assoc_args;
		$assoc_args             = $this->parse_assoc_args(
			$assoc_args,
			array(
				'feature'            => '',
				'system-instruction' => '',
			),
			$this->formatter_args
		);
		$generation_config_args = $this->sanitize_generation_config_args(
			array_diff_key( $generation_config_args, $assoc_args )
		);

		$capabilities = array( AI_Capability::IMAGE_GENERATION );

		return array(
			'feature'           => $assoc_args['feature'] ? $assoc_args['feature'] : null,
			'model'             => $model_slug,
			'capabilities'      => $capabilities,
			'generationConfig'  => Image_Generation_Config::from_array( $generation_config_args ),
			'systemInstruction' => $assoc_args['system-instruction'] ? $assoc_args['system-instruction'] : null,
		);
	}

	/**
	 * Sanitizes the generation configuration arguments.
	 *
	 * This method transforms hyphen-case keys to camelCase keys.
	 *
	 * @since 0.2.0
	 *
	 * @param array<string, mixed> $generation_config_args The generation configuration arguments.
	 * @return array<string, mixed> The sanitized generation configuration arguments.
	 */
	private function sanitize_generation_config_args( array $generation_config_args ): array {
		$sanitized_args = array();
		foreach ( $generation_config_args as $key => $value ) {
			// Transform hyphen-case to camelCase.
			if ( str_contains( $key, '-' ) ) {
				$key = str_replace( '-', '', lcfirst( ucwords( $key, '-' ) ) );
			}
			if ( 'responseSchema' === $key && is_string( $value ) ) {
				$value = json_decode( $value, true );
			}
			$sanitized_args[ $key ] = $value;
		}
		return $sanitized_args;
	}

	/**
	 * Retrieves the (text-based) model with the given slug and parameters.
	 *
	 * @since 0.2.0
	 *
	 * @param Generative_AI_Service $service      The service instance to get the model from.
	 * @param array<string, mixed>  $model_params The model parameters.
	 * @return Generative_AI_Model The model.
	 */
	private function get_model( Generative_AI_Service $service, array $model_params ): Generative_AI_Model {
		try {
			$model = $service->get_model( $model_params );
		} catch ( Generative_AI_Exception $e ) {
			WP_CLI::error(
				sprintf(
					'Getting the model failed: %s',
					html_entity_decode( $e->getMessage() )
				)
			);
		} catch ( InvalidArgumentException $e ) {
			WP_CLI::error(
				sprintf(
					'Invalid model slug or model params: %s',
					html_entity_decode( $e->getMessage() )
				)
			);
		}

		return $model;
	}

	/**
	 * Generates text content using the given generative model and prints it.
	 *
	 * @since 0.2.0
	 * @since 0.5.0 Now requires Content instance for second parameter instead of string.
	 *
	 * @param Generative_AI_Model $model   The model to use.
	 * @param Content             $content Prompt for the content to generate.
	 */
	private function generate_text_using_model( Generative_AI_Model $model, Content $content ): void {
		if ( ! $model instanceof With_Text_Generation ) {
			WP_CLI::error( 'The model does not support text generation.' );
		}

		try {
			$candidates = $model->generate_text( $content );
		} catch ( Generative_AI_Exception $e ) {
			WP_CLI::error(
				sprintf(
					'Generating text with model %1$s failed: %2$s',
					$model->get_model_slug(),
					html_entity_decode( $e->getMessage() )
				)
			);
		} catch ( InvalidArgumentException $e ) {
			WP_CLI::error(
				sprintf(
					'Invalid content provided to model %1$s: %2$s',
					$model->get_model_slug(),
					html_entity_decode( $e->getMessage() )
				)
			);
		}

		// Try finding content with text, otherwise just use the first candidate.
		$content = Helpers::get_text_content_from_contents(
			Helpers::get_candidate_contents( $candidates )
		);
		if ( ! $content ) {
			$content = Helpers::get_candidate_contents( $candidates )[0];
		}

		// If the content is purely text, print it directly, otherwise print the parts as structured JSON.
		$parts = $content->get_parts();
		if ( count( $parts ) === 1 && $parts->get( 0 ) instanceof Text_Part ) {
			WP_CLI::print_value( trim( $parts->get( 0 )->get_text() ), array( 'format' => 'table' ) );
			return;
		}
		WP_CLI::print_value( $parts->to_array(), array( 'format' => 'json' ) );
	}

	/**
	 * Generates text content using the given generative model, streaming the response and printing it as it comes in.
	 *
	 * @since 0.3.0
	 * @since 0.5.0 Now requires Content instance for second parameter instead of string.
	 *
	 * @param Generative_AI_Model $model   The model to use.
	 * @param Content             $content Prompt for the content to generate.
	 */
	private function stream_generate_text_using_model( Generative_AI_Model $model, Content $content ): void {
		if ( ! $model instanceof With_Text_Generation ) {
			WP_CLI::error( 'The model does not support text generation.' );
		}

		try {
			$candidates_generator = $model->stream_generate_text( $content );
		} catch ( Generative_AI_Exception $e ) {
			WP_CLI::error(
				sprintf(
					'Generating text with model %1$s failed: %2$s',
					$model->get_model_slug(),
					html_entity_decode( $e->getMessage() )
				)
			);
		} catch ( InvalidArgumentException $e ) {
			WP_CLI::error(
				sprintf(
					'Invalid content provided to model %1$s: %2$s',
					$model->get_model_slug(),
					html_entity_decode( $e->getMessage() )
				)
			);
		}

		try {
			foreach ( $candidates_generator as $candidates ) {
				$text = Helpers::get_text_from_contents(
					Helpers::get_candidate_contents( $candidates )
				);

				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $text;
			}
		} catch ( Generative_AI_Exception $e ) {
			WP_CLI::error(
				html_entity_decode( $e->getMessage() )
			);
		}
	}

	/**
	 * Generates an image using the given generative model and prints it.
	 *
	 * @since 0.5.0
	 *
	 * @param Generative_AI_Model $model   The model to use.
	 * @param Content             $content Prompt for the content to generate.
	 */
	private function generate_image_using_model( Generative_AI_Model $model, Content $content ): void {
		if ( ! $model instanceof With_Image_Generation ) {
			WP_CLI::error( 'The model does not support image generation.' );
		}

		try {
			$candidates = $model->generate_image( $content );
		} catch ( Generative_AI_Exception $e ) {
			WP_CLI::error(
				sprintf(
					'Generating image with model %1$s failed: %2$s',
					$model->get_model_slug(),
					html_entity_decode( $e->getMessage() )
				)
			);
		} catch ( InvalidArgumentException $e ) {
			WP_CLI::error(
				sprintf(
					'Invalid content provided to model %1$s: %2$s',
					$model->get_model_slug(),
					html_entity_decode( $e->getMessage() )
				)
			);
		}

		$contents = Helpers::get_candidate_contents( $candidates );
		foreach ( $contents as $content ) {
			// If the content is purely an image, print it directly, otherwise print the parts as structured JSON.
			$parts = $content->get_parts();
			if ( count( $parts ) === 1 && $parts->get( 0 ) instanceof Inline_Data_Part ) {
				WP_CLI::print_value( $parts->get( 0 )->get_base64_data(), array( 'format' => 'table' ) );
				continue;
			}
			if ( count( $parts ) === 1 && $parts->get( 0 ) instanceof File_Data_Part ) {
				WP_CLI::print_value( $parts->get( 0 )->get_file_uri(), array( 'format' => 'table' ) );
				continue;
			}
			WP_CLI::print_value( $parts->to_array(), array( 'format' => 'json' ) );
		}
	}

	/**
	 * Parses and validates the associative arguments.
	 *
	 * @since 0.2.0
	 *
	 * @param array<string, mixed> $assoc_args      Map of the associative arguments and their values.
	 * @param array<string, mixed> $defaults        Map of the default argument values.
	 * @param string[]             $optional_fields Optional fields that can be included.
	 * @return array<string, mixed> Map of the associative arguments and their values.
	 *
	 * @throws WP_CLI\ExitException Thrown if an invalid argument is provided.
	 */
	private function parse_assoc_args( array $assoc_args, array $defaults, array $optional_fields = array() ): array {
		$allowed_fields = array_merge( array_keys( $defaults ), $optional_fields );

		$assoc_args = wp_parse_args( $assoc_args, $defaults );
		return wp_array_slice_assoc( $assoc_args, $allowed_fields );
	}

	/**
	 * Gets the formatter instance to format CLI output.
	 *
	 * @since 0.2.0
	 *
	 * @param array<string, mixed> $assoc_args     Associative arguments.
	 * @param string[]             $default_fields Default fields to display.
	 * @return Formatter The CLI formatter instance.
	 */
	private function get_formatter( array $assoc_args, array $default_fields ): Formatter {
		return new Formatter(
			$assoc_args,
			$default_fields
		);
	}

	/**
	 * Conditionally bypasses any AI related capability requirements if no user is specified.
	 *
	 * This is expected behavior as WP-CLI by default has access to everything.
	 *
	 * @since 0.5.0
	 */
	private function maybe_bypass_cap_requirements(): void {
		// If a user is specified, we should not bypass capability checks.
		if ( 0 !== $this->current_user->get_id() ) {
			return;
		}

		// Allow access to all AI services by default.
		$this->capability_controller->set_meta_map_callback(
			'ais_access_service',
			function () {
				return array( 'exist' );
			}
		);
	}
}
