<?php
/**
 * Mobile theme switcher admin settings page HTML
 *
 * @author	Sam Pospischil <pospi@spadgos.com>
 * @since	5 Jun 2013
 */

$themes = JSMobileThemeSwitcher::getAvailableThemes();
$options = JSMobileThemeSwitcher::getOptions();

// sort themes for display in list
natcasesort($themes);

?>
<h1>Mobile Themes</h1>

<form method="post">
	<h3>Select themes</h3>
<p>
	<label for="jsmts_mobile_theme">
		Mobile:
		<select name="mobile_theme" id="jsmts_mobile_theme">
			<option value="">Use primary theme</option>
		<?php foreach ($themes as $dir => $theme) : ?>
			<option value="<?php echo $dir; ?>"<?php selected($dir, $options['mobile_theme']); ?>><?php echo $theme['Name']; ?></option>
		<?php endforeach; ?>
		</select>
	</label>
</p><p>
	<label for="jsmts_tablet_theme">
		Tablet:
		<select name="tablet_theme" id="jsmts_tablet_theme">
			<option value="">Use primary theme</option>
		<?php foreach ($themes as $dir => $theme) : ?>
			<option value="<?php echo $dir; ?>"<?php selected($dir, $options['tablet_theme']); ?>><?php echo $theme['Name']; ?></option>
		<?php endforeach; ?>
		</select>
	</label>
</p><p>

	<h3>State mechanism</h3>
	<blockquote>
		These settings determine the way in which the mobile theme is persisted between page views.
		Using URL parameters has greatest compatibility, but may require additional coding in your theme's URL handling methods to persist values correctly without requiring re-detection. Use this method if you have advanced pre-Wordpress caching mechanisms on your server, for example WPEngine's EverCache.
		Cookies have the widest support otherwise, whilst sessions will require that your server has PHP session handling enabled (this is not utilised by Wordpress by default, and is often disabled on specialised hosting).
	</blockquote>
<p>
	<label for="jsmts_state_method">
		Persist using:
		<select name="state_method" id="jsmts_state_method">
			<option value="qs"<?php selected('qs', $options['state_method']); ?>>URL parameters</option>
			<option value="c"<?php selected('c', $options['state_method']); ?>>Cookies</option>
			<option value="s"<?php selected('s', $options['state_method']); ?>>Sessions</option>
		</select>
	</label>
</p>
<p>
	<label for="jsmts_state_key">
		State key:
		<input type="text" name="state_key" id="jsmts_state_key" value="<?php echo esc_attr($options['state_key']); ?>" /><br />
		<small>(default can be overridden for advanced use where it would conflict with variables used by your themes)</small>
	</label>
</p>

<p>
	<input type="submit" name="save" value="Update settings" />
</p>
</form>
<?php
