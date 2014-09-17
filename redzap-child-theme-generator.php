<?php
/*
Plugin Name: RedZap Child Theme Generator
Plugin URI: 
Description: A plugin to create child themes based off an installed theme.
Version: 1.0
Author: voldemortensen, (add curtis)
Author URI: 
Text Domain: rzct
License: GPLv2 only
License URI: http://www.gnu.org/licenses/gpl-2.0.html

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License version 2,
    as published by the Free Software Foundation.

    You may NOT assume that you can use any other version of the GPL.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    The license for this software can likely be found here:
    http://www.gnu.org/licenses/gpl-2.0.html

*/

// Load the textdomain
add_action('init', 'rzct_load_textdomain');
function rzct_load_textdomain() {
	load_plugin_textdomain('rzct', false, dirname(plugin_basename(__FILE__)));
}


add_action('admin_menu', 'rzct_admin_add_page');
function rzct_admin_add_page() {
	add_submenu_page(
		'tools.php',
		esc_html__('Create a Child Theme','rzct'),
		esc_html__('Create a Child Theme','rzct'),
		'switch_themes',
		'rzct',
		'rzct_options_page'
	);
}

function rzct_options_page() {
	$results = rzct_create_theme();

	if ( $results === true ) return;
	?>
	<div class="wrap">
		<?php screen_icon(); ?>
		<h2><?php esc_html_e('Create a Child Theme','rzct'); ?></h2>
		<?php settings_errors(); ?>
		<form method="post" action="">
		<?php wp_nonce_field('rzct_nonce'); ?>
		<table class="form-table">
		<?php
		$opts = array(
			'name' => esc_html__('Child Theme Name', 'rzct'),
			'template' => search_theme_directories(),
			'uri' => esc_html__('Theme URI (optional)', 'rzct'),
			'description' => esc_html__('Description (optional)', 'rzct'),
			'version' => esc_html__('Version (optional)', 'rzct'),
			'author' => esc_html__('Author (optional)', 'rzct'),
			'author_uri' => esc_html__('Author URI (optional)', 'rzct'),
		);

		echo "<tr valign='top'><th scope='row'>" .esc_html__('Template', 'rzct') . "</th><td><select name='rzct_template'>";
		foreach( $opts['template'] as $k=>$v ) {
			echo "<option value='" . $k . "'>" . $k . "</option>";
		}
		echo "</select></td></tr>";
		echo "<tr valign='top'><th scope='row'>{$opts['name']}</th><td><input class='regular-text' type='text' name='rzct_name' value=''></td></tr>
			<tr valign='top'><th scope='row'>{$opts['uri']}</th><td><input class='regular-text' type='text' name='rzct_uri' value=''></td></tr>
			<tr valign='top'><th scope='row'>{$opts['description']}</th><td><input class='regular-text' type='text' name='rzct_description' value=''></td></tr>
			<tr valign='top'><th scope='row'>{$opts['version']}</th><td><input class='regular-text' type='text' name='rzct_version' value=''></td></tr>
			<tr valign='top'><th scope='row'>{$opts['author']}</th><td><input class='regular-text' type='text' name='rzct_author' value=''></td></tr>
			<tr valign='top'><th scope='row'>{$opts['author_uri']}</th><td><input class='regular-text' type='text' name='rzct_author_uri' value=''></td></tr>";

		?>
		</table>
		<?php submit_button( esc_html__('Create a child theme and activate it!', 'rzct') ); ?>
		</form>
	</div>
<?php
}

function rzct_create_theme() {
	if ( 'POST' != $_SERVER['REQUEST_METHOD'] )
		return false;

	check_admin_referer('rzct_nonce');

	// remove the magic quotes
	$_POST = stripslashes_deep( $_POST );

	if (empty($_POST['rzct_name'])) {
		add_settings_error( 'rzct', 'required_name',esc_html__('Theme Name is required', 'rzct'), 'error' );
		return $_POST;
	}

	if (empty($_POST['rzct_template'])) {
		add_settings_error( 'rzct', 'required_template', esc_html__('Template is required.', 'rzct'), 'error' );
		return $_POST;
	}

	if ( empty($_POST['rzct_slug'] ) ) {
		$_POST['rzct_slug'] = sanitize_title($_POST['rzct_name']);
	} else {
		$_POST['rzct_slug'] = sanitize_title($_POST['rzct_slug']);
	}

	if ( file_exists(trailingslashit(WP_PLUGIN_DIR).$_POST['rzct_slug'] ) ) {
		add_settings_error( 'rzct', 'existing_theme', esc_html__('That theme appears to already exist. Use a different slug or name.', 'rzct'), 'error' );
		return $_POST;
	}

	$form_fields = array ('rzct_name', 'rzct_uri', 'rzct_description', 'rzct_version',
				'rzct_author', 'rzct_author_uri');
	$method = ''; // TODO TESTING

	// okay, let's see about getting credentials
	$url = wp_nonce_url('tools.php?page=rzct','rzct_nonce');
	if (false === ($creds = request_filesystem_credentials($url, $method, false, false, $form_fields) ) ) {
		return true;
	}

	// now we have some credentials, try to get the wp_filesystem running
	if ( ! WP_Filesystem($creds) ) {
		// our credentials were no good, ask the user for them again
		request_filesystem_credentials($url, $method, true, false, $form_fields);
		return true;
	}


	global $wp_filesystem;

	// create the theme directory
	$themedir = $wp_filesystem->wp_themes_dir() . $_POST['rzct_slug'];

	if ( ! $wp_filesystem->mkdir($themedir) ) {
		add_settings_error( 'rzct', 'create_directory', esc_html__('Unable to create the theme directory.', 'rzct'), 'error' );
		return $_POST;
	}

	// create the theme header

	$header = <<<END
/*
Theme Name: {$_POST['rzct_name']}
Template: {$_POST['rzct_template']}
Description: {$_POST['rzct_description']}
Version: {$_POST['rzct_version']}
Author: {$_POST['rzct_author']}
Author URI: {$_POST['rzct_author_uri']}
*/

@import url("../{$_POST['rzct_template']}/style.css");

/* =Theme customization starts here
--------------------------------- */
END;

	$themefile = trailingslashit($themedir).'style.css';

	if ( ! $wp_filesystem->put_contents( $themefile, $header, FS_CHMOD_FILE) ) {
		add_settings_error( 'rzct', 'create_file', esc_html__('Unable to create the theme file.', 'rzct'), 'error' );
	}

	$themeslug = $_POST['rzct_slug'].'/'.$_POST['rzct_slug'].'.css';
	$themeeditor = admin_url('theme-editor.php?file=style.css&theme='.$_POST['rzct_slug']);

	if ( null !== switch_theme( $_POST['rzct_template'], $_POST['rzct_slug']  ) ) {
		add_settings_error( 'rzct', 'activate_theme', esc_html__('Unable to activate the new theme.', 'rzct'), 'error' );
	}

	// theme created and activated, redirect to the theme editor
	?>
	<script type="text/javascript">
	<!--
	window.location = "<?php echo $themeeditor; ?>"
	//-->
	</script>
	<?php

	/* translators: inline link to theme editor */
	$message = sprintf(esc_html__('The new theme has been created and activated. You can %sgo to the editor%s if your browser does not redirect you.', 'rzct'), '<a href="'.$themeeditor.'">', '</a>');

	add_settings_error('rzct', 'theme_active', $message, 'rzct', 'updated');

	return true;
}

