<?php
/**
 * Plugin Name: Fix Update in Process
 * Description: One click fix "Another update is currently in progress." which occurs while updating the WordPress core or auto updating the thems & plugins.
 * Plugin URI: https://profiles.wordpress.org/mahesh901122/
 * Author: Mahesh M. Waghmare
 * Author URI: https://maheshwaghmare.com/
 * Version: 1.0.1
 * License: GNU General Public License v2.0
 * Text Domain: fix-update-in-process
 *
 * @package Fix Update in Process
 */

// Set constants.
define( 'FIX_UPDATE_IN_PROCESS_VER', '1.0.1' );
define( 'FIX_UPDATE_IN_PROCESS_FILE', __FILE__ );
define( 'FIX_UPDATE_IN_PROCESS_BASE', plugin_basename( FIX_UPDATE_IN_PROCESS_FILE ) );
define( 'FIX_UPDATE_IN_PROCESS_DIR', plugin_dir_path( FIX_UPDATE_IN_PROCESS_FILE ) );
define( 'FIX_UPDATE_IN_PROCESS_URI', plugins_url( '/', FIX_UPDATE_IN_PROCESS_FILE ) );

require_once FIX_UPDATE_IN_PROCESS_DIR . 'classes/class-fix-update-in-process.php';
