<?php
/**
 * Created by PhpStorm.
 * User: janhenkes
 * Date: 11/10/16
 * Time: 10:35
 */

namespace JmAppMail;


class Admin {
	private static $options;
	const option = 'jm_appmail';
	const settings_group = 'jm_appmail_settings_group';

	public static function init() {
		add_action( 'admin_menu', [ get_called_class(), 'add_menu_page' ] );
		add_action( 'admin_init', [ get_called_class(), 'register_settings' ] );
	}

	public static function add_menu_page() {
		add_menu_page( _x( 'AppMail.io', '', Plugin::text_domain ), 'AppMail.io', 'manage_options', Plugin::text_domain,
			[
				get_called_class(),
				'auto_publisher_page'
			] );
	}

	public static function get_options() {
		if ( ! is_null( self::$options ) ) {
			return self::$options;
		}

		return self::$options = get_option( self::option );
	}

	public static function auto_publisher_page() {
		// Set class property
		self::get_options();
		?>
		<div class="wrap">
			<h1><?php _ex( 'AppMail.io', 'page title', Plugin::text_domain ) ?></h1>
			<form method="post" action="options.php">
				<?php
				// This prints out all hidden setting fields
				settings_fields( self::settings_group );
				do_settings_sections( self::settings_group );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	public static function register_settings() {
		//register our settings
		register_setting( self::settings_group, self::option, array( get_called_class(), 'sanitize' ) );

		add_settings_section( 'section_api_key', // ID
			'API key', // Title
			array( get_called_class(), 'print_section_info' ), // Callback
			self::settings_group // Page
		);

		add_settings_field( 'api_key', 'API key', array(
			get_called_class(),
			'api_key_callback'
		), self::settings_group, 'section_api_key' );

		add_settings_field( 'from_email', 'From email', array(
			get_called_class(),
			'from_email_callback'
		), self::settings_group, 'section_api_key' );
	}

	/**
	 * Print the Section text
	 */
	public static function print_section_info() {
		_ex( 'Set your API key', 'Admin page', Plugin::text_domain );
	}

	public static function api_key_callback() {
		if ( ! isset( self::$options['api_key'] ) ) {
			self::$options['api_key'] = '';
		}
		printf( '<input type="text" name="%s[api_key]" id="api_key" value="%s" />', self::option,
			self::$options['api_key'] );
	}

	public static function from_email_callback() {
		if ( ! isset( self::$options['from_email'] ) ) {
			self::$options['from_email'] = '';
		}
		printf( '<input type="text" name="%s[from_email]" id="from_email" value="%s" />', self::option,
			self::$options['from_email'] );
	}

	/**
	 * Sanitize each setting field as needed
	 *
	 * @param array $input Contains all settings fields as array keys
	 *
	 * @return array
	 */
	public static function sanitize( $input ) {
		$new_input = array();

		if ( isset( $input['api_key'] ) ) {
			$new_input['api_key'] = $input['api_key'];
		}

		if ( isset( $input['from_email'] ) ) {
			$new_input['from_email'] = $input['from_email'];
		}

		return $new_input;
	}
}