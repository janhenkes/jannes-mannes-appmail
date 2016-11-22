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
}