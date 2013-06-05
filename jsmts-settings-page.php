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
			<option value="<?php echo $dir; ?>" <?php selected($dir, $options['mobile_theme']); ?>><?php echo $theme['Name']; ?></option>
		<?php endforeach; ?>
		</select>
	</label>
</p><p>
	<label for="jsmts_mobile_theme">
		Tablet:
		<select name="tablet_theme" id="jsmts_tablet_theme">
			<option value="">Use primary theme</option>
		<?php foreach ($themes as $dir => $theme) : ?>
			<option value="<?php echo $dir; ?>" <?php selected($dir, $options['tablet_theme']); ?>><?php echo $theme['Name']; ?></option>
		<?php endforeach; ?>
		</select>
	</label>
</p><p>
	<input type="submit" name="save" value="Update settings" />
</p>
</form>
<?php
