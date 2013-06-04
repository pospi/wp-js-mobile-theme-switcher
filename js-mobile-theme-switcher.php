<?php
/*
Plugin Name: JS Mobile Theme Switcher
Plugin URI: http://mapdigital.com.au/
Author: Map Digital
Author URI: http://mapdigital.com.au/
Description: Plugin for serving different themes to mobile websites. Browser detection is JavaScript-driven, so this plugin is compatible with WPEngine hosting.
Version: 1.0
*/

abstract class JSMobileThemeSwitcher
{
	const SCRIPT_VERSION = '1.0';

	public static function init()
	{
		$cls = get_class();

		add_action('wp_enqueue_scripts', array($cls, 'enqueueJS'));
	}

	public static function enqueueJS()
	{
		wp_register_script('mts-js', plugins_url('mobile-theme-switch.js', __FILE__), array('jquery'), self::SCRIPT_VERSION, true);
		wp_enqueue_script('mts-js');
	}
}
JSMobileThemeSwitcher::init();
