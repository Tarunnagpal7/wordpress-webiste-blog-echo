<?php

/**
 * Class YasrSettingsStyle
 *
 * @author Dario Curvino <@dudo>
 * @since 3.1.9
 */
class YasrSettingsStyle {
    public function init() {
        //init style options
        add_action('admin_init', array($this, 'styleOptions'));

        //Add setting field to choose the image for the free version
        add_action('yasr_style_options_add_settings_field', array('YasrSettingsStyle', 'settingsFieldFreeChooseImage'));

        //hook into options
        add_filter('yasr_filter_style_options', array($this, 'defaultStarSet'));
    }

    /**
     * Init style options
     *
     * @author Dario Curvino <@dudo>
     * @return void
     */
    public function styleOptions() {
        register_setting(
            'yasr_style_options_group', // A settings group name. Must exist prior to the register_setting call. This must match the group name in settings_fields()
            'yasr_style_options', //The name of an option to sanitize and save.
            array($this, 'styleOptionsSanitize')
        );

        $style_options = json_decode(YASR_STYLE_OPTIONS, true);

        //filter $style_options
        $style_options = apply_filters('yasr_filter_style_options', $style_options);

        add_settings_section(
            'yasr_style_options_section_id',
            __('Style Options', 'yet-another-stars-rating'),
            '__return_false',
            'yasr_style_tab'
        );

        do_action('yasr_style_options_add_settings_field', $style_options);

        add_settings_field(
            'yasr_color_scheme_multiset',
            __('Which color scheme do you want to use?', 'yet-another-stars-rating'),
            array($this, 'settingsFieldFreeMultisetHTML'),
            'yasr_style_tab',
            'yasr_style_options_section_id',
            $style_options
        );

        add_settings_field(
            'yasr_style_options_textarea',
            __('Custom CSS Styles', 'yet-another-stars-rating'),
            array($this,'settingsFieldTextareaHTML'),
            'yasr_style_tab',
            'yasr_style_options_section_id',
            $style_options
        );
    }


    /**
     * Print the radios to choose the color for multiset
     *
     * @author Dario Curvino <@dudo>
     *
     * @param $style_options
     *
     * @return void
     */
    public function settingsFieldFreeMultisetHTML($style_options) {
        ?>

        <div class="yasr-settings-row-35">
            <?php
                $array_options = array (
                    'light' => __('Light', 'yet-another-stars-rating'),
                    'dark'  => __('Dark', 'yet-another-stars-rating')
                );
                $default = $style_options['scheme_color_multiset'];
                $name    = 'yasr_style_options[scheme_color_multiset]';
                $class   = 'yasr-general-options-scheme-color';
                $id      = 'yasr-style-options-color-scheme';

                echo yasr_kses(YasrPhpFieldsHelper::radio('', $class, $array_options, $name, $default, $id));
            ?>

            <div id="yasr-color-scheme-preview">
                <?php esc_html_e("Light theme", 'yet-another-stars-rating'); ?>
                <br /><br /><img src="<?php echo esc_url(YASR_IMG_DIR . 'yasr-multi-set.png')?>" alt="light-multiset">

                <br /> <br />

                <?php esc_html_e("Dark theme", 'yet-another-stars-rating'); ?>
                <br /><br /><img src="<?php echo esc_url(YASR_IMG_DIR . 'dark-multi-set.png')?>" alt="dark-multiset">
            </div>

        </div>

        <p>

        <?php
    }

    /**
     * Print the textarea to customize css
     *
     * @author Dario Curvino <@dudo>*
     * @param $style_options
     *
     * @return void
     */
    public function settingsFieldTextareaHTML($style_options) {
        esc_html_e('Please use text area below to write your own CSS styles to override the default ones.',
            'yet-another-stars-rating');
        echo '<br /><strong>';
        esc_html_e('Leave it blank if you don\'t know what you\'re doing.', 'yet-another-stars-rating');
        echo '</strong><p>';
        ?>

        <label for='yasr_style_options_textarea'></label><textarea
        rows='17'
        cols='40'
        name='yasr_style_options[textarea]'
        id='yasr_style_options_textarea'><?php echo esc_textarea($style_options['textarea']); ?></textarea>

        <?php
    }

    /**
     * Add setting field for free version
     *
     * @author Dario Curvino <@dudo>
     *
     * @param $style_options
     *
     * @return void
     */
    public static function settingsFieldFreeChooseImage($style_options) {
        add_settings_field(
            'yasr_style_options_choose_stars_lite',
            __('Choose Stars Set', 'yet-another-stars-rating'),
            array('YasrSettingsStyle', 'settingsFieldFreeChooseImageHTML'),
            'yasr_style_tab',
            'yasr_style_options_section_id',
            $style_options
        );
    }

    /**
     * Print the html with the radios to choose the image to use
     *
     * @author Dario Curvino <@dudo>
     *
     * @param $style_options
     *
     * @return void
     */
    public static function settingsFieldFreeChooseImageHTML($style_options) {
        ?>
        <div class='yasr-select-img-container' id='yasr_pro_custom_set_choosen_stars'>
            <div>
                <input type='radio'
                       name='yasr_style_options[stars_set_free]'
                       value='rater'
                       id="radio-img-rater"
                       class='yasr-general-options-scheme-color'
                    <?php if ($style_options['stars_set_free'] === 'rater') {
                        echo 'checked="checked"';
                    } ?> />
                <label for="radio-img-rater">
                <span class='yasr_pro_stars_set'>
                    <?php
                    echo '<img src="' . esc_url(YASR_IMG_DIR . 'stars_rater.png').'">';
                    ?>
                </span>
                </label>
            </div>
            <div>
                <input type='radio' name='yasr_style_options[stars_set_free]' value='rater-yasr' id="radio-img-yasr"
                       class='yasr-general-options-scheme-color' <?php if ($style_options['stars_set_free'] === 'rater-yasr') {
                    echo 'checked="checked"';
                } ?> />
                <label for="radio-img-yasr">
                <span class='yasr_pro_stars_set'>
                    <?php
                    echo '<img src="' . esc_url(YASR_IMG_DIR . 'stars_rater_yasr.png').'">';
                    ?>
                </span>
                </label>
            </div>
            <div>
                <input type='radio' name='yasr_style_options[stars_set_free]' value='rater-oxy' id="radio-img-oxy"
                       class='yasr-general-options-scheme-color' <?php if ($style_options['stars_set_free'] === 'rater-oxy') {
                    echo 'checked="checked"';
                } ?> />
                <label for="radio-img-oxy">
                <span class='yasr_pro_stars_set'>
                    <?php
                    echo '<img src="' . esc_url(YASR_IMG_DIR . 'stars_rater_oxy.png').'">';
                    ?>
                </span>
                </label>
            </div>
        </div>

        <hr />

        <div id="yasr-settings-stylish-stars" style="margin-top: 30px">
            <div id="yasr-settings-stylish-image-container">
                <?php
                echo '<img id="yasr-settings-stylish-image" src=' . esc_url(YASR_IMG_DIR . 'yasr-pro-stars.png').'>';
                ?>
            </div>
        </div>

        <div id='yasr-settings-stylish-text'>
            <?php
            $text = __('Looking for more?', 'yet-another-stars-rating');
            $text .= '<br />';
            $text .= sprintf(__('Upgrade to %s', 'yet-another-stars-rating'), '<a href="?page=yasr_settings_page-pricing">Yasr Pro!</a>');

            echo wp_kses_post($text);
            ?>
        </div>

        <?php
        submit_button(__('Save Settings', 'yet-another-stars-rating'));
    }

    /**
     * @author Dario Curvino <@dudo>
     *
     * Filter the $style_options and, if a default value doesn't exist,
     * set 'rater-yasr' as default
     * 
     * @param $style_options
     *
     * @return mixed
     */
    public function defaultStarSet($style_options) {
        if (!array_key_exists('stars_set_free', $style_options)) {
            $style_options['stars_set_free'] = 'rater-yasr'; //..default value if not exists
        }
        return $style_options;
    }

    /**
     * Sanitize the input
     *
     * @author Dario Curvino <@dudo>
     *
     * @param $style_options
     *
     * @return array
     */
    function styleOptionsSanitize($style_options) {
        $style_options = apply_filters('yasr_sanitize_style_options', $style_options);
        $output = array();

        foreach ($style_options as $key => $value) {
            $output[$key] = sanitize_textarea_field($style_options[$key]);
        }

        return $output;
    }

}