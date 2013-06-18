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
 * - implement other theme state methods (cookies, localstorage ...)
 *
 * @author	Sam Pospischil <pospi@spadgos.com>
 * @since	5 Jun 2013
 */
abstract class JSMobileThemeSwitcher
{
	const SCRIPT_VERSION = '1.0';

	private static $options;
	private static $themes;

	const FLAG_MOBILE = 'm';
	const FLAG_TABLET = 't';
	const FLAG_DESKTOP = 'd';

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

		// inject querystring parameter into as many link methods as we can when using it for state
		if (get_option('jsmts_state_method') == 'qs') {
			add_filter('post_link', array($cls, 'passPermalinkState'));
			add_filter('post_type_link', array($cls, 'passPermalinkState'));
			add_filter('page_link', array($cls, 'passPermalinkState'));
			add_filter('attachment_link', array($cls, 'passPermalinkState'));
			add_filter('year_link', array($cls, 'passPermalinkState'));
			add_filter('month_link', array($cls, 'passPermalinkState'));
			add_filter('day_link', array($cls, 'passPermalinkState'));
			add_filter('search_link', array($cls, 'passPermalinkState'));
			add_filter('post_type_archive_link', array($cls, 'passPermalinkState'));
			add_filter('get_pagenum_link', array($cls, 'passPermalinkState'));
			add_filter('get_comments_pagenum_link', array($cls, 'passPermalinkState'));
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
		wp_register_script('mts-js', plugins_url('mobile-theme-switch.js', __FILE__), array(), self::SCRIPT_VERSION, true);
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

		// find the theme override (if any)
		$theme = self::getOverriddenTheme();
		if (!$theme) {
			return $template;
		}

		// check for child theme and return parent's template if there is one
		if ($theme['Template'] != "") {
			return $theme['Template'];
		}
		return $theme['Stylesheet'];
	}

	public static function handleStylesheet($stylesheet)
	{
		$opts = self::getOptions();

		// find the theme override (if any)
		$theme = self::getOverriddenTheme();
		if (!$theme) {
			return $stylesheet;
		}

		return $theme['Stylesheet'];
	}

	private static function getOverriddenTheme()
	{
		$opts = self::getOptions();

		// abort early if we have nothing to do
		if (!$opts['mobile_theme'] && !$opts['tablet_theme']) {
			return false;
		}

		// check to see if we're overriding the default theme
		$themeOverride = self::getPersistedOverrideValue();

		switch ($themeOverride) {
			case self::FLAG_MOBILE:
				$themeOverride = $opts['mobile_theme'];
				break;
			case self::FLAG_TABLET:
				$themeOverride = $opts['tablet_theme'];
				break;
			default:
				$themeOverride = null;
				break;
		}

		// if no device-specific override detected, nothing to do
		if (!$themeOverride) {
			return false;
		}

		// check if the theme we've specified is still installed (paranoia)
		$themes = self::getAvailableThemes();
		if (!isset($themes[$themeOverride])) {
			return false;
		}

		return $themes[$themeOverride];
	}

	private static function getPersistedOverrideValue()
	{
		$opts = self::getOptions();

		switch ($opts['state_method']) {
			case 'c':
				return isset($_COOKIE[$opts['state_key']]) ? $_COOKIE[$opts['state_key']] : null;
			default:
				return isset($_GET[$opts['state_key']]) ? $_GET[$opts['state_key']] : null;
		}
	}

	//----------------------------------------------------------------------------------------------------------------------------------------------------
	//	Link handling hooks for when running in QueryString persistence mode
	//----------------------------------------------------------------------------------------------------------------------------------------------------

	public static function passPermalinkState($link, $unused1 = null, $unused2 = null, $unused3 = null)
	{
		$override = self::getPersistedOverrideValue();
		if ($override) {
			$opts = self::getOptions();
			return add_query_arg($opts['state_key'], $override, $link);
		}
		return $link;
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
				'state_key2'	=> get_option('jsmts_state_key2'),
				'do_canonical'	=> get_option('jsmts_do_canonical'),
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
			update_option('jsmts_state_key2', $_POST['state_key2']);
			update_option('jsmts_do_canonical', !empty($_POST['do_canonical']));

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
		delete_option('jsmts_state_key2');
		delete_option('jsmts_do_canonical');
	}
}
JSMobileThemeSwitcher::init();
