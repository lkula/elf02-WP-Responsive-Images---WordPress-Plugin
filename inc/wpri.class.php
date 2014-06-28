<?php

defined('ABSPATH') OR exit;


final class wpri {

    /**
     * Default options
     */
    private static $options_default = array(
        'bp1' => array(
            'name' => 'large-img',
            'size' => '1100',
            'size2x' => '0',
            'bp_pixel' => '769'
        ),
        'bp2' => array(
            'name' => 'medium-img',
            'size' => '768',
            'size2x' => '0',
            'bp_pixel' => '321'
        ),
        'bp3' => array(
            'name' => 'small-img',
            'size' => '320',
            'size2x' => '0',
            'bp_pixel' => '0'
        ),
        'bp4' => array(
            'name' => '',
            'size' => '0',
            'size2x' => '0',
            'bp_pixel' => '0'
        ),
        'bp5' => array(
            'name' => '',
            'size' => '0',
            'size2x' => '0',
            'bp_pixel' => '0'
        ),
        '_fallback' => 0,
        '_native' => 0
    );

    private static $options_name = 'elf02_wp_responsive_images';
    private static $options = array();


    /**
     * Singleton
     */
    private static $instance = null;

    public static function instance() {
        if(NULL === self::$instance) {
            self::$instance = new self;
        }
        return self::$instance;
    }


    /**
     * Init plugin, load options, set image sizes, set filters and actions
     */
    private function __construct() {
        add_theme_support('post-thumbnails');

        self::$options = wp_parse_args(
            get_option(self::$options_name),
            self::$options_default
        );

        foreach(self::$options as $key => $value) {
            if(substr($key, 0, 1) != '_' && !empty($value['name']) && !empty($value['size'])) {
                $size = intval($value['size']);
                add_image_size($value['name'], $size);

                // Very secret and magical pixel calculation
                $size2x = $size * 2;
                $name2x = $value['name'].'@2x';
                self::$options[$key]['size2x'] = $size2x;
                add_image_size($name2x, $size2x);
            }
        }

        if(!self::$options['_native']) {
            add_action(
                'wp_enqueue_scripts',
                array(
                    $this,
                    'add_picturefilljs'
                )
            );
        }

        add_filter(
            'image_send_to_editor',
            array(
                $this,
                'insert_image_with_id'
            ),
            10,
            9
        );

        add_filter(
            'the_content',
            array(
                $this,
                'filter_responsive_images'
            ),
            99
        );

        add_action(
            'admin_init',
            array(
                $this,
                'init_options'
            )
        );

        add_action(
            'admin_menu',
            array(
                $this,
                'init_options_page'
            )
        );
    }


    /**
     * Options Page handling
     */
    public function init_options() {
        register_setting(
            'responsive_images_options',
            self::$options_name,
            array(
                $this,
                'validate'
            )
        );
    }


    public function init_options_page() {
        add_options_page(
            'Responsive Images Options',
            'Responsive Images',
            'manage_options',
            'responsive_images_options',
            array(
                $this,
                'options_do_page'
            )
        );
    }


    /**
     * Validate and sanitize options
     */
    public function validate($input) {
        foreach($input as $key => $value) {
            if(substr($key, 0, 1) != '_') {
                $input[$key]['name'] = sanitize_text_field($value['name']);
                $input[$key]['size'] = intval($value['size']);
                $input[$key]['bp_pixel'] = intval($value['bp_pixel']);
            }
        }

        $input['_fallback'] = intval($input['_fallback']);
        $input['_native'] = intval($input['_native']);

        return $input;
    }


    /**
     * Output options
     */
    public function options_do_page() {
        ?>
        <div class="wrap">
            <h2><?php _e('Responsive Images Options', 'wpri'); ?></h2>
            <form method="post" action="options.php">
                <?php settings_fields('responsive_images_options'); ?>
                <table class="widefat" style="width:500px;">
                <thead>
                    <tr>
                        <th><?php _e('Breakpoint Name', 'wpri'); ?></th>
                        <th><?php _e('Image Size (width)', 'wpri'); ?></th>
                        <th><?php _e('Breakpoint Pixel (min-width)', 'wpri'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        for($i=1; $i<=5; $i++) {
                            echo '<tr>';

                            $str1 = 'bp'.$i;
                            $str2 = self::$options_name . '['. $str1 .'][name]';
                            $str3 = self::$options[$str1]['name'];
                            printf('<td><input type="text" name="%s" value="%s"></td>', $str2, $str3);

                            $str2 = self::$options_name . '['. $str1 .'][size]';
                            $str3 = self::$options[$str1]['size'];
                            printf('<td><input type="text" name="%s" value="%s"></td>', $str2, $str3);

                            $str2 = self::$options_name . '['. $str1 .'][bp_pixel]';
                            $str3 = self::$options[$str1]['bp_pixel'];
                            printf('<td><input type="text" name="%s" value="%s"></td>', $str2, $str3);

                            echo '</tr>';
                        }
                    ?>
                </tbody>
                </table>
                <div style="margin-top: 20px;">
                    <p>
                        <label for="cb_fallback">
                        <?php
                            printf('<input id="cb_fallback" type="checkbox" name="%s" value="1" %s>',
                                self::$options_name.'[_fallback]',
                                checked(self::$options['_fallback'], 1, false)
                            );
                            _e('Use full size image as fallback? (Can produce extra http requests.)', 'wpri');
                        ?>
                        </label>
                    </p>
                    <p>
                        <label for="cb_native">
                        <?php
                            printf('<input id="cb_native" type="checkbox" name="%s" value="1" %s>',
                                self::$options_name.'[_native]',
                                checked(self::$options['_native'], 1, false)
                            );
                            _e('Use native implementation? (Not yet recommended!)', 'wpri');
                        ?>
                        </label>
                    </p>
                </div>
                <p class="submit">
                    <input type="submit" class="button-primary" value="<?php _e('Save Changes', 'wpri'); ?>" />
                </p>
            </form>
            <div>
                <?php
                    // Output all registered image sizes
                    printf('<h3>%s</h3><ul>', __('Registered image sizes', 'wpri'));
                    global $_wp_additional_image_sizes;
                    foreach($_wp_additional_image_sizes as $key => $value) {
                        $list[] = (!empty($key)) ?
                            sprintf('<li><strong>%s=</strong>%s</li>', $key, $value['width']) :
                            '';
                    }
                    echo implode($list).'</ul>';
                ?>
            </div>
        </div>
        <?php
    }


    /**
     * Add picturefill.js
     * @author picturefill.js http://scottjehl.github.io/picturefill/
     */
    public function add_picturefilljs() {
        wp_register_script(
            'picturefill',
            plugins_url(
                '/js/picturefill.min.js',
                PLUGIN_FILE_WPRI
            ),
            array(),
            null
        );
        wp_enqueue_script('picturefill');
    }


    /**
     * Add data-responsive attribute
     */
    public function insert_image_with_id($html, $id, $caption, $title, $align, $url) {
        $html = str_replace(
            '<img',
            sprintf('<img data-responsive="%s"', $id),
            $html
        );
        return $html;
    }


    /**
     * Filter all images with a data-responsive attribute
     */
    public function filter_responsive_images($content) {
        // Check for empty options
        if(empty(self::$options)) return $content;

        $content = preg_replace_callback(
            '#<img.*?data-responsive=[\'"](.*?)[\'"](?:.*?class=[\'"](.*?)[\'"].*?)?.*?>#imsu',
            array(
                $this,
                'replace_responsive_images'
            ),
            $content
        );

        return $content;
    }


    /**
     * Replace images with srcset image markup
     */
    public function replace_responsive_images($matches) {
        $image_id = intval($matches[1]);

        // Check image id
        if(empty($image_id)) return $matches[0];

        // Get class names
        $class_names = (!empty($matches[2])) ?
            $matches[2] :
            '';

        // Collect all images
        foreach(self::$options as $key => $value) {
            if(substr($key, 0, 1) != '_' && !empty($value['name'])) {
                $imgsrc = wp_get_attachment_image_src($image_id, $value['name']);
                $imgsrc_2x = wp_get_attachment_image_src($image_id, $value['name'].'@2x');
                $srcset[] = sprintf('%s %sw, %s %sw, ', $imgsrc[0], $value['size'], $imgsrc_2x[0], $value['size2x']);
                if(!empty($value['bp_pixel'])) {
                    $mq[] = sprintf('(min-width: %spx) %spx, ', $value['bp_pixel'], $value['size']);
                }
            }
        }

        $img_fallback = '';
        if(self::$options['_fallback']) {
            $imgsrc_full = wp_get_attachment_image_src($image_id, 'full');
            $img_fallback = sprintf(' src="%s"', $imgsrc_full[0]);
        }

        // srcset image markup
        $markup = sprintf('<img class="%s"%s srcset="%s" sizes="%s100vw">',
            $class_names,
            $img_fallback,
            (isset($srcset)) ? trim(implode($srcset), ', ') : '',
            (isset($mq)) ? implode($mq) : ''
        );


        return $markup;
    }

}

?>