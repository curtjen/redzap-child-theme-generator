<?php
/*
Plugin Name: RedZap Child Theme Generator
Plugin URI: 
Description: A plugin to create child themes based off an installed theme.
Version: 1.0
Author: voldemortensen, curtjen
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

if ( ! defined( 'WPINC' ) ) { die; }

define( 'RZCT_BASE_DIR', plugin_dir_path( __FILE__ ) );
define( 'RZCT_BASE_URL', plugin_dir_url( __FILE__ ) );

require_once( RZCT_BASE_DIR . 'admin/menu.php' );
require_once( RZCT_BASE_DIR . 'admin/rzct-admin-page.php' );
require_once( RZCT_BASE_DIR . 'admin/includes.php' );
