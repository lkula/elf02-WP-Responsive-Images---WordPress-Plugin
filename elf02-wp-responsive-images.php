<?php

/*
Plugin Name: elf02 WP Responsive Images
Plugin URI: http://elf02.de/elf02-wp-respo…rdpress-plugin/
Description: Responsive image solution using picturefilljs. Base idea from http://timevko.com.
Version: 1.0.0
Author: ChrisB
Author URI: http://elf02.de
License: MIT
*/


    /**
     * Set your own image sizes and breakpoints.
     */

    add_theme_support('post-thumbnails');
    add_image_size('large-img', 1200);
    add_image_size('medium-img', 700);
    add_image_size('small-img', 300);

    elf02_wp_responsive_images::$breakpoints = array(
        '0' => 'small-img',
        '600' => 'medium-img',
        '1000' => 'large-img'
    );





    /**
     * Plugin code. Don't touch.
     */

    elf02_wp_responsive_images::get_instance();

    class elf02_wp_responsive_images {

        static private $instance = null;

        static public function get_instance() {
            if(self::$instance === null) {
                self::$instance = new self;
            }
            return self::$instance;
        }

        static public $breakpoints = array(
            '0' => 'small-img',
            '600' => 'medium-img',
            '1000' => 'large-img'
        );

        private function __construct() {
            add_action('plugins_loaded', array($this, 'init'));
        }

        public function init() {
            add_action('wp_enqueue_scripts', array($this, 'add_picturefilljs'));
            add_filter('image_send_to_editor', array($this, 'insert_image_with_id'), 10, 9);
            add_filter('the_content', array($this, 'filter_responsive_images'));
        }

        /**
         * Add picturefill.js
         * @author picturefill.js http://scottjehl.github.io/picturefill/
         */
        public function add_picturefilljs() {
            wp_register_script('picturefill', plugins_url('/js/picturefill.min.js', __FILE__), array(), null, true);
            wp_enqueue_script('picturefill');
        }

        /**
         * data-responsive attribute with id added to all images
         */
        public function insert_image_with_id($html, $id, $caption, $title, $align, $url) {
            $html = str_replace('<img', '<img data-responsive="' . $id . '"', $html);
            return $html;
        }

        /**
         * Filter all images with a data-responsive tag
         */
        public function filter_responsive_images($content) {
            $content = preg_replace_callback(
                '#<img.*?data-responsive=[\'"](.*?)[\'"].*?>#',
                array($this, 'replace_responsive_images'),
                $content
            );

            return $content;
        }

        /**
         * Replace images with picturefill.js markup
         */
        public function replace_responsive_images($matches) {
            $image_id = $matches[1];
            $markup = '<span data-picture>';

            // First image with no media query
            $imgsrc = wp_get_attachment_image_src($image_id, 'large-img');
            $arr[] ='<span data-src="'. $imgsrc[0] . '"></span>';

            // Images with media querys and breakpoints
            foreach(self::$breakpoints as $size => $type)
            {
                $imgsrc = wp_get_attachment_image_src($image_id, $type);
                $arr[] ='<span data-src="'. $imgsrc[0] . '" data-media="(min-width:'. $size .'px)"></span>';
            }
            $markup .= implode($arr);

            // Noscript fallback image
            $markup .= '<noscript>' . wp_get_attachment_image($image_id, 'large-img') . '</noscript></span>';

            return $markup;
        }

    }

?>