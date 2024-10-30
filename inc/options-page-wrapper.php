<?php
if ( ! defined( 'ABSPATH' ) ) exit; 
global $productTitle;
$newsletterText = getSettingDetails("newsletter_text", $productTitle);
$sendButtonText = getSettingDetails("sendbutton_text", $productTitle);
$siteKey = trim(get_option('captcha_site_key'));
$alignForm = trim(get_option('form_alignment_'.$productTitle));
?>
<?php if (!isset($email_address) || $email_address == ""): ?>

    <form id="coupon-plugin"  name="email_form" method="post" action="">
        <p>
            <input type="hidden" name="email_form_submitted" value="Y">
        </p>

        <h4 style="display: inline-block"><label for="email" class="emailLabel">Enter your E-mail address</label><br>
            <input class="regular-text" type="email" name="email" id="email" style="width: 200px"><br></h4>
            <?php
            $keepNewsletterBox = getSettingDetails('keep_newsletter_checkbox', $productTitle);
            if ($keepNewsletterBox):
                ?>
            <br><input type="checkbox" name="signup" id="signup"><label for="signup"><?php echo $newsletterText; ?></label><br>
        <?php endif; ?>

        <?php if (get_option("enable_captcha_" . $productTitle) == "YES"): ?>   

            <div class="g-recaptcha" data-sitekey="<?php echo $siteKey ?>"></div>

        <?php endif; ?>
        <input class="button-primary free-prints" type="submit" name="email_submit" value="<?php echo $sendButtonText; ?>" />
    </form>

<?php elseif (isset($email_address) && !(is_email($email_address))): ?>
    <form id="coupon-plugin"  name="email_form" method="post" action="">
        <p>
            <input type="hidden" name="email_form_submitted" value="Y">
        </p>


        <h4 style="display: inline-block"><label for="email" class="emailLabel">Enter your E-mail address</label><br></h4>
        <input class="regular-text" type="email" name="email" id="email" value="<?php echo $email_address ?>">
        <font color="red">Email address is not valid</font>
        <br>
        <?php
        $keepNewsletterBox = getSettingDetails('keep_newsletter_checkbox', $productTitle);
        if ($keepNewsletterBox):
            ?>
        <?php if (isset($_POST["signup"])): ?>
            <br><input type="checkbox" name="signup" id="signup" checked><label for="signup"><?php echo $newsletterText; ?></label><br>
        <?php else: ?>
            <br><input type="checkbox" name="signup" id="signup"><label for="signup"><?php echo $newsletterText; ?></label><br>
        <?php endif; ?>
    <?php endif; ?>

    <?php if (get_option("enable_captcha_" . $productTitle) == "YES"): ?>   
        <div class="g-recaptcha"  data-sitekey="<?php echo $siteKey ?>"></div>
    <?php endif; ?>
    <input class="button-primary free-prints" type="submit" name="email_submit" value="<?php echo $sendButtonText; ?>" />
</form>

<?php elseif (get_option("enable_captcha_" . $productTitle) == "YES" && !$IsHumanIdentified): ?>
    <form id="coupon-plugin"  name="email_form" method="post" action="">
        <p>
            <input type="hidden" name="email_form_submitted" value="Y">
        </p>


        <h4 style="display: inline-block"><label for="email" class="emailLabel">Enter your E-mail address</label><br></h4>
        <input class="regular-text" type="email" name="email" id="email" value="<?php echo $email_address ?>">
        <br>
        <?php
        $keepNewsletterBox = getSettingDetails('keep_newsletter_checkbox', $productTitle);
        if ($keepNewsletterBox):
            ?>
        <?php if (isset($_POST["signup"])): ?>
            <br><input type="checkbox" name="signup" id="signup" checked><label for="signup"><?php echo $newsletterText; ?></label><br>
        <?php else: ?>
            <br><input type="checkbox" name="signup" id="signup"><label for="signup"><?php echo $newsletterText; ?></label><br>
        <?php endif; ?>
    <?php endif; ?>
    <div class="g-recaptcha" data-sitekey="<?php echo $siteKey ?>"></div>
    <p><font color="red">reCaptcha could not be verified</font></p>
    <input class="button-primary free-prints" type="submit" name="email_submit" value="<?php echo $sendButtonText; ?>" />
</form>


<?php else: ?>
    <div class="inside">
        <form id="coupon-plugin" >

            <?php
            global $productTitle;
            global $isEmailSuccessfullySent;
            if ($isEmailSuccessfullySent == FALSE):
                ?>
            <h3 style="color: red">Email Could not be sent.</h3>
        <?php elseif ($isOutofLimit == TRUE): ?>
            <h3><?php print getSettingDetails("msg_limit", $productTitle); ?></h3>
        <?php elseif ($isEmailExists == true): ?>
            <h3><?php print getSettingDetails("msg_existing_email", $productTitle); ?></h3>
        <?php elseif ($isOutofCoupon == TRUE): ?>
            <h3><?php print getSettingDetails("msg_nocode", $productTitle); ?></h3>
        <?php else: ?>
            <h3><?php print getSettingDetails("msg_success", $productTitle); ?></h3>
        <?php endif; ?>
        <br>
        <br>
        <br>

    </form>
</div>
<?php endif; ?>