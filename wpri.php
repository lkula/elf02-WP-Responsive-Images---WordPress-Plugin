<?php

/*
Plugin Name: elf02 WP Responsive Images
Plugin URI: http://elf02.de/elf02-wp-responsive-images-wordpress-plugin/
Description: Responsive image solution using Picturefill.js -- Original idea by timevko.com.
Version: 1.3.1
Author: ChrisB & Martin Wolf
Author URI: http://elf02.de
License: MIT
*/

define('PLUGIN_FILE', __FILE__);

add_action(
    'plugins_loaded',
    array(
        'wpri_base',
        'instance'
    ),
    99
);


spl_autoload_register('wpri_autoload');

function wpri_autoload($class) {
    if(substr($class, 0, 5) === 'wpri_') {
        require_once(
            sprintf(
                '%s/inc/%s.class.php',
                dirname(__FILE__),
                strtolower($class)
            )
        );
    }
}


?>