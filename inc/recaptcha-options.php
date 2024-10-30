<?php
if ( ! defined( 'ABSPATH' ) ) exit; 

if (!current_user_can('manage_options')) {
        wp_die('You do not have suggicient permissions to access this page.');
    }
    
if (isset($_POST["submit"])) {

    $siteKey = sanitize_text_field($_POST["captcha_site_key"]);
    $secretKey = sanitize_text_field($_POST["captcha_secret_key"]);


    if (!empty($siteKey) && !empty($secretKey)) {

        if (!get_option("captcha_site_key")) {
            add_option("captcha_site_key", $siteKey);
        } else {
            update_option("captcha_site_key", $siteKey);
        }
        if (!get_option("captcha_secret_key")) {
            add_option("captcha_secret_key", $secretKey);
        } else {
            update_option("captcha_secret_key", $secretKey);
        }
    } else {
        ?>
        <div class="error"><p><strong><?php _e('Please enter required details', 'menu-test'); ?></strong></p></div>
        <?php
    }
}
?>


<h1> <?php echo __('reCaptcha Options', 'menu-test'); ?></h1>
<!--<h2> <?php echo __('Keys', 'menu-test'); ?></h2>-->
<p><a href="https://www.google.com/recaptcha/admin" rel="external" target="_blank">Register your domain</a> with Google to get authentication keys and Enable reCaptcha from product settings.</p>
<p>Enter the key details:</p>

<form action="" method="post">
    <table style="text-align: left">
        <tr>
            <th>
                <label for="captcha_site_key">Site Key:</label>
            </th>
            <td>
                <input type="text" name="captcha_site_key" id="captcha_site_key" class="regular-text" value="<?php echo get_option('captcha_site_key'); ?>" />
            </td>
        </tr>
        <tr>
            <th>
                <label for="captcha_secret_key">Secret Key:</label>
            </th>
            <td>
                <input type="text" name="captcha_secret_key" id="captcha_secret_key" class="regular-text" value="<?php echo get_option('captcha_secret_key'); ?>" />
            </td>
        </tr>
<!--        <tr>
            <th>
                <br>
                <?php if (get_option("enable_google_captcha") == "YES"): ?>
                    <input type="checkbox" name="enable_google_captcha" id="enable_google_captcha" checked><label for="enable_google_captcha">Enable reCaptcha</label>
                <?php else: ?>
                    <input type="checkbox" name="enable_google_captcha" id="enable_google_captcha"><label for="enable_google_captcha">Enable reCaptcha</label>
                <?php endif; ?>
            </th>
        </tr>-->
    </table>
    <br>
    <input type="submit" name="submit" class="button-primary" value="Save Changes">
</form>