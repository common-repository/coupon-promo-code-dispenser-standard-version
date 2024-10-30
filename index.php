<?php

/*
 * 	Plugin Name: Coupon & Promo Code Dispenser
 * 	Plugin URI: http://www.meamobile.com
 * 	Description: Distribute unique coupon codes. Visitors enter their email address into a form and they are emailed a unique code. Fully customizable with fraud prevention. Developed for app marketers and coupon sites. Perfect for app promo code distribution, discount codes and coupons.
 * 	Version: 1.01
 * 	Author: MEA Mobile
 * 	Author URI: http://www.meamobile.com
 * 	License: GPL2
 * Text Domain:       wp-svg-icons
 * Domain Path:       /languages
 *
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;
require 'inc/EmailSend.php';
//require 'inc/sendEmail.php';
require 'inc/dataValues.php';
define('COUPON_PLUGIN', __FILE__);
define('COUPON_PLUGIN_DIR', untrailingslashit(dirname(COUPON_PLUGIN)));
add_action('admin_menu', 'mea_coupon_plugin_pages');
register_activation_hook(__FILE__, 'mea_coupon_create_database_tables');
register_uninstall_hook(__FILE__, 'mea_coupon_delete_database_tables');

function mea_coupon_delete_database_tables() {
    if (!current_user_can('manage_options')) {
        wp_die('You do not have suggicient permissions to access this page.');
    }

    global $wpdb;
    $table_coupons = $wpdb->prefix . 'coupons';
    $table_coupon_company = $wpdb->prefix . 'coupon_company';
    $table_coupon_emails = $wpdb->prefix . 'coupon_emails';
    $table_coupon_settings = $wpdb->prefix . 'coupon_settings';

    $results = $wpdb->get_results("SELECT company_name FROM $table_coupon_company", ARRAY_A);
    foreach ($results as $name) {
        echo $name["company_name"];
        delete_option("CouponPlugin_EmailSetup_" . $name["company_name"]);
    }

    delete_option("CouponPlugin_EmailSetup");
    delete_option("CouponPluginRegistration");

    $deleteTables = "DROP TABLE $table_coupons,$table_coupon_emails,$table_coupon_settings";

    if (!$wpdb->query($deleteTables)) {
        $wpdb->show_errors();
    }
    if (!$wpdb->query("DROP TABLE $table_coupon_company")) {
        $wpdb->show_errors();
    }
}

global $coupon_db_version;

$coupon_db_version = '1.0';
$isEmailExists = FALSE;
$isOutofCoupon = FALSE;
$isOutofLimit = FALSE;
$productTitle = "";
$isEmailSuccessfullySent = TRUE;

// action function for above hook
function mea_coupon_plugin_pages() {
    // Add a new submenu under Settings:
    //add_options_page(__('Coupon Plugin', 'menu-test'), __('Coupon Plugin', 'menu-test'), 'manage_options', 'wpcoupon-plugin', 'coupon_plugin_options_page');
    //Add a new menu at end:
    add_menu_page(__('Coupon Plugin', 'menu-test'), __('Coupon Plugin', 'menu-test'), 'manage_options', 'mt-top-level-handle', 'mea_coupon_product_list_page', plugin_dir_url(__FILE__) . 'iconw.png');
    //Add a new page under menu page:
    add_submenu_page('', __('Coupon Setting', 'menu-test'), __('Coupon Settings', 'menu-test'), 'manage_options', 'setting-page', 'mea_coupon_product_settings_page');

    add_submenu_page('mt-top-level-handle', __('reCapatcha Options', 'menu-test'), __('reCaptcha Options', 'menu-test'), 'manage_options', 'recaptcha-options', 'mea_coupon_recaptcha_options_page');
}

function mea_coupon_create_database_tables() {

    if (!current_user_can('manage_options')) {
        wp_die('You do not have suggicient permissions to access this page.');
    }


    global $wpdb;
    global $coupon_db_version;

    $table_coupons = $wpdb->prefix . 'coupons';
    $table_coupon_company = $wpdb->prefix . 'coupon_company';
    $table_coupon_emails = $wpdb->prefix . 'coupon_emails';
    $table_coupon_settings = $wpdb->prefix . 'coupon_settings';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE `$table_coupon_company` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `company_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
            `created_by` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
            `short_code` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
            `added_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`company_name`),
            UNIQUE KEY `id` (`id`)
            ) ENGINE=InnoDB $charset_collate;
                
            CREATE TABLE `$table_coupons` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `codes` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
            `valid` tinyint(1) NOT NULL DEFAULT '1',
            `sent_to_email` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
            `sent_on` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
            `is_signedup` tinyint(4) DEFAULT NULL,
            `product_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
            PRIMARY KEY (`id`),
            KEY `product_name` (`product_name`),
            KEY `product_name_2` (`product_name`),
            FOREIGN KEY (`product_name`) REFERENCES `$table_coupon_company` (`company_name`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB $charset_collate; 
                
           CREATE TABLE `$table_coupon_emails` (
           `id` int(11) NOT NULL AUTO_INCREMENT,
           `default_email` text COLLATE utf8_unicode_ci NOT NULL,
           `alt_email` text COLLATE utf8_unicode_ci NOT NULL,
           `product_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
           PRIMARY KEY (`id`),
           UNIQUE KEY `product_name` (`product_name`),
           FOREIGN KEY (`product_name`) REFERENCES `$table_coupon_company` (`company_name`) ON DELETE CASCADE ON UPDATE CASCADE
           ) ENGINE=InnoDB $charset_collate;

            CREATE TABLE `$table_coupon_settings` (
             `ID` int(11) NOT NULL AUTO_INCREMENT,
             `msg_success` text COLLATE utf8_unicode_ci NOT NULL,
             `msg_nocode` text COLLATE utf8_unicode_ci NOT NULL,
             `msg_existing_email` text COLLATE utf8_unicode_ci,
             `max_perday` int(11) NOT NULL DEFAULT '1000',
             `max_perhour` int(11) NOT NULL DEFAULT '20',
             `msg_limit` text COLLATE utf8_unicode_ci NOT NULL,
             `product_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
             `email_from` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
             `newsletter_text` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
             `keep_newsletter_checkbox` tinyint(1) NOT NULL DEFAULT '1',
             `sendbutton_text` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
             PRIMARY KEY (`ID`),
             UNIQUE KEY `product_name` (`product_name`),
            FOREIGN KEY (`product_name`) REFERENCES `$table_coupon_company` (`company_name`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta($sql);

    add_option('coupon_db_version', $coupon_db_version);
}

function mea_coupon_recaptcha_options_page() {
    if (!current_user_can('manage_options')) {
        wp_die('You do not have suggicient permissions to access this page.');
    }
    require 'inc/recaptcha-options.php';
}

add_shortcode('coupon', 'mea_coupon_plugin_product_shortcode_page');

function mea_coupon_plugin_product_shortcode_page($atts) {
    ob_start();
    $atts = shortcode_atts(array(
        'title' => 'Hello',
        'n' => 1
            ), $atts);

    global $productTitle;
    global $isEmailSuccessfullySent;
    $productTitle = $atts['title'];

    wp_enqueue_script('captcha_script','https://www.google.com/recaptcha/api.js');

    $alignForm = trim(get_option('form_alignment_'.$productTitle));
    if($alignForm == "left") {
        wp_enqueue_style( 'style1', plugins_url( 'css/left-style.css' , __FILE__ ) );
    } else {
        wp_enqueue_style( 'style1', plugins_url( 'css/center-style.css' , __FILE__ ) );
    }

    global $isEmailExists;
    global $isOutofCoupon;
    global $isOutofLimit;
    global $wpdb;
    $table = $wpdb->prefix . 'coupons'; //Good practice

    $results = $wpdb->get_results("SELECT (SELECT COUNT(*) FROM $table WHERE sent_on > CURRENT_DATE AND valid = FALSE AND product_name ='$productTitle' )as perday, (SELECT COUNT(*) FROM $table WHERE DATE_ADD(sent_on, INTERVAL 1 HOUR) > NOW() AND valid = FALSE AND product_name ='$productTitle') as perhour");
    $perday = $results[0]->perday;
    $perhour = $results[0]->perhour;


    if (isset($_POST['g-recaptcha-response'])) {
        $captcha = $_POST['g-recaptcha-response'];
        $secretKey = trim(get_option('captcha_secret_key'));
        $response = json_decode(file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=".$secretKey."&response=" . $captcha . "&remoteip=" . $_SERVER['REMOTE_ADDR']), true);
        $IsHumanIdentified = $response['success'];
    }

    if (isset($_POST['email_form_submitted'])) {
        $hidden_field = esc_html($_POST['email_form_submitted']);
        if ($hidden_field == "Y") {

            $email_address = sanitize_text_field($_POST["email"]);

            if (is_email($email_address)) {
                if ($IsHumanIdentified || get_option("enable_captcha_".$productTitle) == "NO"){
                if ((getSettingDetails("max_perhour", $productTitle) == 0 && getSettingDetails("max_perday", $productTitle) == 0) || ($perhour < getSettingDetails("max_perhour", $productTitle) && $perday < getSettingDetails("max_perday", $productTitle))) {
                    $isOutofLimit = FALSE;
                    $code = getCodes($productTitle);
                    if ($isEmailExists == TRUE) {

                        $emailMessage = "Your Email Already Exist in OurDatabse. You Cant get code twise";
                    } elseif ((!empty($code) || $code != "") && $isEmailExists == FALSE) {

                        $isOutofCoupon = FALSE;
                        $emailMessage = getDefaultEmail($productTitle);
                        $emailMessage = str_replace("[code]", "<b>" . $code . "</b>", $emailMessage);
                        //$emailMessage = "Your Coupon Code is:<b> $code </b><br><br>" . $emailMessage;
                        $emailfrom = get_option("CouponPlugin_EmailSetup_" . $productTitle);

                        $isEmailSuccessfullySent = sendEmail($email_address, $emailfrom, $emailMessage, '');

                        if ($isEmailSuccessfullySent == TRUE) {

                            global $wpdb;
                            $table = $wpdb->prefix . 'coupons'; //Good practice
                            if (isset($_POST["signup"])) {
                                $updateSentCode = $wpdb->prepare("UPDATE $table SET valid = FALSE,is_signedup=TRUE,sent_to_email=%s WHERE valid=true AND product_name=%s AND codes=%s LIMIT %d", $email_address, $productTitle, $code, 1);
                                $wpdb->query($updateSentCode);
                            } else {
                                $updateSentCode = $wpdb->prepare("UPDATE $table SET valid = FALSE, is_signedup=FALSE, sent_to_email=%s WHERE valid=true AND product_name=%s AND codes=%s LIMIT %d", $email_address, $productTitle, $code, 1);
                                $wpdb->query($updateSentCode);
                            }
                        }
                    } else {
                        $isOutofCoupon = TRUE;
                        $emailMessage = getAltEmail($productTitle);
                        $emailfrom = get_option("CouponPlugin_EmailSetup_" . $productTitle);
                        $isEmailSuccessfullySent = sendEmail($email_address, $emailfrom, $emailMessage, '');
                    }
                } else {
                    $isOutofLimit = TRUE;
                }   
            }
        }
        }
    }

    require ('inc/options-page-wrapper.php');
    $output = ob_get_contents(); // end output buffering
    ob_end_clean(); // grab the buffer contents and empty the buffer
    return $output;
}

function mea_coupon_product_settings_page() {
    if (!current_user_can('manage_options')) {
        wp_die('You do not have suggicient permissions to access this page.');
    }

    wp_enqueue_script('bootstrap_script',plugins_url( 'scripts/bootstrap.min.js' , __FILE__ ));
    wp_enqueue_style( 'bootstrap_style', plugins_url( 'css/bootstrap.min.css' , __FILE__ ) );
    require 'inc/coupon-settings.php';
}

function mea_coupon_product_list_page() {
    if (!current_user_can('manage_options')) {
        wp_die('You do not have suggicient permissions to access this page.');
    }
    wp_enqueue_style( 'bootstrap_style', plugins_url( 'css/bootstrap.min.css' , __FILE__ ) );
    wp_enqueue_style( 'table_style', plugins_url( 'css/productPage.css' , __FILE__ ) );
    require 'inc/product.php';
}
?>