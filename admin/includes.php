<?php

function add_rzct_scripts() {
	wp_enqueue_style( 'rzct-style', RZCT_BASE_URL . 'admin/css/rz.css' );
}
add_action( 'admin_init', 'add_rzct_scripts' );
