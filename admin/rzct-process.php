<?php

function rzct_create_theme() {
	if ( 'POST' != $_SERVER['REQUEST_METHOD'] )
		return false;

	check_admin_referer('rzct_nonce');

	if( !current_user_can('manage_options') )
		return false;

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
		$_POST['rzct_slug'] = sanitize_title( $_POST['rzct_name'] );
	} else {
		$_POST['rzct_slug'] = sanitize_title( $_POST['rzct_slug'] );
	}

	if ( file_exists(trailingslashit( WP_PLUGIN_DIR ) . $_POST['rzct_slug'] ) ) {
		add_settings_error( 'rzct', 'existing_theme', esc_html__('That theme appears to already exist. Use a different slug or name.', 'rzct'), 'error' );
		return $_POST;
	}

	$form_fields = array ('rzct_name', 'rzct_uri', 'rzct_description', 'rzct_version',
				'rzct_author', 'rzct_author_uri');
	$method = ''; // TODO TESTING

	// okay, let's see about getting credentials
	$url = wp_nonce_url('tools.php?page=rzct','rzct_nonce');
	if (false === ($creds = request_filesystem_credentials( $url, $method, false, false, $form_fields ) ) ) {
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

	if ( ! $wp_filesystem->mkdir( $themedir ) ) {
		add_settings_error( 'rzct', 'create_directory', esc_html__('Unable to create the theme directory.', 'rzct'), 'error' );
		return $_POST;
	}

	// create the theme header

	$themeheader = <<<END
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

	$themefile = trailingslashit( $themedir ) . 'style.css';

	if ( ! $wp_filesystem->put_contents( $themefile, $themeheader, FS_CHMOD_FILE ) ) {
		add_settings_error( 'rzct', 'create_file', esc_html__('Unable to create the theme file.', 'rzct'), 'error' );
	}
	$themeslug = $_POST['rzct_slug']. '/' . $_POST['rzct_slug'] . '.css';
	$themeeditor = admin_url( 'theme-editor.php?file=style.css&theme=' . $_POST['rzct_slug'] );

	if( isset( $_POST['rzct_function'] ) && $_POST['rzct_function'] === 'on' ) {
		$functionsheader = <<<END
<?php
// This is where you put your custom functions.
END;

		$functionsfile = trailingslashit( $themedir ) . 'functions.php';

		if ( ! $wp_filesystem->put_contents( $functionsfile, $functionsheader, FS_CHMOD_FILE ) ) {
			add_settings_error( 'rzct', 'create_file', esc_html__('Unable to create functions.php.', 'rzct'), 'error' );
		}
	}

	if ( null !== switch_theme( $_POST['rzct_template'], $_POST['rzct_slug']  ) ) {
		add_settings_error( 'rzct', 'activate_theme', esc_html__('Unable to activate the new theme.', 'rzct'), 'error' );
	}

	if ( isset( $_POST['rzct_parent'] ) && $_POST['rzct_parent'] === 'on' ) {
		$parent_mod = get_option( 'theme_mods_' . $_POST['rzct_template'] );
		update_option( 'theme_mods_' . $_POST['rzct_slug'], $parent_mod );
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
