<?php
/*
Plugin Name: JS Mobile Theme Switcher
Plugin URI: http://mapdigital.com.au/
Author: Map Digital
Author URI: http://mapdigital.com.au/
Description: Plugin for serving different themes to mobile websites. Browser detection is JavaScript-driven, so this plugin is compatible with WPEngine hosting.
Version: 1.0
*/

/**
 * Mobile theme switcher main plugin class namespace
 *
 * :TODO:
 * - implement other theme state methods (sessions, cookies, ...)
 *
 * @author	Sam Pospischil <pospi@spadgos.com>
 * @since	5 Jun 2013
 */
abstract class JSMobileThemeSwitcher
{
	const SCRIPT_VERSION = '1.0';

	private static $options;
	private static $themes;

	public static function init()
	{
		$cls = get_class();

		// enqueue the javascript
		add_action('wp_enqueue_scripts', array($cls, 'enqueueJS'));
		add_action('wp_head', array($cls, 'renderJSVariables'));

		// intercept template and stylesheet rendering with configured themes
		if (!is_admin()) {
			add_filter('template', array($cls, 'handleTemplate'));
			add_filter('stylesheet', array($cls, 'handleStylesheet'));
		}

		// add configuration UI
		add_action('admin_menu', array($cls, 'setupAdminScreens'));
		add_action('load-appearance_page_js-mobile-themes', array($cls, 'handleOptions'));

		// uninstall & installation hooks
		register_activation_hook(__FILE__, array($cls, 'runInstall'));
		register_uninstall_hook(__FILE__, array($cls, 'runUninstall'));
	}

	//----------------------------------------------------------------------------------------------------------------------------------------------------
	//	Plugin functionality
	//----------------------------------------------------------------------------------------------------------------------------------------------------

	public static function enqueueJS()
	{
		wp_register_script('mts-js', plugins_url('mobile-theme-switch.js', __FILE__), array('jquery'), self::SCRIPT_VERSION, true);
		wp_enqueue_script('mts-js');
	}

	public static function renderJSVariables()
	{
		$opts = self::getOptions();
		?>
		<script type="text/javascript">
			var JSMTS = {
				check_mobile : <?php echo $opts['mobile_theme'] ? 'true' : 'false'; ?>,
				check_tablet : <?php echo $opts['tablet_theme'] ? 'true' : 'false'; ?>,
				method : '<?php echo $opts['state_method']; ?>',
				key : '<?php echo $opts['state_key']; ?>'
			};
		</script>
		<?php
	}

	public static function handleTemplate($template)
	{
		$opts = self::getOptions();

		return $template;
	}

	public static function handleStylesheet($stylesheet)
	{
		$opts = self::getOptions();

		return $stylesheet;
	}

	//----------------------------------------------------------------------------------------------------------------------------------------------------
	//	Utility methods
	//----------------------------------------------------------------------------------------------------------------------------------------------------

	public static function getOptions()
	{
		if (!isset(self::$options)) {
			self::$options = array(
				'mobile_theme'	=> get_option('jsmts_mobile_theme'),
				'tablet_theme'	=> get_option('jsmts_tablet_theme'),
				'state_method'	=> get_option('jsmts_state_method'),
				'state_key'		=> get_option('jsmts_state_key'),
			);
		}
		return self::$options;
	}

	public static function getAvailableThemes()
	{
		if (isset(self::$themes)) {
			return self::$themes;
		}

		if (function_exists('wp_get_themes')) {
			self::$themes = wp_get_themes();
		} else {
			// :NOTE: backwards compatibility for Wordpress < 3.4
			self::$themes = get_themes();
		}

		return self::$themes;
	}

	//----------------------------------------------------------------------------------------------------------------------------------------------------
	//	Administration UI
	//----------------------------------------------------------------------------------------------------------------------------------------------------

	public static function setupAdminScreens()
	{
		add_submenu_page('themes.php',  __('Mobile Themes'), __('Mobile Themes'), 'manage_options', 'js-mobile-themes', array(get_class(), 'drawSettingsPage'));
	}

	public static function drawSettingsPage()
	{
		include('jsmts-settings-page.php');
	}

	public static function handleOptions()
	{
		if (!empty($_POST)) {
			update_option('jsmts_mobile_theme', empty($_POST['mobile_theme']) ? false : $_POST['mobile_theme']);
			update_option('jsmts_tablet_theme', empty($_POST['tablet_theme']) ? false : $_POST['tablet_theme']);
			update_option('jsmts_state_method', $_POST['state_method']);
			update_option('jsmts_state_key', $_POST['state_key']);

			add_action('admin_notices', array(get_class(), 'handleUpdateNotice'));
		}
	}

	public static function handleUpdateNotice()
	{
		echo '<div class="updated"><p>Settings saved.</p></div>';
	}

	public static function runInstall()
	{
		update_option('jsmts_state_method', 'qs');
		update_option('jsmts_state_key', 'v');
	}

	public static function runUninstall()
	{
		delete_option('jsmts_mobile_theme');
		delete_option('jsmts_tablet_theme');
		delete_option('jsmts_state_method');
		delete_option('jsmts_state_key');
	}
}
JSMobileThemeSwitcher::init();
