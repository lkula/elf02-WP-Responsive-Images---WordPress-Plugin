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

    private static $instance = null;

    public static function get_instance() {
        if(NULL === self::$instance) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     * Five default Breakpoints
     */
    private static $options_default = array(
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

    private static $options_name = 'elf02_wp_responsive_images';
    private static $options = array();


    private function __construct() {
        add_action('plugins_loaded', array($this, 'init'));
    }

    /**
     * Init plugin, load options and set image sizes
     */
    public function init() {
        add_theme_support('post-thumbnails');
        $wp_options = get_option(self::$options_name);
        self::$options = (FALSE === $wp_options) ?
            self::$options_default :
            $wp_options;

        foreach(self::$options as $key => $value) {
            add_image_size($value['name'], intval($value['size']));
            if($value['size2x'] !== '') {
                $str = $value['name'].'@2x';
                add_image_size($str, intval($value['size2x']));
            }
        }

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
        register_setting('responsive_images_options', self::$options_name, array($this, 'validate'));
    }

    public function init_options_page() {
        add_options_page('Responsive Images Options', 'Responsive Images', 'manage_options', 'responsive_images_options', array($this, 'options_do_page'));
    }

    private function in_array_r($needle, $haystack) {
        foreach ($haystack as $item) {
            if(($item === $needle) || (is_array($item) && $this->in_array_r($needle, $item))) {
                return true;
            }
        }
        return false;
    }

    public function validate($input) {
        if(!$this->in_array_r('small-img', $input)) {
            add_settings_error(
                'elf02WPResponsiveImages-error',
                'small-img-error',
                'Please set a "small-img" breakpoint.',
                'error'
            );
        }
        return $input;
    }

    public function options_do_page() {
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
                            $str2 = self::$options_name . '['. $str1 .'][name]';
                            $str3 = self::$options[$str1]['name'];
                            printf('<td><input type="text" name="%s" value="%s" /></td>', $str2, $str3);

                            $str2 = self::$options_name . '['. $str1 .'][size]';
                            $str3 = self::$options[$str1]['size'];
                            printf('<td><input type="text" name="%s" value="%s" /></td>', $str2, $str3);

                            $str2 = self::$options_name . '['. $str1 .'][size2x]';
                            $str3 = self::$options[$str1]['size2x'];
                            printf('<td><input type="text" name="%s" value="%s" /></td>', $str2, $str3);

                            $str2 = self::$options_name . '['. $str1 .'][pixel]';
                            $str3 = self::$options[$str1]['pixel'];
                            printf('<td><input type="text" name="%s" value="%s" /></td>', $str2, $str3);

                            echo '</tr>';
                        }
                    ?>
                    <tr>
                        <td colspan="4">
                            <?php
                                // Check for "small-img" breakpoint
                                if(!$this->in_array_r('small-img', self::$options)) {
                                    echo '<p style="padding:10px;border: 2px dashed red;"><strong>Error: </strong>Please set a "small-img" breakpoint.</p>';
                                }
                            ?>
                            <ul style="padding:10px;border:2px dashed #1e8cbe;">
                                <li><strong>Notes:</strong></li>
                                <li><strong>Smallest Breakpoint must be set. This is also the initial Image and must be called "small-img".</strong></li>
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

        // Check for "small-img" breakpoint and empty options
        if((!$this->in_array_r('small-img', self::$options)) || (empty(self::$options))) return $content;

        $regex = (FALSE === strpos($content, '<p><img')) ?
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

        foreach(self::$options as $key => $value) {

            if($value['name'] !== '') {

                $imgsrc = wp_get_attachment_image_src($image_id, $value['name']);
                if($value['size2x'] !== '') {
                    $str = $value['name'].'@2x';
                    $imgsrc_retina = wp_get_attachment_image_src($image_id, $str);
                }

                $media = ($value['pixel'] === '') ?
                    '' :
                    ' media="(min-width:'. $value['pixel'] .'px)"';

                $retina = ($value['size2x'] === '') ?
                    '' :
                    ', '. $imgsrc_retina[0] .' 2x';

                $arr[] = sprintf('<source srcset="%s"%s>', $imgsrc[0].$retina, $media);
            }
        }

        $markup .= implode($arr);

        // Initial image
        $imgsrc = wp_get_attachment_image_src($image_id, 'small-img');
        $markup .= '<!--[if IE 9]></video><![endif]--><img class="responsive-img" srcset="'. $imgsrc[0] .'"></picture>';

        return $markup;
    }

}

?>