<?php if ( ! defined( 'ABSPATH' ) ) exit;?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Coupon Plugin</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script type="text/javascript">
        function CheckHost(val) {
            var element = document.getElementById('hostName');
            if (val == 'other')
                element.style.display = 'block';
            else
                element.style.display = 'none';
        }

        function showAddOrEditButton() {
            jQuery("#addCodes").show();
            jQuery("#editCodes").show();
            jQuery("#cancelAddProduct").hide();
            jQuery("#saveCodes").hide();
            jQuery("#modifyCodes").hide();
            jQuery('#listOfcodes').prop('disabled',true);
        }

        jQuery(document).ready(function () {
                var availableCodes = jQuery('#listOfcodes').val();
                jQuery("#cancelAddProduct").hide();
                jQuery("#saveChanges").hide();
                jQuery("#saveCodes").hide();
                jQuery("#modifyCodes").hide();
                jQuery('#csvSentCodesText').hide()

                jQuery("#editCodes").click(function () {

                    jQuery('#listOfcodes').prop('disabled',false);
                    jQuery(this).hide()
                    jQuery("#addCodes").hide();
                    jQuery("#cancelAddProduct").show();
                    jQuery("#modifyCodes").show();
                });

                jQuery("#addCodes").click(function () {
                    jQuery("#cancelAddProduct").show();
                    jQuery("#editCodes").hide();
                    jQuery(this).hide()
                    jQuery("#saveCodes").show();
                    jQuery('#listOfcodes').prop('disabled',false);
                    jQuery('#listOfcodes').val("");
                });
                jQuery("#cancelAddProduct").click(function () {
                    showAddOrEditButton();
                    jQuery('#listOfcodes').val(availableCodes);
                });

                jQuery("#codeTab").click(function () {
                    jQuery("#saveChanges").hide();
                });

                jQuery("#emailTab, #altemailTab, #settingsTab, #emailsetupTab").click(function () {
                    jQuery("#saveChanges").show();
                    showAddOrEditButton();
                    jQuery('#listOfcodes').val(availableCodes);
                });

                jQuery("#sentcodeTab, #sendBulkEmailTab").click(function () {
                    showAddOrEditButton();
                    jQuery('#listOfcodes').val(availableCodes);
                    jQuery("#saveChanges").hide();
                });

                jQuery("#tableView").click(function () {
                    jQuery('.editable').show();
                    jQuery('#csvSentCodesText').hide();
                });

                jQuery("#csvView").click(function () {
                   jQuery('.editable').hide();
                   jQuery('#csvSentCodesText').show(); 
                });


            });
</script> 
<style>
    /* styling for the footer */
    #wpwrap{
        background-color: white;
    }
    table, th, td {
        /*border: solid;*/
        border: 1px solid black;
        border-collapse: collapse;

    }
    td {
        padding: 2px;
        text-align: left;
    }
    th{
        padding: 5px;
        text-align: center;
    }
    div.editable {
        width: 1100px;
        height: 400px;
        border: 1px solid #ccc;
        padding: 5px;
        overflow:auto;

    }
</style>

</head>
<body>


    <?php
    if (!current_user_can('manage_options')) {
        wp_die('You do not have suggicient permissions to access this page.');
    }

    if (isset($_POST['saveCodes'])){
        $product = sanitize_text_field($_GET['name']);
        $codes = sanitize_text_field($_POST['listOfcodes']);
        global $wpdb;

            $codeTable = $wpdb->prefix . 'coupons'; //Good practice
            $newcodes = preg_split("/[\s,]+/", $codes);
            $numberOfCodes = count($newcodes);
            $totalAvailableCode = getNumberOfAvailableCodes($product);
            if ($totalAvailableCode < 50){

                if (($numberOfCodes + $totalAvailableCode) > 50) {
                    ?>
                    <div class="error"><p><strong><?php _e('Standard Version can only include 50 codes. Please upgrade to Pro Version to add more codes.', 'menu-test'); ?></strong></p></div>
                    <?php
                }

                $i = 1;
                $numerOfNewCodesToAdd = 50 - $totalAvailableCode;
                foreach ($newcodes as $code) {
                    if($code != "" || !empty($code)){
                    if ($i <=  $numerOfNewCodesToAdd){
                        if (!$wpdb->insert($codeTable, array('codes' => $code, 'product_name' => $product))) {
                            $wpdb->show_errors();
                        } else {
                            $i++;
                        } 
                    }
                    }
                }
            } else {
                ?>
                    <div class="error"><p><strong><?php _e('Standard Version can only include 50 codes. Please upgrade to Pro Version to add more codes.', 'menu-test'); ?></strong></p></div>
                    <?php
            }
        }

        if (isset($_POST['modifyCodes'])){
            $product = sanitize_text_field($_GET['name']);
            $codes = sanitize_text_field($_POST['listOfcodes']);
            global $wpdb;

            $codeTable = $wpdb->prefix . 'coupons'; //Good practice
            $newcodes = preg_split("/[\s,]+/", $codes);
            $numberOfCodes = count($newcodes);

            if ($numberOfCodes > 50) {
                ?>
                <div class="error"><p><strong><?php _e('Standard Version can only include 50 codes. Please upgrade to Pro Version to add more codes.', 'menu-test'); ?></strong></p></div>
                <?php
            }
            $i = 1;
            $wpdb->query($wpdb->prepare("DELETE FROM $codeTable WHERE valid=%d AND product_name='$product' ", 1));
            foreach ($newcodes as $code) {
                if($code != "" || !empty($code)){
                if ($i <= 50){
                    if (!$wpdb->insert($codeTable, array('codes' => $code, 'product_name' => $product))) {
                        $wpdb->show_errors();
                    } else {
                        $i++;
                    } 
                }
            }
        }
        }

        
        if (isset($_POST['Submit']) || isset($_POST['testEmail'])) {
            if (isset($_POST['hidden']) && $_POST['hidden'] == 'Y') {
                $product = sanitize_text_field($_GET['name']);

                if (isset($product) && $product !== "") {


                    global $wpdb;

                    
                    $msgsuccess = sanitize_text_field($_POST["msgsuccess"]);
                    $msgnocode = sanitize_text_field($_POST["msgnocode"]);
                    $msgexistingemail = sanitize_text_field($_POST["msgexistingemail"]);
                    $msglimit = sanitize_text_field($_POST["msglimit"]);
                    
                    

                    if(!is_numeric(sanitize_text_field($_POST["perday"]))) {
                        ?>
                        <div class="error"><p><strong><?php _e('Invalid value for \'Per Day\' limit.', 'menu-test'); ?></strong></p></div>
                        <?php
                    } else {
                        $maxperday = intval(sanitize_text_field($_POST["perday"]));
                    }

                    if(!is_numeric(sanitize_text_field($_POST["perhour"]))) {
                        ?>
                        <div class="error"><p><strong><?php _e('Invalid value for \'Per Hour\' limit.', 'menu-test'); ?></strong></p></div>
                        <?php
                    } else {
                        $maxperhour = intval(sanitize_text_field($_POST["perhour"]));
                    }

                    $newsletterText = sanitize_text_field($_POST["newsletterText"]);
                    $sendButtonText = sanitize_text_field($_POST["sendButtonText"]);

                    $email = stripslashes($_POST["email"]);
                    $altemail = stripslashes($_POST['altemail']);

                    if (isset($_POST["newsletterCheckbox"])) {
                        $keepNewsletterBox = 1;
                    } else {
                        $keepNewsletterBox = 0;
                    }


                    $hostselected = sanitize_text_field($_POST["hostselected"]);
                    $hostname = sanitize_text_field($_POST["hostname"]);

                    if ($hostselected == 'other') {
                        $host = sanitize_text_field($_POST["hostname"]);
                    } else {
                        $host = sanitize_text_field($_POST["hostselected"]);
                    }

                    $emailfrom = sanitize_email($_POST["emailfrom"]);
                    if(empty($emailfrom) || !is_email($emailfrom)) {
                        ?>
                        <div class="error"><p><strong><?php _e('Invalid email address.', 'menu-test'); ?></strong></p></div>
                        <?php
                    }


                    $password = trim($_POST["password"]);
                    // $portno = intval(sanitize_text_field($_POST["portno"]));
                    if(!is_numeric(sanitize_text_field($_POST["portno"]))) {
                        ?>
                        <div class="error"><p><strong><?php _e('Invalid value for \'Port no\'.', 'menu-test'); ?></strong></p></div>
                        <?php
                    } else {
                        $portno = intval(sanitize_text_field($_POST["portno"]));
                    }

                    $encryption = sanitize_text_field($_POST["encryption"]);
                    $emailfromname = sanitize_text_field($_POST["emailfromname"]);
                    if (!ctype_alpha(str_replace(' ', '',$emailfromname))) {
                        ?>
                        <div class="error"><p><strong><?php _e('Invalid email from name.', 'menu-test'); ?></strong></p></div>
                        <?php
                    }


                    $emailSubject = sanitize_text_field($_POST["emailsubject"]);

                    if (empty($host) || empty($emailfrom) || empty($password) || empty($portno) || empty($emailfromname) || empty($emailSubject)) {
                        ?>
                        <div class="error"><p><strong><?php _e('Please enter all email setup information.', 'menu-test'); ?></strong></p></div>
                        <?php
                    }

                    $optionValue = $host . "$$" . $emailfrom . "$$" . $password . "$$" . $portno . "$$" . $encryption . "$$" . $emailfromname . "$$" . $emailSubject;

                    if (!get_option("CouponPlugin_EmailSetup_" . $product)) {
                        if (!get_option("CouponPlugin_EmailSetup")) {
                            add_option("CouponPlugin_EmailSetup", $optionValue);
                        } else {
                            update_option("CouponPlugin_EmailSetup", $optionValue);
                        }

                        add_option("CouponPlugin_EmailSetup_" . $product, $optionValue);
                    } else {
                        update_option("CouponPlugin_EmailSetup", $optionValue);
                        update_option("CouponPlugin_EmailSetup_" . $product, $optionValue);
                    }


                    if (isset($_POST["enable_captcha"])) {
                        if (!get_option("enable_captcha_" . $product)) {
                            add_option("enable_captcha_" . $product, "YES");
                        } else {
                            update_option("enable_captcha_" . $product, "YES");
                        }
                    } else {
                        if (!get_option("enable_captcha_" . $product)) {
                            add_option("enable_captcha_" . $product, "NO");
                        } else {
                            update_option("enable_captcha_" . $product, "NO");
                        }
                    }

                    $alignForm = $_POST["alignForm"];
                    if(!get_option("form_alignment_".$product)){
                        add_option("form_alignment_" . $product, "$alignForm");
                    } else {
                        update_option("form_alignment_" . $product, "$alignForm");
                    }

                    // $codeTable = $wpdb->prefix . 'coupons'; //Good practice
                    // $newcodes = preg_split("/[\s,]+/", $codes);
                    // $wpdb->query($wpdb->prepare("DELETE FROM $codeTable WHERE valid=%d AND product_name='$product' ", 1));
                    // foreach ($newcodes as $code) {
                    //     if (!$wpdb->insert($codeTable, array('codes' => $code, 'product_name' => $product))) {
                    //         $wpdb->show_errors();
                    //     }
                    // }


                    $emailTable = $wpdb->prefix . 'coupon_emails'; //Good practice
                    $results = $wpdb->get_results("SELECT * FROM $emailTable WHERE product_name='$product'");
                    $NumRows = count((array) $results);

                    if ($NumRows > 0) {
                        $wpdb->query($wpdb->prepare("UPDATE $emailTable SET default_email = %s , alt_email = %s WHERE product_name='$product'", $email, $altemail));
                    } else {
                        $wpdb->insert($emailTable, array('default_email' => $email, 'alt_email' => $altemail, 'product_name' => $product));
                    }


                    $settingTable = $wpdb->prefix . 'coupon_settings';
                    $results = $wpdb->get_results("SELECT * FROM $settingTable WHERE product_name='$product'");
                    $NumRows = count((array) $results);
                    if ($NumRows > 0) {
                        $wpdb->query($wpdb->prepare("UPDATE $settingTable SET email_from = %s, msg_success = %s , msg_nocode = %s, msg_existing_email = %s, msg_limit = %s, max_perday = %d, max_perhour = %d, newsletter_text = %s, keep_newsletter_checkbox = %d, sendbutton_text = %s WHERE product_name='$product'", $emailfrom, $msgsuccess, $msgnocode, $msgexistingemail, $msglimit, $maxperday, $maxperhour, $newsletterText, $keepNewsletterBox, $sendButtonText));
                    } else {
                        $wpdb->insert($settingTable, array('email_from' => $emailfrom, 'msg_success' => $msgsuccess, 'msg_nocode' => $msgnocode, 'msg_existing_email' => $msgexistingemail, 'msg_limit' => $msglimit, 'max_perday' => $maxperday, 'max_perhour' => $maxperhour, 'newsletter_text' => $newsletterText, 'keep_newsletter_checkbox' => $keepNewsletterBox, 'sendbutton_text' => $sendButtonText, 'product_name' => $product));
                    }
                    ?>

                    <div class="updated"><p><strong><?php _e('Settings saved.', 'menu-test'); ?></strong></p></div>
                    <?php
                }
            }
        }

        if (isset($_POST['testEmail'])) {

            $emailto = sanitize_email($_POST['testemail']);
            $emailMessage = "If you are reading this email your email settings are working.";
            if(empty($emailto) || !is_email($emailto)){
                ?>
                <div class="error"><p><strong><?php _e('Invalid email address. Please check email address to send test email.', 'menu-test'); ?></strong></p></div>
                <?php

            } else {
                $emailSetupFrom = get_option("CouponPlugin_EmailSetup_" . $product);
                sendEmail($emailto, $emailSetupFrom, $emailMessage, 'Coupon Plugin Test Email');
            }
        }
        ?>

        <div class="container">
        <?php $productName = sanitize_text_field($_GET['name'])?>
            <h2> <?php echo __('Coupon Plugin Settings: '. $productName, 'menu-test'); ?> </h2>

            <ul class="nav nav-pills">
                <li class="active"><a data-toggle="pill" id="codeTab" href="#code">Codes</a></li>
                <li><a data-toggle="pill" id="sentcodeTab" href="#sentcode">Sent Codes</a></li>
                <li><a data-toggle="pill" id="emailTab" href="#email">Email</a></li>
                <li><a data-toggle="pill" id="altemailTab" href="#altemail">Alt Email</a></li>
                <li><a data-toggle="pill" id="settingsTab" href="#settings">Options</a></li>
                <li><a data-toggle="pill" id="emailsetupTab" href="#emailsetup">Email Setup</a></li>
            </ul>


            <form name="form1" method="post" action="">
                <input type="hidden" name="hidden" value="Y">    
                <div class="tab-content">
                    <div id="code" class="tab-pane fade in active">

                        <h3>Current Codes
                            <!-- <input type="button" name="currentCodes" id="currentCodes" class="button-primary" value="<?php esc_attr_e('Current') ?>" /> -->
                            <input type="button" name="addCodes" id="addCodes" class="button-primary" value="<?php esc_attr_e('Add') ?>" />
                            <input type="button" name="editCodes" id="editCodes" class="button-primary" value="<?php esc_attr_e('Edit') ?>" />    
                            <input type="button" name="cancelAddProduct" id="cancelAddProduct" class="button-primary" value="<?php esc_attr_e('Cancel') ?>" />    
                        </h3> 
                        <h4>(Enter one code per line or separate codes with a space.)</h4>
                        <textarea id="listOfcodes" name="listOfcodes" cols="80" rows="20" style="color: black" class="large-text" disabled><?php getAvailableCodes($productName); ?></textarea><br>

                    </div>

                    <div id="sentcode" class="tab-pane fade">
                        
                        <h3>List of sent codes
                            <!-- <input type="submit" name="export" class="button-primary" value="<?php esc_attr_e('Export') ?>" /> -->
                            <input type="button" name="tableView" id="tableView" class="button-primary" value="<?php esc_attr_e('Table Format') ?>" />
                            <input type="button" name="csvView" id="csvView" class="button-primary" value="<?php esc_attr_e('CSV Format') ?>" />    
                        </h3>
                        <h4>Sent: <?php echo getNumberOfSentCodes($productName); ?> Available: <?php echo getNumberOfAvailableCodes($productName); ?></h4>
                        <textarea id="csvSentCodesText" name="csvSentCodesText" cols="80" rows="20" style="color: black" class="large-text" disabled><?php getCSVFormattedData($productName); ?></textarea><br>
                        <div class="editable" >
                            <table>
                                <thead>
                                    <tr>
                                        <th>Code</th>
                                        <th>Email Address</th>
                                        <th> Sent On</th>
                                        <th> Signed Up for Updates</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php getSentCodes($productName); ?>
                                </tbody>
                            </table>
                        </div>

                    </div>

                    <div id="email" class="tab-pane fade">
                        <h3>Email to be sent with code.</h3>
                        <h4>(Enter in [code] where you would like the code inserted.)</h4>
                        <textarea id="email" name="email" cols="80" rows="20" class="large-text"><?php
                            $emailmsg = getDefaultEmail($productName);
                            if (empty($emailmsg) || $emailmsg == "") {
                                echo "This is sample content for an email which will contain a unique coupon code or unique promotional code. Replace the text in this form to customize your response. Your coupon code is: [code] which expires on 1/1/2020.<br><br>\n";
                                echo "\nThis plugin was developed by MEA Mobile, a contract software developer with offices in New Haven, Connecticut and New Zealand. We use this plugin ourselves to provide promo codes for our apps, client apps as well as to distribute coupon codes for other products.<br><br>\n";
                                echo "\nIf you're an app marketer you may also like our Zebroute system for routing mobile device traffic. www.zebroute.com<br><br>\n";
                                echo "\nComments, feedback and suggestions for improvement can be sent to info@meamobile.com. Thank you for choosing an MEA Mobile product!<br><br>\n";
                                echo "\nMEA Mobile<br>\nwww.meamobile.com<br>\n59 Elm Street<br>\nSecond Floor<br>\nNew Haven, CT 06510<br>\n(203) 599-1111<br>\ninfo@meamobile.com";
                            } else {
                                print $emailmsg;
                            }
                            ?></textarea><br>
                        </div>
                        <div id="altemail" class="tab-pane fade">
                            <h3>Alternate Emails are sent if no codes are available.</h3>
                            <textarea id="altemail" name="altemail" cols="80" rows="20" class="large-text"><?php
                                $altemailmsg = getAltEmail($productName);
                                if (empty($altemailmsg) || $altemailmsg == "") {
                                    echo "This is sample content for an email which is sent if no more codes are available to be sent or one of the 'Limits' has been exceeded. The 'Limits' are configurable in the 'Additional Options' menu.<br><br>\n";
                                    echo "\nComments, feedback and suggestions for improvement can be sent to info@meamobile.com. Thank you for choosing an MEA Mobile product!<br><br>\n";
                                    echo "\nDeveloped By:<br>\nMEA Mobile<br>\nwww.meamobile.com";
                                } else {
                                    print $altemailmsg;
                                }
                                ?></textarea><br>
                            </div>
                            <div id="settings" class="tab-pane fade">
                                <div id="col-container">
                                    <h3>Additional Settings</h3>
                                    <div id="col-right">

                                        <h5>Text for send button:</h5>
                                        <input type="text" name="sendButtonText" class="regular-text" value="<?php
                                        $sendButtonText = getSettingDetails("sendbutton_text", $productName);
                                        if (empty($sendButtonText)) {
                                            print "Send me a code";
                                        } else {
                                            print $sendButtonText;
                                        }
                                        ?>"><br><br>

                                        <h5>Text for Opt-In checkbox:</h5>
                                        <input type="text" name="newsletterText" class="regular-text" value="<?php
                                        $newsletterText = getSettingDetails("newsletter_text", $productName);
                                        if (empty($newsletterText)) {
                                            print "Sign Up For Newsletter";
                                        } else {
                                            print $newsletterText;
                                        }
                                        ?>"><br><br>
                                        <?php
                                        $keepNewsletterBox = getSettingDetails('keep_newsletter_checkbox', $productName);
                                        if ($keepNewsletterBox):
                                         ?>
                                     <input type="checkbox" name="newsletterCheckbox" id="newsletterCheckbox" checked><label for="newsletterCheckbox"> &nbsp; Enable Opt-In Checkbox.</label><br>
                                 <?php else: ?>
                                    <input type="checkbox" name="newsletterCheckbox" id="newsletterCheckbox"><label for="newsletterCheckbox"> &nbsp; Enable Opt-In Checkbox.</label><br>
                                <?php endif; ?>
                                <br>
                                <?php if (get_option("enable_captcha_" . $productName) == "YES"): ?>
                                    <input type="checkbox" name="enable_captcha" id="enable_captcha" checked><label for="enable_captcha"> &nbsp; Enable reCaptcha</label>
                                <?php else: ?>
                                    <input type="checkbox" name="enable_captcha" id="enable_captcha"><label for="enable_captcha"> &nbsp; Enable reCaptcha</label>
                                <?php endif; ?>
                                <br><br>
                                <h5>Form alignment:</h5>
                                <?php if(get_option("form_alignment_".$productName) == "left"): ?>
                                    <input type="radio" name="alignForm" value="left" checked> Left align &nbsp &nbsp
                                    <input type = "radio" name="alignForm" value="center"> Center align &nbsp &nbsp
                                <?php else:?>
                                    <input type="radio" name="alignForm" value="left"> Left align &nbsp &nbsp
                                    <input type = "radio" name="alignForm" value="center" checked> Center align &nbsp &nbsp
                                <?php endif; ?>


                            </div>
                            <div id="col-left">
                                <!--<h3>Additional Settings</h3>-->
                                <h5>Email address to send a email from:</h5>
                                <input type="text" name="" style="color: black" class="regular-text" value="<?php
                                $fromEmail = getSettingDetails("email_from", $productName);
                                if (empty($fromEmail)) {
                                    print "example@yourcompany.com";
                                } else {
                                    print $fromEmail;
                                }
                                ?>" disabled/><br><br>

                                <h5>Message for successfully sent code:</h5>
                                <input type="text" name="msgsuccess" class="regular-text" value="<?php
                                $successMsg = getSettingDetails("msg_success", $productName);
                                if (empty($successMsg)) {
                                    print "Code successfully sent.";
                                } else {
                                    print $successMsg;
                                }
                                ?>"/><br><br>

                                <h5>Message if no more codes available:</h5>
                                <input type="text" name="msgnocode" class="regular-text" value="<?php
                                $nocodeMsg = getSettingDetails("msg_nocode", $productName);
                                if (empty($nocodeMsg)) {
                                    print "Sorry! No more codes are available at this time.";
                                } else {
                                    print $nocodeMsg;
                                }
                                ?>"/><br><br>

                                <h5>Message for duplicate email address:</h5>
                                <input type="text" name="msgexistingemail" class="regular-text" value="<?php
                                $existingEmailMsg = getSettingDetails("msg_existing_email", $productName);
                                if (empty($existingEmailMsg)) {
                                    print "Sorry! Limit one code per address.";
                                } else {
                                    print $existingEmailMsg;
                                }
                                ?>"/><br><br>

                                <h5>Message for daily/hourly limit reached:</h5>
                                <input type="text" name="msglimit" class="regular-text" value="<?php
                                $limitMsg = getSettingDetails("msg_limit", $productName);
                                if (empty($limitMsg)) {
                                    print "Sorry! No more codes are available at this time.";
                                } else {
                                    print $limitMsg;
                                }
                                ?>"/><br><br>
                                <table>
                                    <tr>
                                        <th>
                                            <label for="perday">Per Day:</label>
                                        </th>
                                        <td>
                                            <input type="number" name="perday" min="0" style="width: 80px" value="<?php print getSettingDetails("max_perday", $productName); ?>"/><br>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>
                                            <label for="perhour">Per Hour: </label>
                                        </th>
                                        <td>
                                            <input type="number" name="perhour" min="0" style="width: 80px" value="<?php print getSettingDetails("max_perhour", $productName); ?>"/> <br>
                                        </td>
                                    </tr>
                                </table>
                                <p>Note: 0 = Unlimited</p>
                            </div>
                        </div>
                    </div>

                    <div id="emailsetup" class="tab-pane fade">
                        <div id="col-container">
                            <div id="col-right">

                                <div class="col-wrap">
                                    <br><br><br>
                                    <?php esc_attr_e('', 'wp_admin_style'); ?>
                                    <div class="inside">
                                        <p><?php esc_attr_e('Important: You may receive an alert from your email provider reporting, "Sign-in attempt prevented" or another security alert. This is because your site is attempting to connect to your email servers using the information you provided. Absolutely no information is being transmitted to third parties or the developer. WE RESPECT YOUR PRIVACY. This plugin does not contain analytics or data collection code. We do encourage you to register for free because we can alert you to updates and new features.', 'wp_admin_style'); ?></p>
                                    </div>
                                </div>
                                <!-- /col-wrap -->

                            </div>
                            <div id="col-left">
                                <h3>Email Settings</h3>
                                <?php
                                $EmailSetup = get_option("CouponPlugin_EmailSetup_" . $productName);
                                $values = explode("$$", $EmailSetup);
                                $hostName = $values[0];
                                $emailFrom = $values[1];
                                $emailPass = $values[2];
                                $portNo = $values[3];
                                $encryption = $values[4];
                                $emailfromname = $values[5];
                                $emailSubject = $values[6];
                                ?>
                                <h5>Host name:</h5>
                                <!--<input type="text" name="hostName" class="regular-text" value="<?php ?>"/><br><br>-->
                                <select name="hostselected" onchange='CheckHost(this.value);'>
                                    <?php if (!empty($hostName)): ?>
                                        <option value="<?php echo $hostName; ?>"><?php echo $hostName; ?></option>
                                    <?php endif; ?>
                                    <option value="smtp.mail.yahoo.com">smtp.mail.yahoo.com</option>
                                    <option value="smtp.office365.com">smtp.office365.com</option>
                                    <option value="smtp.aol.com">smtp.aol.com</option>
                                    <option value="other">other</option>
                                </select>

                                <input type="text" name="hostname" id="hostName"  class="regular-text" style='display:none;'/>

                                <h5>Email from name:</h5>
                                <input type="text" name="emailfromname" class="regular-text" value="<?php
                                if (empty($emailfromname) || $emailfromname == "") {
                                    echo "First Last";
                                } else {
                                    echo $emailfromname;
                                }
                                ?>"/><br><br>

                                <h5>Email subject:</h5>
                                <input type="text" name="emailsubject" class="regular-text" value="<?php echo $emailSubject; ?>"/><br><br>

                                <h5>Email address:</h5>
                                <input type="text" name="emailfrom" class="regular-text" value="<?php echo $emailFrom; ?>"/><br><br>

                                <h5>Password:</h5>
                                <input type="password" name="password" class="regular-text" value="<?php echo $emailPass; ?>"/><br><br>

                                <h5>Port no: <br>(Common ports for TLS are 587 and SSL are 25, 465, 993)</h5>
                                <input type="text" name="portno" class="regular-text" value="<?php
                                if (!empty($portNo)) {
                                    echo $portNo;
                                } else {
                                    echo 587;
                                }
                                ?>"/><br><br>

                                <h5>Encryption type:</h5>
                                <select name="encryption">
                                    <?php if ($encryption == "ssl"): ?>
                                        <option selected="selected" value="ssl">SSL</option>
                                        <option value="tls">TLS</option>
                                    <?php else: ?>
                                        <option selected="selected" value="tls">TLS</option>
                                        <option value="ssl">SSL</option>
                                    <?php endif; ?>
                                </select>
                                <br><br>

                                <p><input type="text" name="testemail" class="regular-text" value="<?php ?>"/></p>
                            </div>
                            <input type="submit" name="testEmail" class="button-secondary" value="<?php esc_attr_e('Send test email') ?>" />

                        </div>
                    </div>

                <p class="submit">
                    <input type="submit" name="Submit" id="saveChanges" class="button-primary" value="<?php esc_attr_e('Save Changes') ?>" onclick="if (!confirm('Do you want to save changes?')) {
                    return false;
                }" />

                <input type="submit" name="saveCodes" id="saveCodes" class="button-primary" value="<?php esc_attr_e('Add Codes') ?>" onclick="if (!confirm('Do you want to save changes?')) {
                return false;
            }" />

            <input type="submit" name="modifyCodes" id="modifyCodes" class="button-primary" value="<?php esc_attr_e('Update Codes') ?>" onclick="if (!confirm('Do you want to save changes?')) {
            return false;
        }" />
    </p>

</div>

</form>

</div>

</body>
</html>
