<?php

/*
Plugin Name: elf02 WP Responsive Images
Plugin URI: http://elf02.de/elf02-wp-responsive-images-wordpress-plugin/
Description: Responsive image solution using picturefilljs. Original idea by timevko.com.
Version: 1.2.1
Author: ChrisB & Martin Wolf
Author URI: http://elf02.de
License: MIT
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

        /**
         * Five default Breakpoints
         */
        protected $option_name = 'elf02_wp_responsive_images';
        protected $option_default = array(
            'bp1' => array(
                'name' => 'large-img',
                'size' => '1100',
                'size2x' => '1600',
                'pixel' => '769'
            ),
            'bp2' => array(
                'name' => 'medium-img',
                'size' => '768',
                'size2x' => '1536',
                'pixel' => '321'
            ),
            'bp3' => array(
                'name' => 'small-img',
                'size' => '320',
                'size2x' => '640',
                'pixel' => ''
            ),
            'bp4' => array(
                'name' => '',
                'size' => '',
                'size2x' => '',
                'pixel' => ''
            ),
            'bp5' => array(
                'name' => '',
                'size' => '',
                'size2x' => '',
                'pixel' => ''
            )
        );

        private function __construct() {
            add_action('plugins_loaded', array($this, 'init'));
        }

        public function init() {
            // add_image_size from plugin options
            add_theme_support('post-thumbnails');
            $options = get_option($this->option_name);
            $options = ($options === FALSE) ? $this->option_default : $options;
            foreach ($options as $key => $value) {
                add_image_size($value['name'], intval($value['size']));
                if($value['size2x'] !== '') {
                    $str = $value['name'].'@2x';
                    add_image_size($str, intval($value['size2x']));
                }
            }

            // Set wp actions and filters
            add_action('wp_enqueue_scripts', array($this, 'add_picturefilljs'));
            add_filter('image_send_to_editor', array($this, 'insert_image_with_id'), 10, 9);
            add_filter('the_content', array($this, 'filter_responsive_images'));
            add_action('admin_init', array($this, 'init_options'));
            add_action('admin_menu', array($this, 'init_options_page'));
        }


        /**
         * Options Page handling
         */
        public function init_options() {
            register_setting('responsive_images_options', $this->option_name);
        }

        public function init_options_page() {
            add_options_page('Responsive Images Options', 'Responsive Images', 'manage_options', 'responsive_images_options', array($this, 'options_do_page'));
        }

        public function options_do_page() {
            $options = get_option($this->option_name);
            $options = ($options === FALSE) ? $this->option_default : $options;
            ?>
            <div class="wrap">
                <h2>Responsive Images Options</h2>
                <form method="post" action="options.php">
                    <?php settings_fields('responsive_images_options'); ?>
                    <table class="form-table" style="width:500px;">
                        <tr>
                            <th>Breakpoint Name</th>
                            <th>Image Size</th>
                            <th>Image Size Retina</th>
                            <th>Breakpoint Pixel (min-width)</th>
                        </tr>
                        <?php
                            for($i=1; $i<=5; $i++) {
                                echo '<tr>';

                                $str1 = 'bp'.$i;
                                $str2 = $this->option_name . '['. $str1 .'][name]';
                                $str3 = $options[$str1]['name'];
                                printf('<td><input type="text" name="%s" value="%s" /></td>', $str2, $str3);

                                $str2 = $this->option_name . '['. $str1 .'][size]';
                                $str3 = $options[$str1]['size'];
                                printf('<td><input type="text" name="%s" value="%s" /></td>', $str2, $str3);

                                $str2 = $this->option_name . '['. $str1 .'][size2x]';
                                $str3 = $options[$str1]['size2x'];
                                printf('<td><input type="text" name="%s" value="%s" /></td>', $str2, $str3);

                                $str2 = $this->option_name . '['. $str1 .'][pixel]';
                                $str3 = $options[$str1]['pixel'];
                                printf('<td><input type="text" name="%s" value="%s" /></td>', $str2, $str3);

                                echo '</tr>';
                            }
                        ?>
                        <tr>
                            <td colspan="4">
                                <ul style="padding:10px;border:2px dashed #1e8cbe;">
                                    <li><strong>Notes:</strong></li>
                                    <li><strong>First Breakpoint must be set.</strong> This is also the Fallback Image and should be the largest one.</li>
                                    <li>"Image Size Retina" is optional and can be blank.</li>
                                    <li>It is not necessary to set all five Breakpoints.</li>
                                    <li><strong>Set all values without an additional "px".</strong></li>
                                    <li><strong>The smallest Breakpoint shouldn't have set a "Breakpoint Pixel" value.</strong></li>
                                </ul>
                            </td>
                        </tr>
                    </table>
                    <p class="submit">
                        <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
                    </p>
                </form>
            </div>
            <?php
        }

        /**
         * Add picturefill.js
         * @author picturefill.js http://scottjehl.github.io/picturefill/
         */
        public function add_picturefilljs() {
            wp_register_script('picturefill', plugins_url('/js/picturefill.min.js', __FILE__), array(), null);
            wp_enqueue_script('picturefill');
        }

        /**
         * data-responsive attribute with id added to all images
         */
        public function insert_image_with_id($html, $id, $caption, $title, $align, $url) {
            $html = str_replace('<img', '<img data-responsive="'. $id .'"', $html);
            return $html;
        }

        /**
         * Filter all images with a data-responsive attribute
         */
        public function filter_responsive_images($content) {
            $regex = ( strpos($content, '<p><img') === false ) ?
                '#<img.*?data-responsive=[\'"](.*?)[\'"].*?>#' :
                '#<p><img.*?data-responsive=[\'"](.*?)[\'"].*?></p>#';

            $content = preg_replace_callback(
                $regex,
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
            $markup = '<picture><!--[if IE 9]><video style="display: none;"><![endif]-->';

            // Images with media querys and breakpoints
            $options = get_option($this->option_name);
            $options = ($options === FALSE) ? $this->option_default : $options;
            foreach ($options as $key => $value) {

                if($value['name'] !== '') {

                    $imgsrc = wp_get_attachment_image_src($image_id, $value['name']);
                    if($value['size2x'] !== '') {
                        $str = $value['name'].'@2x';
                        $imgsrc_retina = wp_get_attachment_image_src($image_id, $str);
                    }

                    $media = ($value['pixel'] === '') ? '' : ' media="(min-width:'. $value['pixel'] .'px)"';
                    $retina = ($value['size2x'] === '') ? '' : ', '. $imgsrc_retina[0] .' 2x';
                    $arr[] ='<source srcset="'. $imgsrc[0].$retina .'"'. $media .'>';

                }
            }

            $markup .= implode($arr);

            // Fallback Image
            $imgsrc = wp_get_attachment_image_src($image_id, $options['bp1']['name']);
            $markup .= '<!--[if IE 9]></video><![endif]--><img class="responsive-img" src="'. $imgsrc[0] .'"></picture>';

            return $markup;
        }

    }

?>