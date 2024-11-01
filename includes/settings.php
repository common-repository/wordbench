<?php

/**
 * Callback for admin_init action. Initializes setting for this plugin.
 */
function wordbench_settings_admin_init() {
	register_setting( 'wordbench_settings', 'wordbench_settings',
		'wordbench_settings_validate' );

	add_settings_section( 'wordbench_main', 'Main Settings',
		'wordbench_settings_main_section', 'wordbench' );

	add_settings_field( 'wordbench_enable_comments', 'Blog Comments',
		'wordbench_settings_enable_comments_field',
		'wordbench', 'wordbench_main' );
}

/**
 * Callback for admin_menu action. Adds plugin settings page to admin interface.
 */
function wordbench_settings_admin_menu() {
	add_options_page( 'WordBench Plugin Settings', 'WordBench',
		'manage_options', 'wordbench-settings', 'wordbench_settings_form' );
}

/**
 * Callback for register_setting(). Validates user-submitted form data.
 */
function wordbench_settings_validate( $args = array() ) {
	$settings = wordbench_get_current_settings();
	$settings['enable_comments'] = isset( $args['enable_comments'] );

	return apply_filters( 'wordbench_validate_settings', $settings );
}

/**
 * Callback for wordbench_settings_admin_menu(). Renders HTML for settings page.
 */
function wordbench_settings_form() {
?>
<div class="wrap">
	<div id="icon-options-general" class="icon32"><br></div>
	<h2>WordBench Settings</h2>
	<form action="options.php" method="post">
		<?php settings_fields( 'wordbench_settings' ); ?>
		<?php do_settings_sections( 'wordbench' ); ?>
		<p class="submit"><input type="submit" value="Save Changes" class="button-primary"></p>
	</form>
</div>
<?php
}

/**
 * Callback for add_settings_section(). Renders section header on settings page.
 */
function wordbench_settings_main_section() {
?>
<p>This is the main section.</p>
<?php
}

/**
 * Callback for add_settings_field(). Renders input field for setting.
 */
function wordbench_settings_enable_comments_field() {
	$settings = wordbench_get_current_settings();

	$checked = $settings['enable_comments'] ? ' checked="checked"' : '';
?>
<input id="wb-settings-enable-comments" type="checkbox" name="wordbench_settings[enable_comments]"<?php echo $checked; ?>>
<label for="wb-settings-enable-comments">Enable comments on blog posts.</label>
<?php
}

/**
 * Returns default values for all plugin settings.
 *
 * @see wordbench_get_current_settings()
 * @return array Returns default settings as associative array.
 */
function wordbench_get_default_settings() {
	$defaults = array(
		'enable_comments' => true
	);

	return apply_filters( 'wordbench_default_settings', $defaults );
}

/**
 * Returns current values for all plugin settings.
 *
 * @uses wordbench_get_default_settings()
 * @return array Returns default settings as associative array.
 */
function wordbench_get_current_settings() {
	$defaults = wordbench_get_default_settings();
	$settings = get_option( 'wordbench_settings', array() );
	$settings = wp_parse_args( $settings, $defaults );

	return apply_filters( 'wordbench_current_settings', $settings );
}
