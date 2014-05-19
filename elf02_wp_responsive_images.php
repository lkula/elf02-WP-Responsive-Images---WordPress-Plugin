<?php

/*
Plugin Name: elf02 WP Responsive Images
Plugin URI: http://elf02.de/elf02-wp-responsive-images-wordpress-plugin/
Description: Responsive image solution using picturefilljs. Original idea by timevko.com.
Version: 1.3.1
Author: ChrisB & Martin Wolf
Author URI: http://elf02.de
License: MIT
*/

define('PLUGIN_FILE', __FILE__);

add_action(
    'plugins_loaded',
    array(
        'elf02_wp_responsive_images',
        'instance'
    ),
    99
);


spl_autoload_register('elf02_autoload');

function elf02_autoload($class) {
    if(substr($class, 0, 6) === 'elf02_') {
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