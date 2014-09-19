<?php

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