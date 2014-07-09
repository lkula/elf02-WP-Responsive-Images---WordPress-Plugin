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


define('PLUGIN_FILE_WPRI', __FILE__);

require_once(
    sprintf(
        '%s/inc/%s.class.php',
        dirname(__FILE__),
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