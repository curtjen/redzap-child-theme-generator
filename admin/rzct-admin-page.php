<?php

function rzct_options_page() {
	include_once( RZCT_BASE_DIR . '/admin/rzct-process.php' );
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
			<tr valign='top'><th scope='row'>{$opts['author_uri']}</th><td><input class='regular-text' type='text' name='rzct_author_uri' value=''></td></tr>
			<tr valign='top'><th scope='row'>Create a functions.php?</th><td><input class='regular-text' type='checkbox' name='rzct_function'></td</tr>
			<tr valign='top'><th scope='row'>Include changes to parent theme?</th><td><input class='regular-text' type='checkbox' name='rzct_parent'></td></tr>";

		?>
		</table>
		<?php submit_button( esc_html__('Create a child theme and activate it!', 'rzct') ); ?>
		</form>
	</div>
<?php
}
