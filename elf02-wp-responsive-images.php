<?php

/*
Plugin Name: elf02 WP Responsive Images
Plugin URI: http://elf02.de/elf02-wp-responsive-images-wordpress-plugin/
Description: Responsive image solution using picturefilljs. Original idea by timevko.com.
Version: 1.2.2
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
            'pixel' => '0'
        ),
        'bp4' => array(
            'name' => '',
            'size' => '0',
            'size2x' => '0',
            'pixel' => '0'
        ),
        'bp5' => array(
            'name' => '',
            'size' => '0',
            'size2x' => '0',
            'pixel' => '0'
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
            if(!empty($value['name']) && !empty($value['size'])) {
                add_image_size($value['name'], intval($value['size']));
                if(!empty($value['size2x'])) {
                    $str = $value['name'].'@2x';
                    add_image_size($str, intval($value['size2x']));
                }
            }
        }

        add_action('wp_enqueue_scripts', array($this, 'add_picturefilljs'));
        add_filter('image_send_to_editor', array($this, 'insert_image_with_id'), 10, 9);
        add_filter('the_content', array($this, 'filter_responsive_images'));
        add_action('admin_init', array($this, 'init_options'));
        add_action('admin_menu', array($this, 'init_options_page'));
    }

    /**
     * The Needle and the Haystack
     */
    private function in_array_r($needle, $haystack) {
        foreach ($haystack as $item) {
            if(($item === $needle) || (is_array($item) && $this->in_array_r($needle, $item))) {
                return true;
            }
        }
        return false;
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

    /**
     * Validate and sanitize options
     */
    public function validate($input) {
        // Check for available small-img breakpoint
        if(!$this->in_array_r('small-img', $input)) {
            add_settings_error(
                'elf02WPResponsiveImages-error',
                'small-img-error',
                'Please set a "small-img" breakpoint.',
                'error'
            );
        }

        // Sanitize input fields
        foreach($input as $key => $value) {
            $input[$key]['name'] = sanitize_text_field($value['name']);

            if($input[$key]['size'] !== '0') {
                $input[$key]['size'] = intval($value['size']);
                if(!$input[$key]['size']) {
                    add_settings_error(
                        'elf02WPResponsiveImages-error',
                        'int-error',
                        'Number for ('. $input[$key]['name'] .': Image Size) please!',
                        'error'
                    );
                }
            }

           if($input[$key]['size2x'] !== '0') {
                $input[$key]['size2x'] = intval($value['size2x']);
                if(!$input[$key]['size2x']) {
                    add_settings_error(
                        'elf02WPResponsiveImages-error',
                        'int-error',
                        'Number for ('. $input[$key]['name'] .': Image Size Retina) please!',
                        'error'
                    );
                }
            }

            if($input[$key]['pixel'] !== '0') {
                $input[$key]['pixel'] = intval($value['pixel']);
                if(!$input[$key]['pixel']) {
                    add_settings_error(
                        'elf02WPResponsiveImages-error',
                        'int-error',
                        'Number for ('. $input[$key]['name'] .': Breakpoint Pixel) please!',
                        'error'
                    );
                }
            }
        }

        return $input;
    }


    /**
     * Output options
     */
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
                        <td colspan="4" style="padding:10px;border:2px dashed #1e8cbe;">
                            <?php
                                // Check for available small-img breakpoint
                                if(!$this->in_array_r('small-img', self::$options)) {
                                    echo '<p style="padding:10px;border: 3px dashed red;"><strong>Error: </strong>Please set a <i>small-img</i> breakpoint.</p>';
                                }

                                // Output all registered image sizes
                                echo '<h3>All currently registered image sizes</h3><ul>';
                                global $_wp_additional_image_sizes;
                                foreach($_wp_additional_image_sizes as $key => $value) {
                                    $list[] = (!empty($key)) ?
                                        sprintf('<li><strong>%s=</strong>%s</li>', $key, $value['width']) :
                                        '';
                                }
                                echo implode($list).'</ul>';
                            ?>
                            <h3>Notes</h3>
                            <ul>
                                <li><strong>Smallest Breakpoint called <i>small-img</i> must be set for the initial image and should have a value of zero.</strong></li>
                                <li><i>Image Size Retina</i> is optional and can be zero.</li>
                                <li>It is not necessary to set all five Breakpoints.</li>
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
        // Check for available small-img breakpoint and empty options
        if(!$this->in_array_r('small-img', self::$options) || empty(self::$options)) return $content;

        $content = preg_replace_callback(
            '#(?:<p><img|<img).*?data-responsive=[\'"](.*?)[\'"].*?(?:>(?!</p>)|</p>)#imsu',
            array($this, 'replace_responsive_images'),
            $content
        );

        return $content;
    }

    /**
     * Replace images with picturefill.js markup
     */
    public function replace_responsive_images($matches) {
        $image_id = intval($matches[1]);
        // Check for invalid image id
        if(!$image_id) return $matches[0];

        $markup = '<picture><!--[if IE 9]><video style="display: none;"><![endif]-->';

        // Output all images with the picturefill markup
        foreach(self::$options as $key => $value) {

            if(!empty($value['name'])) {

                $imgsrc = wp_get_attachment_image_src($image_id, $value['name']);
                if(!empty($value['size2x'])) {
                    $str = $value['name'].'@2x';
                    $imgsrc_retina = wp_get_attachment_image_src($image_id, $str);
                }

                $media = (empty($value['pixel'])) ?
                    '' :
                    ' media="(min-width:'. $value['pixel'] .'px)"';

                $retina = (empty($value['size2x'])) ?
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