<?php
/**
 * Created by PhpStorm.
 * User: janhenkes
 * Date: 11/10/16
 * Time: 10:34
 */

namespace JmAppMail;


class Plugin {
	const text_domain = 'jm_appmail';

	public static function init() {
		Admin::init();
	}

	public static function test() {
		add_action( 'init', function () {
			wp_mail( 'jan@jannesmannes.nl', 'Test', 'Test message', 'From: jan@jannesmannes.com' );
		} );
	}

	public static function html2text( $message ) {
		$message = wpautop( $message, true );
		$message = _sanitize_text_fields( $message, true );
		$message = preg_replace( '/\h+/', ' ', $message );
		$message = preg_replace( "/[\r\n]+/", "\n", $message );
		$message = join( "\n", array_map( "trim", explode( "\n", $message ) ) );

		return $message;
	}
}