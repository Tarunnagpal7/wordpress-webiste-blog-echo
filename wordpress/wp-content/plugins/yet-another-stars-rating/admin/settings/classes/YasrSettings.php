<?php

/*

Copyright 2014 Dario Curvino (email : d.curvino@gmail.com)

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>
*/
if ( !defined( 'ABSPATH' ) ) {
    exit( 'You\'re not allowed to see this page' );
}
// Exit if accessed directly
/**
 * @since 2.4.7
 *
 * Setting screen
 *
 * Class YasrSettings
 */
class YasrSettings
{
    /**
     * Init Settings
     */
    public function init()
    {
        add_action( 'admin_init', array( $this, 'generalOptions' ) );
        //This is for general options
        $yasr_style_settings = new YasrSettingsStyle();
        $yasr_style_settings->init();
        $yasr_multiset = new YasrSettingsMultiset();
        $yasr_multiset->init();
        //add ajax endpoint to preview the rankings
        add_action( 'wp_ajax_yasr_rankings_preview_shortcode', array( 'YasrSettingsRankings', 'rankingPreview' ) );
        $yasr_import_plugin = new YasrImportRatingPlugins();
        //add ajax actions
        $yasr_import_plugin->addAjaxActions();
        /** Change default admin footer on yasr settings pages
         *  $text is the default WordPress text
         *  Since 0.8.9
         */
        add_filter( 'admin_footer_text', array( $this, 'customFooter' ) );
    }
    
    /**
     * Load general options
     */
    public function generalOptions()
    {
        register_setting(
            'yasr_general_options_group',
            // A settings group name. Must exist prior to the register_setting call.
            // This must match the group name in settings_fields()
            'yasr_general_options',
            //The name of an options to sanitize and save.
            array( $this, 'sanitize' )
        );
        //Do not use defines here, use $options instead!
        //Otherwise, default values for a disabled setting will not show
        $settings = new YasrSettingsValues();
        $options = $settings->getGeneralSettings();
        add_settings_section(
            'yasr_general_options_section_id',
            __( 'General settings', 'yet-another-stars-rating' ),
            array( $this, 'sectionCallback' ),
            'yasr_general_settings_tab'
        );
        add_settings_field(
            'yasr_use_auto_insert_id',
            $this->descriptionAutoInsert(),
            array( $this, 'autoInsert' ),
            'yasr_general_settings_tab',
            'yasr_general_options_section_id',
            $options
        );
        add_settings_field(
            'yasr_stars_title',
            $this->descriptionStarsTitle(),
            array( $this, 'starsTitle' ),
            'yasr_general_settings_tab',
            'yasr_general_options_section_id',
            $options
        );
        add_settings_field(
            'yasr_show_overall_in_loop',
            $this->descriptionArchivePage(),
            array( $this, 'archivePages' ),
            'yasr_general_settings_tab',
            'yasr_general_options_section_id',
            $options
        );
        add_settings_field(
            'yasr_allow_only_logged_in_id',
            $this->descriptionAllowVote(),
            array( $this, 'loggedOnly' ),
            'yasr_general_settings_tab',
            'yasr_general_options_section_id',
            $options
        );
        add_settings_field(
            'yasr_visitors_stats',
            $this->descriptionVVStats(),
            array( $this, 'vvStats' ),
            'yasr_general_settings_tab',
            'yasr_general_options_section_id',
            $options
        );
        add_settings_field(
            'yasr_choose_snippet_id',
            $this->descriptionStructuredData(),
            array( $this, 'snippets' ),
            'yasr_general_settings_tab',
            'yasr_general_options_section_id',
            $options
        );
        add_settings_field(
            'yasr_custom_text',
            wp_kses_post( $this->descriptionCSTMTxt() ),
            array( $this, 'customText' ),
            'yasr_general_settings_tab',
            'yasr_general_options_section_id',
            $options
        );
        add_settings_field(
            'yasr_advanced',
            __( 'Advanced Settings', 'yet-another-stars-rating' ),
            array( $this, 'advancedSettings' ),
            'yasr_general_settings_tab',
            'yasr_general_options_section_id',
            $options
        );
    }
    
    /**
     * @return void
     */
    public function sectionCallback()
    {
    }
    
    /**
     * Display options for Auto insert
     *
     * @param $option
     */
    public function autoInsert( $option )
    {
        $class = 'yasr-auto-insert-options-class';
        ?>
        <div>
            <strong>
                <?php 
        esc_html_e( 'Use Auto Insert?', 'yet-another-stars-rating' );
        ?>
            </strong>
            <div class="yasr-onoffswitch-big">
                <input type="checkbox" name="yasr_general_options[auto_insert_enabled]" class="yasr-onoffswitch-checkbox"
                       value="1" id="yasr_auto_insert_switch" <?php 
        if ( $option['auto_insert_enabled'] === 1 ) {
            echo  " checked='checked' " ;
        }
        ?> >
                <label class="yasr-onoffswitch-label" for="yasr_auto_insert_switch">
                    <span class="yasr-onoffswitch-inner"></span>
                    <span class="yasr-onoffswitch-switch"></span>
                </label>
            </div>

            <div class="yasr-settings-row-33">
                <div>
                    <?php 
        $option_title = __( 'What?', 'yet-another-stars-rating' );
        $array_options = array(
            'visitor_rating' => __( 'Visitor Votes', 'yet-another-stars-rating' ),
            'overall_rating' => __( 'Overall Rating', 'yet-another-stars-rating' ),
            'both'           => __( 'Both', 'yet-another-stars-rating' ),
        );
        $default = $option['auto_insert_what'];
        $name = 'yasr_general_options[auto_insert_what]';
        echo  yasr_kses( YasrPhpFieldsHelper::radio(
            $option_title,
            $class,
            $array_options,
            $name,
            $default
        ) ) ;
        ?>
                </div>
                <div>
                    <?php 
        $option_title = __( 'Where?', 'yet-another-stars-rating' );
        $array_options = array(
            'top'    => __( 'Before the content', 'yet-another-stars-rating' ),
            'bottom' => __( 'After the content', 'yet-another-stars-rating' ),
            'both'   => __( 'Both', 'yet-another-stars-rating' ),
        );
        $default = $option['auto_insert_where'];
        $name = 'yasr_general_options[auto_insert_where]';
        echo  yasr_kses( YasrPhpFieldsHelper::radio(
            $option_title,
            $class,
            $array_options,
            $name,
            $default
        ) ) ;
        ?>
                </div>
                <div>
                    <?php 
        $option_title = __( 'Align', 'yet-another-stars-rating' );
        $array_options = array(
            'left'   => __( 'Left', 'yet-another-stars-rating' ),
            'center' => __( 'Center', 'yet-another-stars-rating' ),
            'right'  => __( 'Right', 'yet-another-stars-rating' ),
        );
        $default = $option['auto_insert_align'];
        $name = 'yasr_general_options[auto_insert_align]';
        echo  yasr_kses( YasrPhpFieldsHelper::radio(
            $option_title,
            $class,
            $array_options,
            $name,
            $default
        ) ) ;
        ?>
                </div>
                <div>
                    <strong>
                        <?php 
        esc_html_e( 'Size', 'yet-another-stars-rating' );
        ?>
                    </strong>
                    <?php 
        $name = 'yasr_general_options[auto_insert_size]';
        $id = 'yasr-auto-insert-options-stars-size-';
        echo  yasr_kses( self::radioSelectSize(
            $name,
            $class,
            $option['auto_insert_size'],
            $id,
            false
        ) ) ;
        ?>
                </div>
                <div>
                    <?php 
        $option_title = __( 'Exclude Pages?', 'yet-another-stars-rating' );
        $array_options = array(
            'yes' => __( 'Yes', 'yet-another-stars-rating' ),
            'no'  => __( 'No', 'yet-another-stars-rating' ),
        );
        $default = $option['auto_insert_exclude_pages'];
        $name = 'yasr_general_options[auto_insert_exclude_pages]';
        echo  yasr_kses( YasrPhpFieldsHelper::radio(
            $option_title,
            $class,
            $array_options,
            $name,
            $default
        ) ) ;
        ?>
                </div>
                <?php 
        $custom_post_types = YasrCustomPostTypes::getCustomPostTypes();
        
        if ( $custom_post_types ) {
            echo  '<div>' ;
            $option_title = __( 'Use only in custom post types?', 'yet-another-stars-rating' );
            $array_options = array(
                'yes' => __( 'Yes', 'yet-another-stars-rating' ),
                'no'  => __( 'No', 'yet-another-stars-rating' ),
            );
            $default = $option['auto_insert_custom_post_only'];
            $name = 'yasr_general_options[auto_insert_custom_post_only]';
            echo  yasr_kses( YasrPhpFieldsHelper::radio(
                $option_title,
                $class,
                $array_options,
                $name,
                $default
            ) ) ;
            ?>
                            <div class="yasr-element-row-container-description">
                                <?php 
            esc_html_e( 'Select yes if you want to use auto insert only in custom post types', 'yet-another-stars-rating' );
            ?>
                            </div>
                            <?php 
            echo  '</div>' ;
        } else {
            ?>
                            <input type="hidden" name="yasr_general_options[auto_insert_custom_post_only]" value="no">
                        <?php 
        }
        
        ?>
            </div>
            <?php 
        submit_button( YASR_SAVE_All_SETTINGS_TEXT );
        ?>
        </div>
        <hr />
        <?php 
    }
    
    //End yasr_auto_insert_callback
    /**
     * Display options for stars near title
     *
     * @author Dario Curvino <@dudo>
     * @param $option
     */
    public function starsTitle( $option )
    {
        $class = 'yasr-stars-title-options-class';
        ?>
        <div>
            <div class="yasr-onoffswitch-big">
                <input type="checkbox" name="yasr_general_options[stars_title]" class="yasr-onoffswitch-checkbox"
                       id="yasr-general-options-stars-title-switch" <?php 
        if ( $option['stars_title'] === 'yes' ) {
            echo  " checked='checked' " ;
        }
        ?> >
                <label class="yasr-onoffswitch-label" for="yasr-general-options-stars-title-switch">
                    <span class="yasr-onoffswitch-inner"></span>
                    <span class="yasr-onoffswitch-switch"></span>
                </label>
            </div>
            <div class="yasr-settings-row-33">
                <div>
                    <?php 
        $option_title = __( 'What?', 'yet-another-stars-rating' );
        $array_options = array(
            'visitor_rating' => __( 'Visitor Votes', 'yet-another-stars-rating' ),
            'overall_rating' => __( 'Overall Rating', 'yet-another-stars-rating' ),
        );
        $default = $option['stars_title_what'];
        $name = 'yasr_general_options[stars_title_what]';
        echo  yasr_kses( YasrPhpFieldsHelper::radio(
            $option_title,
            $class,
            $array_options,
            $name,
            $default
        ) ) ;
        ?>
                </div>
                <div>
                    <?php 
        $option_title = __( 'Exclude Pages?', 'yet-another-stars-rating' );
        $array_options = array(
            'yes' => __( 'Yes', 'yet-another-stars-rating' ),
            'no'  => __( 'No', 'yet-another-stars-rating' ),
        );
        $default = $option['stars_title_exclude_pages'];
        $name = 'yasr_general_options[stars_title_exclude_pages]';
        echo  yasr_kses( YasrPhpFieldsHelper::radio(
            $option_title,
            $class,
            $array_options,
            $name,
            $default
        ) ) ;
        ?>
                </div>
                <div>
                    <?php 
        $option_title = __( 'Where do you want show ratings?', 'yet-another-stars-rating' );
        $array_options = array(
            'archive' => __( 'Only on archive pages (categories, tags, etc.)', 'yet-another-stars-rating' ),
            'single'  => __( 'Only on single posts or pages', 'yet-another-stars-rating' ),
            'both'    => __( 'Both', 'yet-another-stars-rating' ),
        );
        $default = $option['stars_title_where'];
        $name = 'yasr_general_options[stars_title_where]';
        echo  yasr_kses( YasrPhpFieldsHelper::radio(
            $option_title,
            $class,
            $array_options,
            $name,
            $default
        ) ) ;
        ?>
                </div>
            </div>
        </div>

        <p>&nbsp;</p>
        <hr />

        <?php 
    }
    
    /**
     * Display options for archive pages
     *
     * @author Dario Curvino <@dudo>
     * @param $option
     */
    public function archivePages( $option )
    {
        ?>
        <div class="yasr-settings-row-45">
            <div>
                <strong>
                    <?php 
        esc_html_e( 'Do you want to order posts by ratings?', 'yet-another-stars-rating' );
        ?>
                </strong>
                <?php 
        $array_options = array(
            'no'         => __( 'No', 'yet-another-stars-rating' ),
            'vv_most'    => __( "Sort by Visitors' ratings count", 'yet-another-stars-rating' ),
            'vv_highest' => __( "Sort by Visitors' average rating", 'yet-another-stars-rating' ),
            'overall'    => __( "Sort by Authors' rating", 'yet-another-stars-rating' ),
        );
        $default = $option['sort_posts_by'];
        $name = 'yasr_general_options[sort_posts_by]';
        $class = 'yasr-settings-archive-pages';
        echo  yasr_kses( YasrPhpFieldsHelper::radio(
            false,
            $class,
            $array_options,
            $name,
            $default
        ) ) ;
        ?>

                <div id="yasr-sort-posts-list-archives" class="yasr-sort-posts-list-archives">
                    <strong style="vertical-align: bottom;">
                        <?php 
        esc_html_e( 'Apply to:', 'yet-another-stars-rating' );
        ?>
                    </strong>
                    <span>
                        <label for="yasr-sort-posts-homepage">
                            <input
                                type="checkbox"
                                id="yasr-sort-posts-homepage"
                                value="is_home"
                                name="yasr_general_options[sort_posts_in][]"
                                <?php 
        echo  ( in_array( 'is_home', $option['sort_posts_in'] ) ? 'checked' : '' ) ;
        ?>
                            >
                            <?php 
        esc_html_e( 'Home Page', 'yet-another-stars-rating' );
        ?>
                        </label>
                    </span>
                    <span>
                        <label for="yasr-sort-posts-categories">
                            <input type="checkbox"
                                   id="yasr-sort-posts-categories"
                                   value="is_category"
                                   name="yasr_general_options[sort_posts_in][]"
                                   <?php 
        echo  ( in_array( 'is_category', $option['sort_posts_in'] ) ? 'checked' : '' ) ;
        ?>
                            >
                            <?php 
        esc_html_e( 'Categories', 'yet-another-stars-rating' );
        ?>
                        </label>
                    </span>
                    <span>
                        <label for="yasr-sort-posts-tags">
                            <input type="checkbox"
                                   id="yasr-sort-posts-tags"
                                   value="is_tag"
                                   name="yasr_general_options[sort_posts_in][]"
                                   <?php 
        echo  ( in_array( 'is_tag', $option['sort_posts_in'] ) ? 'checked' : '' ) ;
        ?>
                            >
                            <?php 
        esc_html_e( 'Tags', 'yet-another-stars-rating' );
        ?>
                        </label>
                    </span>
                </div>
            </div>

            <div>
                <div>
                    <span>
                        <strong>
                            <?php 
        esc_html_e( 'Show "Overall Rating" in Archive Pages?', 'yet-another-stars-rating' );
        ?>
                        </strong>
                    </span>
                    <div class="yasr-onoffswitch-big">
                        <input type="checkbox" name="yasr_general_options[show_overall_in_loop]" class="yasr-onoffswitch-checkbox"
                            id="yasr-show-overall-in-loop-switch" <?php 
        if ( $option['show_overall_in_loop'] === 'enabled' ) {
            echo  " checked='checked' " ;
        }
        ?> >
                        <label class="yasr-onoffswitch-label" for="yasr-show-overall-in-loop-switch">
                            <span class="yasr-onoffswitch-inner"></span>
                            <span class="yasr-onoffswitch-switch"></span>
                        </label>
                    </div>
                    <div class="yasr-element-row-container-description">
                        <?php 
        esc_html_e( 'Enable to show "Overall Rating" in archive pages.', 'yet-another-stars-rating' );
        ?>
                    </div>
                </div>
                <div>
                    <span>
                        <strong>
                            <?php 
        esc_html_e( 'Show "Visitor Votes" in Archive Page?', 'yet-another-stars-rating' );
        ?>
                        </strong>
                    </span>
                    <div class="yasr-onoffswitch-big">
                        <input type="checkbox" name="yasr_general_options[show_visitor_votes_in_loop]" class="yasr-onoffswitch-checkbox"
                            id="yasr-show-visitor-votes-in-loop-switch" <?php 
        if ( $option['show_visitor_votes_in_loop'] === 'enabled' ) {
            echo  " checked='checked' " ;
        }
        ?> >
                        <label class="yasr-onoffswitch-label" for="yasr-show-visitor-votes-in-loop-switch">
                            <span class="yasr-onoffswitch-inner"></span>
                            <span class="yasr-onoffswitch-switch"></span>
                        </label>
                    </div>
                    <div class="yasr-element-row-container-description">
                        <?php 
        esc_html_e( 'Enable to show "Visitor Votes" in archive pages', 'yet-another-stars-rating' );
        ?>
                    </div>
                </div>
            </div>
        </div>
        <p>&nbsp;</p>
        <hr>
        <?php 
    }
    
    /**
     * Display options for choose who is allowed to votes
     *
     * @author Dario Curvino <@dudo>
     * @param $option
     */
    public function loggedOnly( $option )
    {
        ?>
        <div class="yasr-settings-padding-left">
            <?php 
        $array_options = array(
            'logged_only'     => __( 'Allow only logged-in users', 'yet-another-stars-rating' ),
            'allow_anonymous' => __( 'Allow everybody (logged in and anonymous)', 'yet-another-stars-rating' ),
        );
        $default = $option['allowed_user'];
        $name = 'yasr_general_options[allowed_user]';
        $class = 'yasr_auto_insert_loggedonly';
        echo  yasr_kses( YasrPhpFieldsHelper::radio(
            false,
            $class,
            $array_options,
            $name,
            $default
        ) ) ;
        ?>
            <br />
            <?php 
        submit_button( YASR_SAVE_All_SETTINGS_TEXT );
        ?>
        </div>
        <hr />
        <?php 
    }
    
    //End function
    /**
     * Display options for vvStats
     *
     * @author Dario Curvino <@dudo>
     * @param $option
     */
    public function vvStats( $option )
    {
        ?>
        <div class="yasr-settings-row">
            <div class="yasr-settings-col-30">
                <div class="yasr-onoffswitch-big">
                    <input type="checkbox" name="yasr_general_options[visitors_stats]" class="yasr-onoffswitch-checkbox"
                           id="yasr-general-options-visitors-stats-switch" <?php 
        if ( $option['visitors_stats'] === 'yes' ) {
            echo  " checked='checked' " ;
        }
        ?> >
                    <label class="yasr-onoffswitch-label" for="yasr-general-options-visitors-stats-switch">
                        <span class="yasr-onoffswitch-inner"></span>
                        <span class="yasr-onoffswitch-switch"></span>
                    </label>
                </div>
                <br/>
                <?php 
        esc_html_e( 'Select "Yes" to enable.', 'yet-another-stars-rating' );
        ?>
                <br />
                <p>&nbsp;</p>
            </div>
            <div class="yasr-settings-col-60">
                <strong>
                    <?php 
        esc_html_e( 'Example', 'yet-another-stars-rating' );
        ?>:
                </strong>
                <br />
                <img src="<?php 
        echo  esc_url( YASR_IMG_DIR . 'yasr-settings-stats.png' ) ;
        ?>"
                     class="yasr-help-box-settings"
                     style="display: block; width: 330px"
                     alt="yasr-statsexplained">
            </div>
        </div>
        <hr />
        <?php 
    }
    
    /**
     * Display options for rich snippets
     *
     * @author Dario Curvino <@dudo>
     * @param $option
     */
    public function snippets( $option )
    {
        $publisher_name = $option['publisher_name'];
        $publisher_logo = $option['publisher_logo'];
        ?>
        <div class="yasr-settings-padding-left yasr-settings-row">
            <div class="yasr-settings-col-60">
                <strong>
                    <?php 
        esc_html_e( 'Select default itemType for all post or pages', 'yet-another-stars-rating' );
        ?>
                </strong>
                <div>
                    <?php 
        yasr_select_itemtype( 'yasr-choose-reviews-types-list', 'yasr_general_options[snippet_itemtype]', $option['snippet_itemtype'] );
        ?>

                    <div class="yasr-element-row-container-description">
                        <?php 
        esc_html_e( 'You can always change itemType in the single post or page.', 'yet-another-stars-rating' );
        ?>
                    </div>

                    <?php 
        $option_title = __( 'Choose whether the site represents an organization or a person.', 'yet-another-stars-rating' );
        $array_options = array(
            'Organization' => 'Organization',
            'Person'       => 'Person',
        );
        $default = $option['publisher'];
        $name = 'yasr_general_options[publisher]';
        $id = 'yasr-general-options-publisher';
        echo  yasr_kses( YasrPhpFieldsHelper::radio(
            $option_title,
            'none',
            $array_options,
            $name,
            $default,
            $id
        ) ) ;
        ?>
                    <br/>
                    <input type='text' name='yasr_general_options[publisher_name]'
                           id="yasr-general-options-publisher-name"
                           class="yasr-additional-info-inputs" <?php 
        printf( 'value="%s"', esc_attr( $publisher_name ) );
        ?>
                           maxlength="180"/>
                    <div class="yasr-element-row-container-description">
                        <label for="yasr-general-options-publisher-name">
                            <?php 
        esc_html_e( 'Publisher name (e.g. Google)', 'yet-another-stars-rating' );
        ?>
                        </label>
                    </div>

                    <input type='text' name='yasr_general_options[publisher_logo]'
                           id="yasr-general-options-publisher-logo"
                           class="yasr-blogPosting-additional-info-inputs"
                        <?php 
        printf( 'value="%s"', esc_url( $publisher_logo ) );
        ?>
                           maxlength="300"/>
                    <div class="yasr-element-row-container-description">
                        <label for="yasr-general-options-publisher-logo">
                            <?php 
        esc_html_e( 'Image Url (if empty siteicon will be used instead)', 'yet-another-stars-rating' );
        ?>
                        </label>
                    </div>
                </div>
            </div>

            <div class="yasr-settings-col-40" id="yasr-blogPosting-additional-info">
                <div class="yasr-help-box-settings" style="display:block">
                    <?php 
        echo  wp_kses_post( sprintf( __( 'Please keep in mind that since September, 16, 2019 blogPosting itemType will
                                        no show stars in SERP anymore. %sHere%s the announcement by Google.', 'yet-another-stars-rating' ), '<br /><br /><a href="https://webmasters.googleblog.com/2019/09/making-review-rich-results-more-helpful.html">', '</a>' ) ) ;
        echo  "<br /><br />" ;
        echo  wp_kses_post( sprintf( __( 'Also, %sread Google guidelines%s', 'yet-another-stars-rating' ), '<a href="https://developers.google.com/search/docs/data-types/review-snippet#guidelines">', '</a>.' ) ) ;
        ?>
                </div>
            </div>
        </div>
        <?php 
        submit_button( YASR_SAVE_All_SETTINGS_TEXT );
        ?>

        <hr />
        <?php 
    }
    
    //End function yasr_choose_snippet_callback
    /**
     * Display options for custom texts
     *
     * @author Dario Curvino <@dudo>
     * @param $option
     */
    public function customText( $option )
    {
        ?>
        <div>
            <?php 
        $custom_text = array(
            'txt_before_overall'    => array(
            'name'        => 'text_before_overall',
            'description' => '&sup1; ' . esc_html__( 'Custom text to display before Overall Rating', 'yet-another-stars-rating' ),
            'id'          => 'yasr-settings-custom-text-before-overall',
            'class'       => 'yasr-general-options-text-before',
        ),
            'txt_before_vv'         => array(
            'name'        => 'text_before_visitor_rating',
            'description' => '&sup2; ' . esc_html__( 'Custom text to display BEFORE Visitor Rating', 'yet-another-stars-rating' ),
            'id'          => 'yasr-settings-custom-text-before-visitor',
            'class'       => 'yasr-general-options-text-before',
        ),
            'txt_after_vv'          => array(
            'name'        => 'text_after_visitor_rating',
            'description' => '&sup2; ' . esc_html__( 'Custom text to display AFTER Visitor Rating', 'yet-another-stars-rating' ),
            'id'          => 'yasr-settings-custom-text-after-visitor',
            'class'       => 'yasr-general-options-text-before',
        ),
            'txt_login_required'    => array(
            'name'        => 'custom_text_must_sign_in',
            'description' => esc_html__( 'Custom text to display when login is required to vote', 'yet-another-stars-rating' ),
            'id'          => 'yasr-settings-custom-text-must-sign-in',
            'class'       => 'yasr-general-options-text-before',
        ),
            'txt_vv_rating_saved'   => array(
            'name'        => 'custom_text_rating_saved',
            'description' => esc_html__( 'Custom text to display when rating is saved', 'yet-another-stars-rating' ),
            'id'          => 'yasr-settings-custom-text-rating-saved',
            'class'       => 'yasr-general-options-text-before',
        ),
            'txt_vv_rating_updated' => array(
            'name'        => 'custom_text_rating_updated',
            'description' => esc_html__( 'Custom text to display when rating is updated (only for logged in users)', 'yet-another-stars-rating' ),
            'id'          => 'yasr-settings-custom-text-rating-updated',
            'class'       => 'yasr-general-options-text-before',
        ),
            'txt_vv_rated'          => array(
            'name'        => 'custom_text_user_voted',
            'description' => '&sup1; ' . esc_html__( 'Custom text to display when an user has already rated', 'yet-another-stars-rating' ),
            'id'          => 'yasr-settings-custom-text-already-rated',
            'class'       => 'yasr-general-options-text-before',
        ),
        );
        ?>
            <div class="yasr-settings-row-45" id="yasr-general-options-custom-text">
                <?php 
        self::echoSettingFields( $custom_text, $option );
        ?>
            </div>

            <div class="yasr-help-box-settings" style="display: block">
                <?php 
        $string_custom_overall = sprintf( __( '%s In these fields you can use %s pattern to show the rating (as text).', 'yet-another-stars-rating' ), '<strong>&sup1;</strong>', '<strong>%rating%</strong>' );
        $string_custom_visitor = sprintf(
            __( '%s In these fields you can use %s pattern to show the
                    total count, and %s pattern to show the average.', 'yet-another-stars-rating' ),
            '<strong>&sup2;</strong>',
            '<strong>%total_count%</strong>',
            '<strong>%average%</strong>'
        );
        $description = esc_html__( 'Leave a field empty to disable it.', 'yet-another-stars-rating' );
        $description .= '<p>' . $string_custom_overall . '</p>';
        $description .= '<p>' . $string_custom_visitor . '</p>';
        $description .= '<p>' . esc_html__( 'Allowed html tags:', 'yet-another-stars-rating' );
        $description .= '<br /><strong>' . esc_html( '<strong>, <p>' ) . '</strong>' . '.</p>';
        echo  wp_kses_post( $description ) ;
        ?>
            </div>
            <div style="padding-left: 10px; padding-bottom: 15px;">
                <p>
                    <input type="button"
                           id="yasr-settings-custom-texts"
                           class="button"
                           value="<?php 
        esc_attr_e( 'Restore default strings', 'yet-another-stars-rating' );
        ?>"
                    >
                </p>
            </div>
        </div>
        <hr />
        <?php 
    }
    
    /**
     * Display options for advanced settings
     *
     * @author Dario Curvino <@dudo>
     * @param $option
     */
    public function advancedSettings( $option )
    {
        ?>
        <div class="yasr-settings-row-45">
            <div>
                <strong>
                    <?php 
        esc_html_e( 'Load results with AJAX?', 'yet-another-stars-rating' );
        ?>
                </strong>
                <div class="yasr-onoffswitch-big">
                    <input type="checkbox" name="yasr_general_options[enable_ajax]" class="yasr-onoffswitch-checkbox"
                           id="yasr-general-options-enable-ajax-switch" <?php 
        if ( $option['enable_ajax'] === 'yes' ) {
            echo  " checked='checked' " ;
        }
        ?> >
                    <label class="yasr-onoffswitch-label" for="yasr-general-options-enable-ajax-switch">
                        <span class="yasr-onoffswitch-inner"></span>
                        <span class="yasr-onoffswitch-switch"></span>
                    </label>
                </div>
                <br/>
                <?php 
        esc_html_e( 'This should be enabled if you\'re using caching plugins.
                        Not required for yasr_overall_rating and yasr_multiset.', 'yet-another-stars-rating' );
        $caching_plugin = new YasrCachingPlugins();
        $caching_plugin_found = $caching_plugin->cachingPluginFound();
        if ( $caching_plugin_found !== false ) {
            echo  wp_kses_post( '<div class="yasr-element-row-container-description">' . sprintf( __( 'Since you\'re using the caching plugin %s you should enable this.', 'yet-another-stars-rating' ), '<strong>' . $caching_plugin_found . '</strong>' ) . '</div>' ) ;
        }
        ?>
            </div>
            <div>
                <strong>
                    <?php 
        esc_html_e( 'Do you want to save ip address?', 'yet-another-stars-rating' );
        ?>
                </strong>
                <div class="yasr-onoffswitch-big">
                    <input type="checkbox" name="yasr_general_options[enable_ip]" class="yasr-onoffswitch-checkbox"
                           id="yasr-general-options-enable-ip-switch" <?php 
        if ( $option['enable_ip'] === 'yes' ) {
            echo  " checked='checked' " ;
        }
        ?> >
                    <label class="yasr-onoffswitch-label" for="yasr-general-options-enable-ip-switch">
                        <span class="yasr-onoffswitch-inner"></span>
                        <span class="yasr-onoffswitch-switch"></span>
                    </label>
                </div>
                <br/>
                <?php 
        $string = sprintf(
            __( "In order to prevent a lot of voting fraud and attempts at automated voting, the user's IP is recorded.\n                        %s\n                        Please note that, to comply with %s EU law, you must inform your users that you are storing their \n                        IP only if you also use their IP for statistical reasons. %s \n                        If you only use the user's IP to prevent spam, there is no need to include this notification. %s \n                        For further information, click %s here. %s", 'yet-another-stars-rating' ),
            '<br />',
            '<a href="https://en.wikipedia.org/wiki/General_Data_Protection_Regulation">GDPR</a>',
            '<br />',
            '<br />',
            '<a href="https://law.stackexchange.com/a/28609">',
            '</a>'
        );
        echo  wp_kses_post( $string ) ;
        ?>
            </div>
        </div>
        <?php 
    }
    
    //End function
    /**
     * Action to do before save data into the db
     *
     * @author Dario Curvino <@dudo>
     *
     * @param $options
     *
     * @return array
     */
    public function sanitize( $options )
    {
        //Array to return
        $output = array();
        // Loop through each of the incoming options
        foreach ( $options as $key => $option ) {
            // Check to see if the current option has a value. If so, process it.
            
            if ( isset( $option ) ) {
                //Tags are not allowed for any fields
                $allowed_tags = '';
                //except these
                
                if ( $key === 'text_before_overall' || $key === 'text_before_visitor_rating' || $key === 'text_after_visitor_rating' || $key === 'custom_text_must_sign_in' || $key === 'custom_text_rating_saved' || $key === 'custom_text_rating_updated' || $key === 'custom_text_user_voted' ) {
                    $allowed_tags = '<strong><p>';
                    // handle quoted strings and allow some tags
                }
                
                //sort posts in is an array, so loop it
                
                if ( $key === 'sort_posts_in' ) {
                    
                    if ( is_array( $option ) ) {
                        foreach ( $option as $archive_name ) {
                            $output[$key][] = strip_tags( stripslashes( $archive_name ), $allowed_tags );
                        }
                    } else {
                        //if there is only one element checked, it is not an array, here I cast
                        // yasr_general_option[sort_posts_in] into an array of 1 element
                        $output[$key][] = strip_tags( stripslashes( $option ), $allowed_tags );
                    }
                
                } else {
                    $output[$key] = strip_tags( stripslashes( $option ), $allowed_tags );
                }
                
                if ( $key === 'publisher_logo' ) {
                    //if is not a valid url get_site_icon_url instead
                    if ( yasr_check_valid_url( $option ) !== true ) {
                        $output[$key] = get_site_icon_url();
                    }
                }
            }
            
            // end if
        }
        // end foreach
        /** The following steps are needed to avoid undefined index if a setting is saved to "no"  **/
        //if in array doesn't exist [auto_insert_enabled] key, create it and set to 0
        
        if ( !array_key_exists( 'auto_insert_enabled', $output ) ) {
            $output['auto_insert_enabled'] = 0;
        } else {
            $output['auto_insert_enabled'] = 1;
        }
        
        //if in array doesn't exist [stars title] key, create it and set to 'no'
        
        if ( !array_key_exists( 'stars_title', $output ) ) {
            $output['stars_title'] = 'no';
        } else {
            $output['stars_title'] = 'yes';
        }
        
        //if sort_post_in doesn't exist, cast into a single element array
        if ( !array_key_exists( 'sort_posts_in', $output ) ) {
            $output['sort_posts_in'] = array( 'is_home' );
        }
        //Same as above but for [show_overall_in_loop] key
        
        if ( !array_key_exists( 'show_overall_in_loop', $output ) ) {
            $output['show_overall_in_loop'] = 'disabled';
        } else {
            $output['show_overall_in_loop'] = 'enabled';
        }
        
        //Same as above but for [show_visitor_votes_in_loop] key
        
        if ( !array_key_exists( 'show_visitor_votes_in_loop', $output ) ) {
            $output['show_visitor_votes_in_loop'] = 'disabled';
        } else {
            $output['show_visitor_votes_in_loop'] = 'enabled';
        }
        
        //Same as above but for visitors_stats key
        
        if ( !array_key_exists( 'visitors_stats', $output ) ) {
            $output['visitors_stats'] = 'no';
        } else {
            $output['visitors_stats'] = 'yes';
        }
        
        //Same as above but for enable_ip key
        
        if ( !array_key_exists( 'enable_ip', $output ) ) {
            $output['enable_ip'] = 'no';
        } else {
            $output['enable_ip'] = 'yes';
        }
        
        //Same as above but for enable_ip key
        
        if ( !array_key_exists( 'enable_ajax', $output ) ) {
            $output['enable_ajax'] = 'no';
        } else {
            $output['enable_ajax'] = 'yes';
        }
        
        return $output;
    }
    
    /**
     * @author       Dario Curvino <@dudo>
     * @since        2.4.7
     * @param        $elementsType_array
     * @param        $option
     * @param string $option_prefix
     */
    public static function echoSettingFields( $elementsType_array, $option, $option_prefix = 'yasr_general_options' )
    {
        $string_input = false;
        $type = false;
        foreach ( $elementsType_array as $property ) {
            //concatenate yasr_general_options with property name
            $element_name = $option_prefix . '[' . $property['name'] . ']';
            
            if ( isset( $property['type'] ) ) {
                
                if ( $property['type'] === 'select' ) {
                    $string_input = YasrPhpFieldsHelper::select(
                        '',
                        $property['label'],
                        $property['options'],
                        $property['name'],
                        '',
                        esc_attr( $option[$property['name']] )
                    );
                } elseif ( $property['type'] === 'textarea' ) {
                    $string_input = YasrPhpFieldsHelper::textArea(
                        '',
                        '',
                        $property['name'],
                        '',
                        '',
                        esc_textarea( $option[$property['name']] )
                    );
                }
            
            } else {
                $type = 'text';
                $placeholder = ( isset( $property['placeholder'] ) ? $property['placeholder'] : '' );
                //if description exists, add another <div> before
                $string_input = ( isset( $property['description'] ) && $property['description'] !== '' ? '<div>' : '' );
                $string_input .= YasrPhpFieldsHelper::text(
                    $property['class'],
                    '',
                    $element_name,
                    $property['id'],
                    $placeholder,
                    esc_attr( $option[$property['name']] )
                );
            }
            
            
            if ( isset( $property['description'] ) && $property['description'] !== '' ) {
                $string_input .= '<div class="yasr-element-row-container-description">';
                $string_input .= esc_html( $property['description'] );
                //if this is coming from "text field, close 2 divs"
                $string_input .= ( $type === 'text' ? '</div>' : '' );
                $string_input .= '</div>';
            }
            
            echo  yasr_kses( $string_input ) ;
        }
    }
    
    /**
     * Returns the radio buttons that allow to select stars size
     *
     * @param      $name
     * @param      $class
     * @param bool $db_value
     * @param bool $id
     * @param bool $txt_label
     * @param bool $newline
     * return string
     *
     * @since 2.3.3
     * @return string
     */
    public static function radioSelectSize(
        $name,
        $class,
        $db_value = false,
        $id = false,
        $txt_label = true,
        $newline = false
    )
    {
        $array_size = array( 'small', 'medium', 'large' );
        $span_label = '';
        $html_to_return = '';
        foreach ( $array_size as $size ) {
            $id_string = $id . $size;
            //must be inside for each, or when loop arrive to last element
            //checked is defined
            $checked = '';
            //If db_value === false, there is no need to check for db value
            //so checked is the medium star (i.e. ranking page)
            
            if ( $db_value === false ) {
                if ( $size === 'medium' ) {
                    $checked = 'checked';
                }
            } else {
                if ( $db_value === $size ) {
                    $checked = 'checked';
                }
            }
            
            
            if ( $txt_label !== false ) {
                $span_label = '<span class="yasr-text-options-size">' . __( ucwords( $size ), 'yet-another-stars-rating' ) . '</span>';
                if ( $newline !== false ) {
                    $span_label = '<br />' . $span_label;
                }
            }
            
            $src = YASR_IMG_DIR . 'yasr-stars-' . $size . '.png';
            $html_to_return .= sprintf(
                '<div class="yasr-option-div">
                                 <label for="%s">
                                     <input type="radio"
                                         name="%s"
                                         value="%s"
                                         class="%s"
                                         id="%s"
                                         %s
                                    >
                                     <img src="%s"
                                        class="yasr-img-option-size" alt=%s>
                                     %s
                                 </label>
                            </div>',
                $id_string,
                $name,
                $size,
                $class,
                $id_string,
                $checked,
                $src,
                $size,
                $span_label
            );
        }
        //end foreach
        return $html_to_return;
    }
    
    /**
     * Print settings tabs
     *
     * @param $active_tab
     *
     * @return void
     */
    public static function printTabs( $active_tab )
    {
        $rating_plugin_exists = new YasrImportRatingPlugins();
        $rating_plugin_exists->supportedPluginFound();
        ?>

        <h2 class="nav-tab-wrapper yasr-no-underline">

            <a href="?page=yasr_settings_page&tab=general_settings"
               id="general_settings"
               class="nav-tab <?php 
        if ( $active_tab === 'general_settings' ) {
            echo  'nav-tab-active' ;
        }
        ?>">
                <?php 
        esc_html_e( 'General Settings', 'yet-another-stars-rating' );
        ?>
            </a>

            <a href="?page=yasr_settings_page&tab=style_options"
               id="style_options"
               class="nav-tab <?php 
        if ( $active_tab === 'style_options' ) {
            echo  'nav-tab-active' ;
        }
        ?>">
                <?php 
        esc_html_e( 'Aspect & Styles', 'yet-another-stars-rating' );
        ?>
            </a>

            <a href="?page=yasr_settings_page&tab=manage_multi"
               id="manage_multi"
               class="nav-tab <?php 
        if ( $active_tab === 'manage_multi' ) {
            echo  'nav-tab-active' ;
        }
        ?>">
                <?php 
        esc_html_e( 'Multi Criteria', 'yet-another-stars-rating' );
        ?>
            </a>

            <a href="?page=yasr_settings_page&tab=rankings"
               id="rankings"
               class="nav-tab <?php 
        if ( $active_tab === 'rankings' ) {
            echo  'nav-tab-active' ;
        }
        ?>">
                <?php 
        esc_html_e( 'Rankings', 'yet-another-stars-rating' );
        ?>
            </a>

            <?php 
        /**
         * Hook here to add new settings tab
         */
        do_action( 'yasr_add_settings_tab', $active_tab );
        
        if ( defined( 'YASR_RATING_PLUGIN_FOUND' ) && YASR_RATING_PLUGIN_FOUND !== false ) {
            ?>
                    <a href="?page=yasr_settings_page&tab=migration_tools"
                       id="migration_tools"
                       class="nav-tab <?php 
            if ( $active_tab === 'migration_tools' ) {
                echo  'nav-tab-active' ;
            }
            ?>">
                        <?php 
            esc_html_e( 'Migration Tools', 'yet-another-stars-rating' );
            ?>
                    </a>
                    <?php 
        }
        
        ?>

        </h2>

        <?php 
    }
    
    /**
     * Print tabs content
     *
     * @author Dario Curvino <@dudo>
     *
     * @since 3.3.1
     *
     * @param $active_tab
     *
     * @return void
     */
    public static function printTabsContent( $active_tab )
    {
        
        if ( $active_tab === 'general_settings' ) {
            ?>
            <form action="options.php" method="post" id="yasr_settings_form">
                <?php 
            settings_fields( 'yasr_general_options_group' );
            do_settings_sections( 'yasr_general_settings_tab' );
            submit_button( YASR_SAVE_All_SETTINGS_TEXT );
            ?>
            </form>
            <?php 
        }
        
        //End if tab 'general_settings'
        if ( $active_tab === 'manage_multi' ) {
            include YASR_ABSOLUTE_PATH_ADMIN . '/settings/yasr-settings-multiset.php';
        }
        //End if ($active_tab=='manage_multi')
        
        if ( $active_tab === 'style_options' ) {
            ?>
            <form action="options.php" method="post" enctype='multipart/form-data' id="yasr_settings_form">
                <?php 
            settings_fields( 'yasr_style_options_group' );
            do_settings_sections( 'yasr_style_tab' );
            submit_button( YASR_SAVE_All_SETTINGS_TEXT );
            ?>
            </form>
            <?php 
        }
        
        //End tab style
        if ( $active_tab === 'rankings' ) {
            include YASR_ABSOLUTE_PATH_ADMIN . '/settings/yasr-settings-rankings.php';
        }
        //End tab ur options
        if ( $active_tab === 'migration_tools' ) {
            //include migration functions
            include YASR_ABSOLUTE_PATH_ADMIN . '/settings/yasr-settings-migration.php';
        }
        //End tab migration
        /**
         * Hook here to add new settings tab content
         */
        do_action( 'yasr_settings_tab_content', $active_tab );
    }
    
    /**
     * Return the description of auto insert
     *
     * @author Dario Curvino <@dudo>
     * @since  2.6.6
     * @return string
     */
    public function descriptionAutoInsert()
    {
        $name = esc_html__( 'Auto Insert Options', 'yet-another-stars-rating' );
        $div_desc = '<div class="yasr-settings-description">';
        $description = sprintf( esc_html__( 'Automatically adds YASR in your posts or pages. %s
            Disable this if you prefer to use shortcodes.', 'yet-another-stars-rating' ), '<br />' );
        $end_div = '</div>';
        return $name . $div_desc . $description . $end_div;
    }
    
    /**
     * @author Dario Curvino <@dudo>
     * @since  2.6.6
     * @return string
     */
    public function descriptionStarsTitle()
    {
        $name = esc_html__( 'Enable stars next to the title?', 'yet-another-stars-rating' );
        $div_desc = '<div class="yasr-settings-description">';
        $description = esc_html__( 'Enable this if you want to show stars next to the title.', 'yet-another-stars-rating' );
        $description .= '<br />';
        $description .= esc_html__( 'Please note that this may not work with all themes', 'yet-another-stars-rating' );
        $end_div = '.</div>';
        return $name . $div_desc . $description . $end_div;
    }
    
    /**
     * @author Dario Curvino <@dudo>
     * @since  2.6.6
     * @return string
     */
    public function descriptionArchivePage()
    {
        $name = esc_html__( 'Archive Pages', 'yet-another-stars-rating' );
        $div_desc = '<div class="yasr-settings-description">';
        $description = esc_html__( 'Here you can order your posts by ratings (please note that this may not work with all themes)
            and enable/disable ratings in your archive pages (homepage, categories, tags, etc.)', 'yet-another-stars-rating' );
        $end_div = '.</div>';
        return $name . $div_desc . $description . $end_div;
    }
    
    /**
     * @author Dario Curvino <@dudo>
     * @since  2.6.6
     * @return string
     */
    public function descriptionAllowVote()
    {
        $name = esc_html__( 'Who is allowed to vote?', 'yet-another-stars-rating' );
        $div_desc = '<div class="yasr-settings-description">';
        $description = sprintf(
            esc_html__( 'Select who can rate your posts for %syasr_visitor_votes%s and %syasr_visitor_multiset%s shortcodes', 'yet-another-stars-rating' ),
            '<em>',
            '</em>',
            '<em>',
            '</em>'
        );
        $end_div = '.</div>';
        return $name . $div_desc . $description . $end_div;
    }
    
    /**
     * @author Dario Curvino <@dudo>
     * @since  2.6.6
     * @return string
     */
    public function descriptionVVStats()
    {
        $name = esc_html__( 'Show stats for visitors votes?', 'yet-another-stars-rating' );
        $div_desc = '<div class="yasr-settings-description">';
        $description = sprintf( esc_html__( 'Enable or disable the chart bar icon (and tooltip hover it) next to the %syasr_visitor_votes%s shortcode', 'yet-another-stars-rating' ), '<em>', '</em>' );
        $end_div = '.</div>';
        return $name . $div_desc . $description . $end_div;
    }
    
    /**
     * @author Dario Curvino <@dudo>
     * @since  2.6.6
     * @return string
     */
    public function descriptionCSTMTxt()
    {
        $name = esc_html__( 'Customize strings', 'yet-another-stars-rating' );
        $div_desc = '<div class="yasr-settings-description">';
        $description = '<p>' . esc_html__( 'Customize YASR strings.', 'yet-another-stars-rating' ) . '</p>';
        $end_div = '</div>';
        return $name . $div_desc . $description . $end_div;
    }
    
    /**
     * @author Dario Curvino <@dudo>
     * @since  2.6.6
     * @return string
     */
    public function descriptionStructuredData()
    {
        $name = esc_html__( 'Structured data options', 'yet-another-stars-rating' );
        $div_desc = '<div class="yasr-settings-description">';
        $description = esc_html__( 'If ratings in a post or page are found, YASR will create structured data to show them in search results
    (SERP)', 'yet-another-stars-rating' );
        $description .= '<br /><a href="https://yetanotherstarsrating.com/docs/rich-snippet/reviewrating-and-aggregaterating/?utm_source=wp-plugin&utm_medium=settings_resources&utm_campaign=yasr_settings&utm_content=yasr_rischnippets_desc"
                        target="_blank">';
        $description .= esc_html__( 'More info here', 'yet-another-stars-rating' );
        $description .= '</a>';
        $end_div = '.</div>';
        return $name . $div_desc . $description . $end_div;
    }
    
    /**
     * @author Dario Curvino <@dudo>
     * @since 1.9.5
     */
    public static function printRightColumn()
    {
        add_thickbox();
        ?>
        <div id="yasr-settings-panel-right">
            <?php 
        do_action( 'yasr_right_settings_panel_box' );
        self::upgradeBox();
        self::resourcesBox();
        self::donations();
        self::relatedPlugins();
        self::askRating();
        ?>
        </div>
        <?php 
    }
    
    private static function upgradeBox()
    {
        
        if ( yasr_fs()->is_free_plan() ) {
            ?>

            <div class="yasr-donatedivdx">
                <h2 class="yasr-donate-title" style="color: #34A7C1">
                    <?php 
            esc_html_e( 'Upgrade to YASR Pro', 'yet-another-stars-rating' );
            ?>
                </h2>
                <div class="yasr-upgrade-to-pro">
                    <ul>
                        <li><strong><?php 
            esc_html_e( 'User Reviews', 'yet-another-stars-rating' );
            ?></strong></li>
                        <li><strong><?php 
            esc_html_e( 'Custom Rankings', 'yet-another-stars-rating' );
            ?></strong></li>
                        <li><strong><?php 
            esc_html_e( '20+ ready to use themes', 'yet-another-stars-rating' );
            ?></strong></li>
                        <li><strong><?php 
            esc_html_e( 'Upload your own theme', 'yet-another-stars-rating' );
            ?></strong></li>
                        <li><strong><?php 
            esc_html_e( 'Fake ratings', 'yet-another-stars-rating' );
            ?></strong></li>
                        <li><strong><?php 
            esc_html_e( 'Dedicate support', 'yet-another-stars-rating' );
            ?></strong></li>
                        <li>
                            <strong>
                                <a href="https://yetanotherstarsrating.com/?utm_source=wp-plugin&utm_medium=settings_resources&utm_campaign=yasr_settings&utm_content=yasr-pro#yasr-pro">
                                    <?php 
            esc_html_e( '...And much more!!', 'yet-another-stars-rating' );
            ?>
                                </a>
                            </strong>
                        </li>
                    </ul>
                    <a href="<?php 
            echo  esc_url( yasr_fs()->get_upgrade_url() ) ;
            ?>">
                        <button class="button button-primary">
                        <span style="font-size: large; font-weight: bold;">
                            <?php 
            esc_html_e( 'Upgrade Now', 'yet-another-stars-rating' );
            ?>
                        </span>
                        </button>
                    </a>
                    <div style="display: block; margin-top: 10px; margin-bottom: 10px; ">
                        --- or ---
                    </div>
                    <a href="<?php 
            echo  esc_url( yasr_fs()->get_trial_url() ) ;
            ?>">
                        <button class="button button-primary">
                        <span style="display: block; font-size: large; font-weight: bold; margin: -3px;">
                            <?php 
            esc_html_e( 'Start Free Trial', 'yet-another-stars-rating' );
            ?>
                        </span>
                            <span style="display: block; margin-top: -10px; font-size: smaller;">
                             <?php 
            esc_html_e( 'No credit-card, risk free!', 'yet-another-stars-rating' );
            ?>
                        </span>
                        </button>
                    </a>
                </div>
            </div>

            <?php 
        }
    
    }
    
    /*
     *   Add a box on with the resouces
     *   Since version 1.9.5
     *
     */
    private static function resourcesBox()
    {
        ?>

        <div class='yasr-donatedivdx' id='yasr-resources-box'>
            <div class="yasr-donate-title">Resources</div>
            <div class="yasr-donate-single-resource">
                <span class="dashicons dashicons-star-filled" style="color: #6c6c6c"></span>
                <a target="blank" href="https://yetanotherstarsrating.com/?utm_source=wp-plugin&utm_medium=settings_resources&utm_campaign=yasr_settings&utm_content=yasr_official">
                    <?php 
        esc_html_e( 'YASR official website', 'yet-another-stars-rating' );
        ?>
                </a>
            </div>
            <div class="yasr-donate-single-resource">
                <img src="<?php 
        echo  esc_attr( YASR_IMG_DIR . 'github.svg' ) ;
        ?>"
                     width="20" height="20" alt="github logo" style="vertical-align: bottom;">
                <a target="blank" href="https://github.com/Dudo1985/yet-another-stars-rating">
                    GitHub Page
                </a>
            </div>
            <div class="yasr-donate-single-resource">
                <span class="dashicons dashicons-edit" style="color: #6c6c6c"></span>
                <a target="blank" href="https://yetanotherstarsrating.com/docs/?utm_source=wp-plugin&utm_medium=settings_resources&utm_campaign=yasr_settings&utm_content=documentation">
                    <?php 
        esc_html_e( 'Documentation', 'yet-another-stars-rating' );
        ?>
                </a>
            </div>
            <div class="yasr-donate-single-resource">
                <span class="dashicons dashicons-video-alt3" style="color: #6c6c6c"></span>
                <a target="blank" href="https://www.youtube.com/channel/UCU5jbO1PJsUUsCNbME9S-Zw">
                    <?php 
        esc_html_e( 'Youtube channel', 'yet-another-stars-rating' );
        ?>
                </a>
            </div>
            <div class="yasr-donate-single-resource">
                <span class="dashicons dashicons-smiley" style="color: #6c6c6c"></span>
                <a target="blank" href="https://yetanotherstarsrating.com/?utm_source=wp-plugin&utm_medium=settings_resources&utm_campaign=yasr_settings&utm_content=yasr-pro#yasr-pro">
                    Yasr Pro
                </a>
            </div>
        </div>

        <?php 
    }
    
    /**
     * Adds buy a cofee box
     *
     * @author Dario Curvino <@dudo>
     */
    private static function donations()
    {
        $donation_text = '<p>';
        $donation_text .= esc_html__( 'First version of YASR was released in 2014.', 'yet-another-stars-rating' );
        $donation_text .= '</p>';
        $donation_text .= '<p>';
        
        if ( yasr_fs()->is_free_plan() ) {
            $donation_text .= esc_html__( 'I can still work on it only thanks to all the people who bought the PRO version over the years.', 'yet-another-stars-rating' );
            $donation_text .= '</p>';
            $donation_text .= esc_html__( "If you don't need the pro version, you may consider to make a donation, thanks!", 'yet-another-stars-rating' );
        } else {
            $donation_text .= esc_html__( 'I can still work on it only thanks to all the amazing people like you who bought the PRO version over the years.', 'yet-another-stars-rating' );
            $donation_text .= '</p>';
            $donation_text .= esc_html__( "If you want, you can also help with a donation, thanks!", 'yet-another-stars-rating' );
        }
        
        $donation_text .= '<br />';
        $lp_image = '<a href="https://liberapay.com/~1775681" target="_blank">
                        <img src="' . YASR_IMG_DIR . '/liberapay.svg" alt="liberapay" width="150">
                     </a>';
        $kofi_image = '<a href="https://ko-fi.com/L4L6HBQQ4" target="_blank">
                        <img src="' . YASR_IMG_DIR . '/kofi.png" alt="kofi" width="150">
                     </a>';
        $div = "<div class='yasr-donatedivdx' id='yasr-buy-cofee'>";
        $text = '<div class="yasr-donate-title">' . __( 'Donations', 'yet-another-stars-rating' ) . '</div>';
        $text .= '<div>';
        $text .= $donation_text;
        $text .= '</div>';
        $text .= '<div style="margin-top:10px;">';
        $text .= $lp_image;
        $text .= '</div>';
        $text .= '<div style="margin-top:10px;">';
        $text .= $kofi_image;
        $text .= '</div>';
        $div_and_text = $div . $text . '</div>';
        echo  wp_kses_post( $div_and_text ) ;
    }
    
    /**
     * Show related plugins
     *
     * @author Dario Curvino <@dudo>
     */
    private static function relatedPlugins()
    {
        $div = "<div class='yasr-donatedivdx' id='yasr-related-plugins'>";
        $text = '<div class="yasr-donate-title">' . esc_html__( 'You may also like...', 'yet-another-stars-rating' ) . '</div>';
        $text .= self::movieHelper();
        $text .= '<hr />';
        $text .= self::cnrt();
        $div_and_text = $div . $text . '</div>';
        echo  wp_kses_post( $div_and_text ) ;
    }
    
    /**
     * @author Dario Curvino <@dudo>
     * @since 2.9.3
     * @return string
     */
    private static function movieHelper()
    {
        $url = add_query_arg( array(
            'tab'       => 'plugin-information',
            'plugin'    => 'yet-another-movie',
            'TB_iframe' => 'true',
            'width'     => '772',
            'height'    => '670',
        ), network_admin_url( 'plugin-install.php' ) );
        $movie_helper_description = esc_html__( 'Movie Helper allows you to easily add links to movie and tv shows, just by searching
    them while you\'re writing your content. Search, click, done!', 'yet-another-stars-rating' );
        $text = '<h4>Movie Helper</h4>';
        $text .= '<div style="margin-top: 15px;">';
        $text .= $movie_helper_description;
        $text .= '</div>';
        $text .= '<div style="margin-top: 15px;">
                <a href="' . esc_url( $url ) . '"
                   class="install-now button thickbox open-plugin-details-modal"
                   target="_blank">' . __( 'Install', 'yet-another-stars-rating' ) . '</a>';
        $text .= '</div>';
        return $text;
    }
    
    /**
     * @author Dario Curvino <@dudo>
     * @since 2.9.3
     * @return string
     */
    private static function cnrt()
    {
        $url = add_query_arg( array(
            'tab'       => 'plugin-information',
            'plugin'    => 'comments-not-replied-to',
            'TB_iframe' => 'true',
            'width'     => '772',
            'height'    => '670',
        ), network_admin_url( 'plugin-install.php' ) );
        $text = '<h4>Comments Not Replied To</h4>';
        $text .= '<div style="margin-top: 15px;">';
        $text .= esc_html__( '"Comments Not Replied To" introduces a new area in the administrative dashboard that allows you to
        see what comments to which you - as the site author - have not yet replied.', 'yet-another-stars-rating' );
        $text .= '</div>';
        $text .= '<div style="margin-top: 15px;">
                <a href="' . esc_url( $url ) . '"
                   class="install-now button thickbox open-plugin-details-modal"
                   target="_blank">' . __( 'Install', 'yet-another-stars-rating' ) . '</a>';
        $text .= '</div>';
        return $text;
    }
    
    /** Add a box on the right for asking to rate 5 stars on Wordpress.org
     *   Since version 0.9.0
     */
    private static function askRating()
    {
        $div = "<div class='yasr-donatedivdx' id='yasr-ask-five-stars'>";
        $text = '<div class="yasr-donate-title">' . esc_html__( 'Can I ask your help?', 'yet-another-stars-rating' ) . '</div>';
        $text .= '<div style="font-size: 32px; color: #F1CB32; text-align:center; margin-bottom: 20px; margin-top: -5px;">
                <span class="dashicons dashicons-star-filled" style="font-size: 26px;"></span>
                <span class="dashicons dashicons-star-filled" style="font-size: 26px;"></span>
                <span class="dashicons dashicons-star-filled" style="font-size: 26px;"></span>
                <span class="dashicons dashicons-star-filled" style="font-size: 26px;"></span>
                <span class="dashicons dashicons-star-filled" style="font-size: 26px;"></span>
            </div>';
        $text .= esc_html__( 'Please rate YASR 5 stars on', 'yet-another-stars-rating' );
        $text .= ' <a href="https://wordpress.org/support/view/plugin-reviews/yet-another-stars-rating?filter=5">
        WordPress.org.</a><br />';
        $text .= esc_html__( ' It will require just 1 min but it\'s a HUGE help for me. Thank you.', 'yet-another-stars-rating' );
        $text .= "<br /><br />";
        $text .= "<em>> Dario Curvino</em>";
        $div_and_text = $div . $text . '</div>';
        echo  wp_kses_post( $div_and_text ) ;
    }
    
    /*
     * @author Dario Curvino <@dudo>
     *
     * @param $text
     *
     * @return mixed|string
     */
    public function customFooter( $text )
    {
        
        if ( isset( $_GET['page'] ) ) {
            $yasr_page = $_GET['page'];
            
            if ( $yasr_page === 'yasr_settings_page' || $yasr_page === 'yasr_stats_page' ) {
                $custom_text = ' | <i>';
                $custom_text .= sprintf(
                    esc_html__( 'Thank you for using %s. Please %s rate it%s 5 stars on %s', 'yet-another-stars-rating' ),
                    '<a href="https://yetanotherstarsrating.com/?utm_source=wp-plugin&utm_medium=footer&utm_campaign=yasr_settings"
                            target="_blank">Yet Another Stars Rating</a>',
                    '<a href="https://wordpress.org/support/view/plugin-reviews/yet-another-stars-rating?filter=5" target="_blank">',
                    '</a>',
                    '<a href="https://wordpress.org/support/view/plugin-reviews/yet-another-stars-rating?filter=5" target="_blank">
                    WordPress.org</a>'
                );
                $custom_text .= '</i>';
                return $text . $custom_text;
            }
            
            return $text;
        }
        
        return $text;
    }
    
    /**
     * Print a div with class "notice-success"
     * https://digwp.com/2016/05/wordpress-admin-notices/
     *
     * @author Dario Curvino <@dudo>
     *
     * @param $message
     *
     * @since  3.1.7
     * @return void
     */
    public static function printNoticeSuccess( $message )
    {
        ?>
        <div class="notice notice-success">
            <p>
                <strong>
                    <?php 
        echo  esc_html( $message ) ;
        ?>
                </strong>
            </p>
        </div>
        <?php 
    }
    
    /**
     * Print a div with class "notice-error"
     * https://digwp.com/2016/05/wordpress-admin-notices/
     *
     * @author Dario Curvino <@dudo>
     *
     * @param string | array $message
     *
     * @since  3.1.7
     * @return void
     */
    public static function printNoticeError( $message )
    {
        ?>
        <div class="notice notice-error">
            <p>
                <strong>
                    <?php 
        
        if ( is_array( $message ) ) {
            foreach ( $message as $error ) {
                echo  esc_html( $error ) . '<br />' ;
            }
        } else {
            echo  esc_html( $message ) ;
        }
        
        ?>
                </strong>
            </p>
        </div>
        <?php 
    }

}