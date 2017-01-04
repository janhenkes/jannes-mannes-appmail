<?php
/*
Plugin Name: Jannes & Mannes AppMail
Plugin URI:
Description:
Author: Jannes & Mannes
Version: 0.3
Author URI: https://www.jannesmannes.nl
*/

spl_autoload_register( function ( $class ) {
	$filename = dirname( __FILE__ ) . '/' . str_replace( '\\', '/', $class ) . '.php';
	if ( file_exists( $filename ) ) {
		require $filename;
	}
} );

require dirname( __FILE__ ) . '/vendor/autoload.php';

require dirname( __FILE__ ) . '/lib/appmail-wp-mail.php';

\JmAppMail\Plugin::init();
//\JmAppMail\Plugin::test();