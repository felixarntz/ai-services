=== AI Services ===

Plugin Name:  AI Services
Plugin URI:   https://wordpress.org/plugins/ai-services/
Author:       Felix Arntz
Author URI:   https://felix-arntz.me
Tested up to: 6.6
Stable tag:   1.0.0
License:      GPLv2 or later
License URI:  https://www.gnu.org/licenses/old-licenses/gpl-2.0.html

Makes AI centrally available in WordPress, whether via PHP, REST API, JavaScript, or WP-CLI - for any provider.

== Description ==

This WordPress plugin introduces central infrastructure which allows other plugins to make use of AI capabilities. It exposes APIs that can be used in various contexts, whether you need to use AI capabilities in server-side or client-side code. Furthermore, the APIs are agnostic of the AI service - whether that's Anthropic, Google, or OpenAI, to only name a few, you can use any of them in the same way. You can also register your own implementation of another service, if it is not supported out of the box.

The plugin does intentionally _not_ come with specific AI driven features built-in, except for a simple WordPress support assistant chatbot that can be disabled if not needed. The purpose of this plugin is to facilitate use of AI by other plugins. As such, it is a perfect use-case for [plugin dependencies](https://make.wordpress.org/core/2024/03/05/introducing-plugin-dependencies-in-wordpress-6-5/).

= Why? =

* A centralized AI infrastructure **facilitates user choice**. Users may prefer certain AI services over other ones, and for many common tasks, either of the popular AI services is suitable. Having a common API regardless of the AI service allows leaving the choice to the user, rather than the plugin author.
* Since the centralized AI infrastructure comes with a common API that works the same for every AI service, it means **plugin developers don't have to spend as much time familiarizing themselves with different services**, at least when it comes to simple tasks. For tasks where certain services may have advantages over others, there is still flexibility to focus on a specific AI service.
* It also means **no more reinventing the wheel**: Since most AI services do not provide PHP SDKs for their APIs, many times this means WordPress plugins that want to leverage AI have to implement their own layer around the service's API. Not only is that time consuming, it also distracts from working on the actual (AI driven) features that the plugin should offer to its users. In fact this directly facilitates the user choice aspect mentioned, as having APIs for various AI services already provided means you can simply make those available to your plugin users.
* Having central AI infrastructure available **unlocks AI capabilities for smaller plugins or features**: It may not be worth the investment to implement a whole AI API layer for a simple AI driven feature, but when you already have it available, it can lead to more plugins (and thus more users) benefitting from AI capabilities.
* Last but not least, a central AI infrastructure means **users will only have to configure the AI API once**, e.g. paste their API keys only in a single WordPress administration screen. Without central AI infrastructure, every plugin has to provide its own UI for pasting API keys, making the process more tedious for site owners the more AI capabilities their site uses.

= Examples =

`
// Generate the answer to a prompt in PHP code.
if ( ai_services()->has_available_services() ) {
	$service = ai_services()->get_available_service();
	try {
		$result = $service->get_model()->generate_text( 'What can I do with WordPress?' );
	} catch ( Exception $e ) {
		// Handle the exception.
	}
}
`

`
// Generate the answer to a prompt in JavaScript code.
const { hasAvailableServices, getAvailableService } = wp.data.select( 'ai-services/ai' );
if ( hasAvailableServices() ) {
	const service = getAvailableService();
	try {
		const result = await service.generateText( 'What can I do with WordPress?' );
	} catch ( error ) {
		// Handle the error.
	}
}
`

`
// Generate the answer to a prompt using WP-CLI.
wp ai-services generate-text 'What can I do with WordPress?'
`

You can also use a specific AI service, if you have a preference, for example the `google` service:
`
// Generate the answer to a prompt using a specific AI service, in PHP code.
if ( ai_services()->is_service_available( 'google' ) ) {
	$service = ai_services()->get_available_service( 'google' );
	try {
		$result = $service->get_model()->generate_text( 'What can I do with WordPress?' );
	} catch ( Exception $e ) {
		// Handle the exception.
	}
}
`

`
# Generate the answer to a prompt using a specific AI service, using the REST API via cURL.
curl 'https://example.com/wp-json/ai-services/v1/services/google:generate-text' \
  -H 'Content-Type: application/json' \
  --data-raw '{"content":"What can I do with WordPress?"}'
`

== Installation ==

= Installation from within WordPress =

1. Visit **Plugins > Add New**.
2. Search for **AI Services**.
3. Install and activate the AI Services plugin.

= Manual installation =

1. Upload the entire `ai-services` folder to the `/wp-content/plugins/` directory.
2. Visit **Plugins**.
3. Activate the AI Services plugin.

== Frequently Asked Questions ==

FAQ section content.

== Changelog ==

= 1.0.0 =

* First stable version.
