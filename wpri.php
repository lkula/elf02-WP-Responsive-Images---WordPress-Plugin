<?php

/*
Plugin Name: elf02 WP Responsive Images
Plugin URI: http://elf02.de/elf02-wp-responsive-images-wordpress-plugin/
Description: Responsive image solution using Picturefill.js -- Original idea by timevko.com.
Version: 1.3.3
Author: ChrisB & Martin Wolf
Author URI: http://elf02.de
License: MIT
*/

defined('ABSPATH') OR exit;

define('WPRI_FILE', __FILE__);
define('WPRI_PLUGIN_DIR', untrailingslashit(plugin_dir_path(WPRI_FILE)));
define('WPRI_PLUGIN_URL', untrailingslashit(plugins_url(basename(plugin_dir_path(WPRI_FILE)), basename(WPRI_FILE))));


require_once(
    sprintf(
        '%s/inc/%s.class.php',
        WPRI_PLUGIN_DIR,
        'wpri'
    )
);


add_action(
    'plugins_loaded',
    array(
        'wpri',
        'instance'
    )
);

?>