<?php

/**
 * This file is based on https://github.com/Freemius/wordpress-sdk/blob/095fc9ced29efef5b18f9b7242bb80b0b4ac6aff/templates/connect.php
 *
 * In version 2.5.6 of the Freemius sdk, translations get broken.
 * This was fixed in version 2.5.6.1, but this could happen again.
 *
 * Further this, translation get old, and the optin page is a LEGAL page.
 * To be sure that the plugin is ALWAYS 100% compliant, I've copied the original connect.php file and remove all the translation
 * function.
 *
 * For over a decade, GPL was in English only. I'm just doing the same thing here.
 *
 * To see the changes, see this
 * https://github.com/Dudo1985/freemius-custom-optin-page/commit/a7f974618b2f2ab324892716d4e091302dc82faa?diff=split&w=1
 */

/**
 * @since       1.0.7
 * @copyright   Copyright (c) 2015, Freemius, Inc.
 * @license     https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License Version 3
 * @package     Freemius
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @var array    $VARS
 * @var Freemius $fs
 */
$fs   = freemius(256);
$slug = $fs->get_slug();

$is_pending_activation = $fs->is_pending_activation();
$is_premium_only       = $fs->is_only_premium();
$has_paid_plans        = $fs->has_paid_plan();
$is_premium_code       = $fs->is_premium();
$is_freemium           = $fs->is_freemium();

$fs->_enqueue_connect_essentials();

/**
 * Enqueueing the styles in `_enqueue_connect_essentials()` is too late, as we need them in the HEADER. Therefore,
 * inject the styles inline to avoid FOUC.
 *
 * @author Vova Feldman (@svovaf)
 */
echo "<style>\n";
include WP_FS__DIR_CSS . '/admin/connect.css';
echo "</style>\n";

$current_user = Freemius::_get_current_wp_user();

$first_name = $current_user->user_firstname;
if (empty($first_name)) {
    $first_name = $current_user->nickname;
}

$site_url     = Freemius::get_unfiltered_site_url();
$protocol_pos = strpos($site_url, '://');
if (false !== $protocol_pos) {
    $site_url = substr($site_url, $protocol_pos + 3);
}

$freemius_site_www = 'https://freemius.com';

$freemius_usage_tracking_url = $fs->get_usage_tracking_terms_url();
$freemius_plugin_terms_url   = $fs->get_eula_url();

$freemius_site_url = $fs->is_premium() ? $freemius_site_www : $freemius_usage_tracking_url;

if ($fs->is_premium()) {
    $freemius_site_url .= '?' . http_build_query(array(
            'id'   => $fs->get_id(),
            'slug' => $slug,
        ));
}

$freemius_link = '<a href="' . $freemius_site_url . '" target="_blank" rel="noopener" tabindex="1">freemius.com</a>';

$error = fs_request_get('error');

$has_release_on_freemius = $fs->has_release_on_freemius();

$require_license_key = $is_premium_only
    || ($is_freemium && ($is_premium_code || !$has_release_on_freemius)
        && fs_request_get_bool(
            'require_license', ($is_premium_code || $has_release_on_freemius)
        ));

if ($is_pending_activation) {
    $require_license_key = false;
}

if ($require_license_key) {
    $fs->_add_license_activation_dialog_box();
}

$is_optin_dialog = ($fs->is_theme() && $fs->is_themes_page() && $fs->show_opt_in_on_themes_page());

if ($is_optin_dialog) {
    $show_close_button             = false;
    $previous_theme_activation_url = '';

    if (!$is_premium_code) {
        $show_close_button = true;
    }
    else if ($is_premium_only) {
        $previous_theme_activation_url = $fs->get_previous_theme_activation_url();
        $show_close_button             = (!empty($previous_theme_activation_url));
    }
}

$is_network_level_activation = (fs_is_network_admin() && $fs->is_network_active()
    && !$fs->is_network_delegated_connection());

$fs_user = Freemius::_get_user_by_email($current_user->user_email);

$activate_with_current_user = (is_object($fs_user) && !$is_pending_activation
    && // If requires a license for activation, use the user associated with the license for the opt-in.
    !$require_license_key
    && !$is_network_level_activation);

$optin_params = $fs->get_opt_in_params(array(), $is_network_level_activation);
$sites        = isset($optin_params['sites']) ? $optin_params['sites'] : array();

$is_network_upgrade_mode = (fs_is_network_admin() && $fs->is_network_upgrade_mode());

/* translators: %s: name (e.g. Hey John,) */
$hey_x_text = esc_html(sprintf(fs_text_x_inline('Hey %s,', 'greeting', 'hey-x', $slug), $first_name));

$activation_state = array(
    'is_license_activation'       => $require_license_key,
    'is_pending_activation'       => $is_pending_activation,
    'is_gdpr_required'            => true,
    'is_network_level_activation' => $is_network_level_activation,
    'is_dialog'                   => $is_optin_dialog,
);


if ($is_optin_dialog) { ?>
    <div id="fs_theme_connect_wrapper">
        <?php
            if ($show_close_button) { ?>
                <button class="close dashicons dashicons-no">
                    <span class="screen-reader-text">Close connect dialog</span>
                </button>
                <?php
            }
        }
        ?>
        <div id="fs_connect"
             class="wrap<?php if (!fs_is_network_admin()
                 && (!$fs->is_enable_anonymous() || $is_pending_activation
                     || $require_license_key)
             ) {
                 echo ' fs-anonymous-disabled';
             } ?><?php echo $require_license_key ? ' require-license-key' : '' ?>">

            <div class="fs-header">
                <?php
                $size      = 50;
                $image_url = YASR_IMG_DIR . '/yet-another-stars-rating.png';
                ?>
                <div class="fs-plugin-icon">
                    <img src="<?php echo esc_url($image_url) ?>"
                         width="<?php echo esc_attr($size) ?>"
                         height="<?php echo esc_attr($size) ?>"
                         alt="logo"
                    />
                </div>
            </div>

            <div class="fs-box-container">
                <div class="fs-content">
                    <?php if (!empty($error)) : ?>
                        <div class="fs-error">
                            <?php echo $fs->apply_filters('connect_error_esc_html', esc_html($error)) ?>
                        </div>
                    <?php endif ?>
                    <?php
                    if (!$is_pending_activation && !$require_license_key) {
                        if (!$fs->is_plugin_update()) {
                            echo '<h2 style="text-align: center">
                                      Never miss an important update
                                  </h2>';
                        }
                        else {
                            echo sprintf(
                                '<h2>%s</h2>', sprintf(
                                    'Thank you for updating to %s v%s!', esc_html($fs->get_plugin_name()),
                                    $fs->get_plugin_version()
                                )
                            );
                        }
                    }
                    ?>
                    <p>
                        <?php
                        $button_label = 'Allow & Continue';
                        $message      = '';

                        if ($is_pending_activation) {
                            $button_label = 'Re-send activation email';

                            $message = sprintf(
                                '<strong>Thanks!</strong> <br /> 
                                You should receive a confirmation email for %s to your mailbox at %s. 
                                Please make sure you click the button in that email to complete the opt-in.',
                                '<b>' . $fs->get_plugin_name() . '</b>', '<b>' . $current_user->user_email . '</b>'
                            );
                        }
                        else if ($require_license_key) {
                            $button_label = 'Activate License';

                            $message = sprintf(
                                sprintf(
                                    'Welcome to %s! To get started, please enter your license key:',
                                    '<b>' . $fs->get_plugin_name() . '</b>'
                                ), $first_name, $fs->get_plugin_name()
                            );
                        }
                        else {
                            if (!$fs->is_plugin_update()) {
                                $default_optin_message = esc_html(
                                    sprintf(
                                    /* translators: %s: module type (plugin, theme, or add-on) */ 'Opt in to get email notifications for security & feature updates, 
                                            educational content, and occasional offers, and to share some basic WordPress 
                                            environment info. This will help us make the %s more compatible with your site 
                                            and better at doing what you need it to.', $fs->get_module_label(true)
                                    )
                                );
                            }
                            else {
                                // If Freemius was added on a plugin update, set different
                                // opt-in message.

                                /* translators: %s: module type (plugin, theme, or add-on) */
                                $default_optin_message = esc_html(
                                    sprintf(
                                        'We have introduced this opt-in so you never miss an important update and 
                                                help us make the %s more compatible with your site and better at doing what 
                                                you need it to.', $fs->get_module_label(true)
                                    )
                                );

                                $default_optin_message .= '<br><br>
                                    Opt in to get email notifications for security & feature 
                                    updates, educational content, and occasional offers, and to share some basic WordPress 
                                    environment info.';

                                if ($fs->is_enable_anonymous()) {
                                    $default_optin_message .= ' If you skip this, that\'s okay! %1$s will still work just fine.';
                                }
                            }

                            $message = sprintf(
                                $default_optin_message,
                                '<b>' . esc_html($fs->get_plugin_name()) . '</b>',
                                '<b>' . $current_user->user_login . '</b>',
                                '<a href="' . $site_url . '" target="_blank" rel="noopener noreferrer">' . $site_url . '</a>',
                                $freemius_link
                            );
                        }

                        if ($is_network_upgrade_mode) {
                            $network_integration_text
                                = 'We\'re excited to introduce the  Freemius network-level integration.';

                            if ($is_premium_code) {
                                $message = $network_integration_text . ' ' . sprintf(
                                        'During the update process we detected %d site(s) that are still pending license activation.',
                                        count($sites)
                                    );

                                $message .= '<br><br>' . sprintf(
                                        'If you\'d like to use the %s on those sites, please enter your license 
                                        key below and click the activation button.', $is_premium_only
                                        ? $fs->get_module_label(true)
                                        : sprintf(
                                        /* translators: %s: module type (plugin, theme, or add-on) */
                                            "%s's paid features", $fs->get_module_label(true)
                                        )
                                    );

                                /* translators: %s: module type (plugin, theme, or add-on) */
                                $message .= ' ' . sprintf(
                                        'Alternatively, you can skip it for now and activate the license 
                                        later, in your %s\'s network-level Account page.', $fs->get_module_label(true)
                                    );
                            }
                            else {
                                $message = $network_integration_text . ' ' . sprintf(
                                        'During the update process we detected %s site(s) in the  network that 
                                    are still pending your attention.', count($sites)
                                    ) . '<br><br>' . (fs_starts_with($message, $hey_x_text . '<br>') ? substr(
                                        $message, strlen($hey_x_text . '<br>')
                                    ) : $message);
                            }
                        }

                        echo wp_kses_post($message);
                        ?>
                    </p>

                    <?php if ($require_license_key) : ?>
                        <div class="fs-license-key-container">
                            <label for="fs_license_key"></label>
                            <input id="fs_license_key" name="fs_key" type="text" required
                                   maxlength="<?php echo $fs->apply_filters('license_key_maxlength', 32) ?>"
                                   placeholder="License key"
                                   tabindex="1"
                            />
                            <i class="dashicons dashicons-admin-network"></i>
                            <a class="show-license-resend-modal show-license-resend-modal-<?php echo $fs->get_unique_affix(
                            ) ?>"
                               href="#">
                                Can't find your license key?
                            </a>
                        </div>

                        <?php
                        $send_updates_text = sprintf(
                            '%s<span class="action-description"> - %s</span>', 'Yes',
                            'send me security & feature updates, educational content and offers.'
                        );

                        $do_not_send_updates_text = sprintf(
                            '%s<span class="action-description"> - %s</span>', 'No',
                            'do <span class="underlined">NOT</span> send me security & feature updates, educational content and offers.'
                        );
                        ?>

                        <div id="fs_marketing_optin">
                            <span class="fs-message">
                                Please let us know if you'd like us to contact you for security &
                                feature updates, educational content, and occasional offers:
                            </span>
                            <div class="fs-input-container">
                                <label>
                                    <input type="radio" name="allow-marketing" value="true" tabindex="1"/>
                                    <span class="fs-input-label">
                                        <?php echo wp_kses_post($send_updates_text) ?>
                                    </span>
                                </label>
                                <label>
                                    <input type="radio" name="allow-marketing" value="false" tabindex="1"/>
                                    <span class="fs-input-label">
                                        <?php echo wp_kses_post($do_not_send_updates_text) ?>
                                    </span>
                                </label>
                            </div>
                        </div>

                    <?php endif ?>
                    <?php if ($is_network_level_activation) : ?>
                        <?php
                        $vars = array(
                            'id'                  => $fs->get_id(),
                            'sites'               => $sites,
                            'require_license_key' => $require_license_key
                        );

                        echo fs_get_template('partials/network-activation.php', $vars);
                        ?>
                    <?php endif ?>

                </div>

                <div class="fs-actions">

                    <?php if ($fs->is_enable_anonymous() && !$is_pending_activation
                        && (!$require_license_key
                            || $is_network_upgrade_mode)
                    ) : ?>
                        <a id="skip_activation"
                           href="<?php echo fs_nonce_url(
                               $fs->_get_admin_page_url(
                                   '', array('fs_action' => $fs->get_unique_affix() . '_skip_activation'),
                                   $is_network_level_activation
                               ), $fs->get_unique_affix() . '_skip_activation'
                           ) ?>"
                           class="button button-secondary"
                           tabindex="2">
                            Skip
                        </a>
                    <?php endif ?>

                    <?php if ($is_network_level_activation && $fs->apply_filters('show_delegation_option', true)) : ?>
                        <a id="delegate_to_site_admins"
                           class="fs-tooltip-trigger <?php echo is_rtl() ? ' rtl' : '' ?>"
                           href="<?php echo fs_nonce_url(
                               $fs->_get_admin_page_url(
                                   '', array('fs_action' => $fs->get_unique_affix() . '_delegate_activation')
                               ), $fs->get_unique_affix() . '_delegate_activation'
                           ) ?>">
                            Delegate to Site Admins
                            <span class="fs-tooltip">
                                If you click it, this decision will be delegated to the sites administrators.
                            </span>
                        </a>
                    <?php endif ?>

                    <?php if ($activate_with_current_user) : ?>
                        <form action="" method="POST">
                            <input type="hidden" name="fs_action"
                                   value="<?php echo $fs->get_unique_affix() ?>_activate_existing">
                            <?php wp_nonce_field('activate_existing_' . $fs->get_public_key()) ?>
                            <input type="hidden" name="is_extensions_tracking_allowed" value="1">
                            <input type="hidden" name="is_diagnostic_tracking_allowed" value="1">
                            <button class="button button-primary" tabindex="1"
                                    type="submit"><?php echo esc_html($button_label) ?></button>
                        </form>
                    <?php else : ?>
                        <form method="post" action="<?php echo WP_FS__ADDRESS ?>/action/service/user/install/">
                            <?php unset($optin_params['sites']); ?>
                            <?php foreach ($optin_params as $name => $value) : ?>
                                <input type="hidden" name="<?php echo esc_attr($name) ?>"
                                       value="<?php echo esc_attr($value) ?>">
                            <?php endforeach ?>
                            <input type="hidden" name="is_extensions_tracking_allowed" value="1">
                            <input type="hidden" name="is_diagnostic_tracking_allowed" value="1">
                            <button class="button button-primary" tabindex="1"
                                    type="submit"<?php if ($require_license_key) {
                                echo ' disabled="disabled"';
                            } ?>><?php echo esc_html($button_label) ?></button>
                        </form>
                    <?php endif ?>

                    <?php if ($require_license_key) : ?>
                        <a id="license_issues_link"
                           href="https://freemius.com/help/documentation/wordpress-sdk/license-activation-issues/"
                           target="_blank">
                            License issues?
                        </a>
                    <?php endif ?>

                </div>

                <?php
                    $permission_manager = FS_Permission_Manager::instance($fs);

                    // Set core permission list items.
                    $permissions = array();

                    // Add newsletter permissions if enabled.
                    if ($fs->is_permission_requested('newsletter')) {
                        $permissions[] = $permission_manager->get_newsletter_permission();
                    }

                    $permissions = $permission_manager->get_permissions(
                        $require_license_key, $permissions
                    );

                    if (!empty($permissions)) : ?>
                        <div class="fs-permissions">
                            <?php if ($require_license_key) : ?>
                                <a class="fs-trigger wp-core-ui" href="#" tabindex="1" style="color: inherit;">
                                    <?php
                                    echo sprintf(
                                        'For delivery of security & feature updates, and license 
                                                management, %s needs to <b class="fs-arrow"></b>', sprintf(
                                            '<nobr class="button-link" style="color: inherit;">%s</nobr>',
                                            $fs->get_plugin_title()
                                        )
                                    )
                                    ?>
                                </a>
                            <?php else : ?>
                                <a class="fs-trigger wp-core-ui" href="#" tabindex="1" style="color: inherit;">
                                    <?php printf(
                                        'This will allow %s to' . '<b class="fs-arrow"></b>', sprintf(
                                            '<nobr class="button-link" style="color: inherit;">%s</nobr>',
                                            $fs->get_plugin_title()
                                        )
                                    ) ?>
                                </a>
                            <?php endif ?>
                            <ul>
                                <?php
                                foreach ($permissions as $permission) {
                                    $permission_manager->render_permission($permission);
                                }
                                ?>
                            </ul>
                        </div>
                    <?php endif ?>

                    <?php if ($is_premium_code && $is_freemium) : ?>
                        <div class="fs-freemium-licensing">
                            <p>
                                <?php if ($require_license_key) : ?>
                                    Don't have a license key?
                                    <a data-require-license="false" tabindex="1">
                                        Activate Free Version
                                    </a>
                                <?php else : ?>
                                    Have a license key?
                                    <a data-require-license="true" tabindex="1">
                                        Activate License
                                    </a>
                                <?php endif ?>
                            </p>
                        </div>
                    <?php endif
                ?>
            </div>

            <div class="fs-terms">
                <a class="fs-tooltip-trigger<?php echo is_rtl() ? ' rtl' : '' ?>"
                   href="<?php echo esc_url($freemius_site_url) ?>"
                   target="_blank"
                   rel="noopener"
                   tabindex="1">
                    Powered by Freemius
                    <?php if ($require_license_key) : ?>
                        <span class="fs-tooltip" style="width: 170px">
                            Freemius is our licensing and software updates engine
                        </span>
                    <?php endif ?>
                </a>
                &nbsp;&nbsp;-&nbsp;&nbsp;
                <a href="https://freemius.com/privacy/" target="_blank" rel="noopener"
                   tabindex="1">Privacy Policy
                </a>
                &nbsp;&nbsp;-&nbsp;&nbsp;
                <a href="<?php echo esc_url(
                    $require_license_key ? $freemius_plugin_terms_url : $freemius_usage_tracking_url
                ) ?>"
                   target="_blank"
                   rel="noopener"
                   tabindex="1">
                    <?php echo $require_license_key ? 'License Agreement' : 'Terms of Service' ?>
                </a>
            </div>
        </div>
        <?php
            if ($is_optin_dialog) { ?>
    </div> <!-- Close fs_theme_connect_wrapper -->
        <?php
    }
?>
    <script type="text/javascript">
        (function ($) {
            var $html = $('html');

            <?php
            if ( $is_optin_dialog ) {
            if ( $show_close_button ) { ?>
            var $themeConnectWrapper = $('#fs_theme_connect_wrapper');

            $themeConnectWrapper.find('button.close').on('click', function () {
                <?php if ( !empty($previous_theme_activation_url) ) { ?>
                location.href = '<?php echo html_entity_decode($previous_theme_activation_url); ?>';
                <?php } else { ?>
                $themeConnectWrapper.remove();
                $html.css({overflow: $html.attr('fs-optin-overflow')});
                <?php } ?>
            });
            <?php
            }
            ?>

            $html.attr('fs-optin-overflow', $html.css('overflow'));
            $html.css({overflow: 'hidden'});

            <?php
            }
            ?>

            var $primaryCta = $('.fs-actions .button.button-primary'),
                primaryCtaLabel = $primaryCta.html(),
                $form = $('.fs-actions form'),
                isNetworkActive = <?php echo $is_network_level_activation ? 'true' : 'false' ?>,
                requireLicenseKey = <?php echo $require_license_key ? 'true' : 'false' ?>,
                hasContextUser = <?php echo $activate_with_current_user ? 'true' : 'false' ?>,
                isNetworkUpgradeMode = <?php echo $is_network_upgrade_mode ? 'true' : 'false' ?>,
                $licenseSecret,
                $licenseKeyInput = $('#fs_license_key'),
                pauseCtaLabelUpdate = false,
                isNetworkDelegating = false,
                /**
                 * @author Leo Fajardo (@leorw)
                 * @since 2.1.0
                 */
                resetLoadingMode = function () {
                    // Reset loading mode.
                    $primaryCta.html(primaryCtaLabel);
                    $primaryCta.prop('disabled', false);
                    $('.fs-loading').removeClass('fs-loading');

                    console.log('resetLoadingMode - Primary button was enabled');
                },
                setLoadingMode = function () {
                    $(document.body).addClass('fs-loading');
                };

            $('.fs-actions .button').on('click', function () {
                setLoadingMode();

                var $this = $(this);

                setTimeout(function () {
                    if (!requireLicenseKey || !$marketingOptin.hasClass('error')) {
                        $this.attr('disabled', 'disabled');
                    }
                }, 200);
            });

            if (isNetworkActive) {
                var
                    $multisiteOptionsContainer = $('.fs-multisite-options-container'),
                    $allSitesOptions = $('.fs-all-sites-options'),
                    $applyOnAllSites = $('.fs-apply-on-all-sites-checkbox'),
                    $sitesListContainer = $('.fs-sites-list-container'),
                    totalSites = <?php echo count($sites) ?>,
                    maxSitesListHeight = null,
                    $skipActivationButton = $('#skip_activation'),
                    $delegateToSiteAdminsButton = $('#delegate_to_site_admins'),
                    hasAnyInstall = <?php echo !is_null($fs->find_first_install()) ? 'true' : 'false' ?>;

                $applyOnAllSites.click(function () {
                    var isChecked = $(this).is(':checked');

                    if (isChecked) {
                        $multisiteOptionsContainer.find('.action').removeClass('selected');
                        updatePrimaryCtaText('allow');
                    }

                    $multisiteOptionsContainer.find('.action-allow').addClass('selected');

                    $skipActivationButton.toggle();

                    $delegateToSiteAdminsButton.toggle();

                    $multisiteOptionsContainer.toggleClass('fs-apply-on-all-sites', isChecked);

                    $sitesListContainer.toggle(!isChecked);
                    if (!isChecked && null === maxSitesListHeight) {
                        /**
                         * Set the visible number of rows to 5 (5 * height of the first row).
                         *
                         * @author Leo Fajardo (@leorw)
                         */
                        maxSitesListHeight = (5 * $sitesListContainer.find('tr:first').height());
                        $sitesListContainer.css('max-height', maxSitesListHeight);
                    }
                });

                $allSitesOptions.find('.action').click(function (evt) {
                    var actionType = $(evt.target).data('action-type');

                    $multisiteOptionsContainer.find('.action').removeClass('selected');
                    $multisiteOptionsContainer.find('.action-' + actionType).toggleClass('selected');

                    updatePrimaryCtaText(actionType);
                });

                $sitesListContainer.delegate('td:not(:first-child)', 'click', function () {
                    // If a site row is clicked, trigger a click on the checkbox.
                    $(this).parent().find('td:first-child input').click();
                });

                $sitesListContainer.delegate('.action', 'click', function (evt) {
                    var $this = $(evt.target);
                    if ($this.hasClass('selected')) {
                        return false;
                    }

                    $this.parents('tr:first').find('.action').removeClass('selected');
                    $this.toggleClass('selected');

                    var
                        singleSiteActionType = $this.data('action-type'),
                        totalSelected = $sitesListContainer.find('.action-' + singleSiteActionType + '.selected').length;

                    $allSitesOptions.find('.action.selected').removeClass('selected');

                    if (totalSelected === totalSites) {
                        $allSitesOptions.find('.action-' + singleSiteActionType).addClass('selected');

                        updatePrimaryCtaText(singleSiteActionType);
                    } else {
                        updatePrimaryCtaText('mixed');
                    }
                });

                if (isNetworkUpgradeMode || hasAnyInstall) {
                    $skipActivationButton.click(function () {
                        $delegateToSiteAdminsButton.hide();

                        $skipActivationButton.html('Skipping, please wait...');

                        pauseCtaLabelUpdate = true;

                        // Check all sites to be skipped.
                        $allSitesOptions.find('.action.action-skip').click();

                        $form.submit();

                        pauseCtaLabelUpdate = false;

                        return false;
                    });

                    $delegateToSiteAdminsButton.click(function () {
                        $delegateToSiteAdminsButton.html('Delegating, please wait...');

                        pauseCtaLabelUpdate = true;

                        /**
                         * Set to true so that the form submission handler can differentiate delegation from license
                         * activation and the proper AJAX action will be used (when delegating, the action should be
                         * `network_activate` and not `activate_license`).
                         *
                         * @author Leo Fajardo (@leorw)
                         * @since 2.3.0
                         */
                        isNetworkDelegating = true;

                        // Check all sites to be skipped.
                        $allSitesOptions.find('.action.action-delegate').click();

                        $form.submit();

                        pauseCtaLabelUpdate = false;

                        /**
                         * Set to false so that in case the previous AJAX request has failed, the form submission handler
                         * can differentiate license activation from delegation and the proper AJAX action will be used
                         * (when activating a license, the action should be `activate_license` and not `network_activate`).
                         *
                         * @author Leo Fajardo (@leorw)
                         * @since 2.3.0
                         */
                        isNetworkDelegating = false;

                        return false;
                    });
                }
            }

            /**
             * @author Leo Fajardo (@leorw)
             */
            function updatePrimaryCtaText(actionType) {
                if (pauseCtaLabelUpdate)
                    return;

                var text = 'Continue';

                switch (actionType) {
                    case 'allow':
                        text = 'Allow & Continue';
                        break;
                    case 'delegate':
                        text = 'Delegate to Site Admins & Continue';
                        break;
                    case 'skip':
                        text = 'Skip';
                        break;
                }

                $primaryCta.html(text);
            }

            var ajaxOptin = (requireLicenseKey || isNetworkActive);

            $form.on('submit', function () {
                var $extensionsPermission = $('#fs_permission_extensions .fs-switch'),
                    isExtensionsTrackingAllowed = ($extensionsPermission.length > 0) ?
                        $extensionsPermission.hasClass('fs-on') :
                        null;

                var $diagnosticPermission = $('#fs_permission_diagnostic .fs-switch'),
                    isDiagnosticTrackingAllowed = ($diagnosticPermission.length > 0) ?
                        $diagnosticPermission.hasClass('fs-on') :
                        null;

                if (null === isExtensionsTrackingAllowed) {
                    $('input[name=is_extensions_tracking_allowed]').remove();
                } else {
                    $('input[name=is_extensions_tracking_allowed]').val(isExtensionsTrackingAllowed ? 1 : 0);
                }

                // We are not showing switch to enable/disable diagnostic tracking while activating free version. So, don't remove hidden `is_diagnostic_tracking_allowed` element from DOM and change the value only if switch is available.
                if (null !== isDiagnosticTrackingAllowed) {
                    $('input[name=is_diagnostic_tracking_allowed]').val(isDiagnosticTrackingAllowed ? 1 : 0);
                }

                /**
                 * @author Vova Feldman (@svovaf)
                 * @since 1.1.9
                 */
                if (ajaxOptin) {
                    if (!hasContextUser || isNetworkUpgradeMode) {
                        var action = null,
                            security = null;

                        if (requireLicenseKey && !isNetworkDelegating) {
                            action = '<?php echo $fs->get_ajax_action('activate_license') ?>';
                            security = '<?php echo $fs->get_ajax_security('activate_license') ?>';
                        } else {
                            action = '<?php echo $fs->get_ajax_action('network_activate') ?>';
                            security = '<?php echo $fs->get_ajax_security('network_activate') ?>';
                        }

                        $('.fs-error').remove();

                        var
                            licenseKey = $licenseKeyInput.val(),
                            data = {
                                action: action,
                                security: security,
                                license_key: licenseKey,
                                module_id: '<?php echo $fs->get_id() ?>'
                            };

                        if (
                            requireLicenseKey &&
                            !isNetworkDelegating &&
                            isMarketingAllowedByLicense.hasOwnProperty(licenseKey)
                        ) {
                            var
                                isMarketingAllowed = null,
                                $isMarketingAllowed = $marketingOptin.find('input[type="radio"][name="allow-marketing"]:checked');


                            if ($isMarketingAllowed.length > 0)
                                isMarketingAllowed = ('true' == $isMarketingAllowed.val());

                            if (null == isMarketingAllowedByLicense[licenseKey] &&
                                null == isMarketingAllowed
                            ) {
                                $marketingOptin.addClass('error').show();
                                resetLoadingMode();
                                return false;
                            } else if (null == isMarketingAllowed) {
                                isMarketingAllowed = isMarketingAllowedByLicense[licenseKey];
                            }

                            data.is_marketing_allowed = isMarketingAllowed;

                            data.is_extensions_tracking_allowed = isExtensionsTrackingAllowed;

                            data.is_diagnostic_tracking_allowed = isDiagnosticTrackingAllowed;
                        }

                        $marketingOptin.removeClass('error');

                        if (isNetworkActive) {
                            var
                                sites = [],
                                applyOnAllSites = $applyOnAllSites.is(':checked');

                            $sitesListContainer.find('tr').each(function () {
                                var
                                    $this = $(this),
                                    includeSite = (!requireLicenseKey || applyOnAllSites || $this.find('input').is(':checked'));

                                if (!includeSite)
                                    return;

                                var site = {
                                    uid: $this.find('.uid').val(),
                                    url: $this.find('.url').val(),
                                    title: $this.find('.title').val(),
                                    language: $this.find('.language').val(),
                                    blog_id: $this.find('.blog-id').find('span').text()
                                };

                                if (!requireLicenseKey) {
                                    site.action = $this.find('.action.selected').data('action-type');
                                } else if (isNetworkDelegating) {
                                    site.action = 'delegate';
                                }

                                sites.push(site);
                            });

                            data.sites = sites;

                            if (hasAnyInstall) {
                                data.has_any_install = hasAnyInstall;
                            }
                        }

                        /**
                         * Use the AJAX opt-in when license key is required to potentially
                         * process the after install failure hook.
                         *
                         * @author Vova Feldman (@svovaf)
                         * @since 1.2.1.5
                         */
                        $.ajax({
                            url: <?php echo Freemius::ajax_url() ?>,
                            method: 'POST',
                            data: data,
                            success: function (result) {
                                var resultObj = $.parseJSON(result);
                                if (resultObj.success) {
                                    // Redirect to the "Account" page and sync the license.
                                    window.location.href = resultObj.next_page;
                                } else {
                                    resetLoadingMode();

                                    // Show error.
                                    $('.fs-content').prepend('<div class="fs-error">' + (resultObj.error.message ? resultObj.error.message : resultObj.error) + '</div>');
                                }
                            },
                            error: function () {
                                resetLoadingMode();
                            }
                        });

                        return false;
                    } else {
                        if (null == $licenseSecret) {
                            $licenseSecret = $('<input type="hidden" name="license_secret_key" value="" />');
                            $form.append($licenseSecret);
                        }

                        // Update secret key if premium only plugin.
                        $licenseSecret.val($licenseKeyInput.val());
                    }
                }

                return true;
            });

            $('#fs_connect .fs-permissions .fs-switch').on('click', function () {
                $(this)
                    .toggleClass('fs-on')
                    .toggleClass('fs-off');

                $(this).closest('.fs-permission')
                    .toggleClass('fs-disabled');
            });

            $primaryCta.on('click', function () {
                console.log('Primary button was clicked');

                $(this).addClass('fs-loading');
                $(this).html('<?php echo esc_js(
                    $is_pending_activation ? fs_text_x_inline(
                        'Sending email', 'as in the process of sending an email', 'sending-email', $slug
                    ) : fs_text_x_inline('Activating', 'as activating plugin', 'activating', $slug)
                ) ?>...');
            });

            $('.fs-permissions .fs-trigger').on('click', function () {
                $('.fs-permissions').toggleClass('fs-open');

                return false;
            });

            if (requireLicenseKey) {
                /**
                 * Submit license key on enter.
                 *
                 * @author Vova Feldman (@svovaf)
                 * @since 1.1.9
                 */
                $licenseKeyInput.keypress(function (e) {
                    if (e.which == 13) {
                        if ('' !== $(this).val()) {
                            $primaryCta.click();
                            return false;
                        }
                    }
                });

                /**
                 * Disable activation button when empty license key.
                 *
                 * @author Vova Feldman (@svovaf)
                 * @since 1.1.9
                 */
                $licenseKeyInput.on('keyup paste delete cut', function () {
                    setTimeout(function () {
                        var key = $licenseKeyInput.val();

                        if (key == previousLicenseKey) {
                            return;
                        }

                        if ('' === key) {
                            $primaryCta.attr('disabled', 'disabled');
                            $marketingOptin.hide();
                        } else {
                            $primaryCta.prop('disabled', false);

                            if (32 <= key.length) {
                                fetchIsMarketingAllowedFlagAndToggleOptin();
                            } else {
                                $marketingOptin.hide();
                            }
                        }

                        previousLicenseKey = key;
                    }, 100);
                }).focus();
            }

            /**
             * Set license mode trigger URL.
             *
             * @author Vova Feldman (@svovaf)
             * @since 1.1.9
             */
            var
                $connectLicenseModeTrigger = $('#fs_connect .fs-freemium-licensing a'),
                href = window.location.href;

            if (href.indexOf('?') > 0) {
                href += '&';
            } else {
                href += '?';
            }

            if ($connectLicenseModeTrigger.length > 0) {
                $connectLicenseModeTrigger.attr(
                    'href',
                    href + 'require_license=' + $connectLicenseModeTrigger.attr('data-require-license')
                );
            }

            //--------------------------------------------------------------------------------
            //region GDPR
            //--------------------------------------------------------------------------------
            var isMarketingAllowedByLicense = {},
                $marketingOptin = $('#fs_marketing_optin'),
                previousLicenseKey = null;

            if (requireLicenseKey) {

                var
                    afterMarketingFlagLoaded = function () {
                        var licenseKey = $licenseKeyInput.val();

                        if (null == isMarketingAllowedByLicense[licenseKey]) {
                            $marketingOptin.show();

                            if ($marketingOptin.find('input[type=radio]:checked').length > 0) {
                                // Focus on button if GDPR opt-in already selected is already selected.
                                $primaryCta.focus();
                            } else {
                                // Focus on the GDPR opt-in radio button.
                                $($marketingOptin.find('input[type=radio]')[0]).focus();
                            }
                        } else {
                            $marketingOptin.hide();
                            $primaryCta.focus();
                        }
                    },
                    /**
                     * @author Leo Fajardo (@leorw)
                     * @since 2.1.0
                     */
                    fetchIsMarketingAllowedFlagAndToggleOptin = function () {
                        var licenseKey = $licenseKeyInput.val();

                        if (licenseKey.length < 32) {
                            $marketingOptin.hide();
                            return;
                        }

                        if (isMarketingAllowedByLicense.hasOwnProperty(licenseKey)) {
                            afterMarketingFlagLoaded();
                            return;
                        }

                        $marketingOptin.hide();

                        setLoadingMode();

                        $primaryCta.addClass('fs-loading');
                        $primaryCta.attr('disabled', 'disabled');
                        $primaryCta.html('Please wait...');

                        $.ajax({
                            url: <?php echo Freemius::ajax_url() ?>,
                            method: 'POST',
                            data: {
                                action: '<?php echo $fs->get_ajax_action('fetch_is_marketing_required_flag_value') ?>',
                                security: '<?php echo $fs->get_ajax_security(
                                    'fetch_is_marketing_required_flag_value'
                                ) ?>',
                                license_key: licenseKey,
                                module_id: '<?php echo $fs->get_id() ?>'
                            },
                            success: function (result) {
                                resetLoadingMode();

                                if (result.success) {
                                    result = result.data;

                                    // Cache result.
                                    isMarketingAllowedByLicense[licenseKey] = result.is_marketing_allowed;
                                }

                                afterMarketingFlagLoaded();
                            }
                        });
                    };

                $marketingOptin.find('input').click(function () {
                    $marketingOptin.removeClass('error');
                });
            }

            //endregion
        })(jQuery);
    </script>
<?php
fs_require_once_template('api-connectivity-message-js.php');