<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Coupon Plugin</title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        
        <script>
            jQuery(document).ready(function () {

                jQuery('#selectAll').click(function (e) {
                    jQuery(this).closest('table').find('td input:checkbox').prop('checked', this.checked);

                });

                jQuery("#newproduct").hide();
                jQuery("#addproduct").click(function () {

                    jQuery("#newproduct").show();
                    //document.getElementById("newproduct").style.display="block";
                    jQuery("#addproduct").hide();
                });
                jQuery("#cancel").click(function () {
                    jQuery("#newproduct").hide();
                    jQuery("#addproduct").show();
                });
            });
        </script>
    </head>
    <body>

        <?php
        if ( ! defined( 'ABSPATH' ) ) exit;
        if (!current_user_can('manage_options')) {
        wp_die('You do not have suggicient permissions to access this page.');
    } 
        if (isset($_POST['addnewdata'])) {
            if (isset($_POST['hidden']) && $_POST['hidden'] == 'Y') {
                if (isset($_POST['name']) && $_POST['name'] != "") {
                    $current_user = wp_get_current_user();
                    $productName = sanitize_text_field($_POST['name']);
                    $productAuthor = $current_user->display_name;
                    $shortCode = '[coupon title="' . $productName . '"]';
                    global $wpdb;
                    $table = $wpdb->prefix . 'coupon_company';
                    $results = $wpdb->get_results("SELECT * FROM $table");
                    $NumRows = count((array) $results);
                    if ($NumRows > 0) {
                        ?>
                        <div class="error"><p><strong><?php _e("Please Upgrade to Pro Version to add more Products.", 'menu-test'); ?></strong></p></div>
                        <?php
                    } else {

                        $wpdb->insert($table, array('company_name' => $productName, 'created_by' => $productAuthor, 'short_code' => $shortCode), array('%s', '%s', '%s'));
                        ?>
                        <div class="updated"><p><strong><?php _e('settings saved.', 'menu-test'); ?></strong></p></div>
                        <?php
                    }
                } else {
                    ?>
                    <div class="error"><p><strong><?php _e('Product Name can not be empty', 'menu-test'); ?></strong></p></div>
                    <?php
                }
            }
        } elseif (isset($_POST['delete'])) {
            if (!empty($_POST['product'])) {
                foreach ($_POST['product'] as $check) {
                    global $wpdb;
                    $table = $wpdb->prefix . 'coupon_company';
                    $company_name = $wpdb-> get_row("SELECT company_name FROM $table WHERE id='$check'",ARRAY_A);
                    
                    delete_option("CouponPlugin_EmailSetup_".$company_name["company_name"]);
                    $wpdb->delete($table, array('id' => $check), array('%d'));
                    
                    get_option("CouponPlugin_EmailSetup_".$product);
                }
                ?>
                <div class="updated"><p><strong><?php _e('Product successfully deleted', 'menu-test'); ?></strong></p></div>
                <?php
            }
        }
        ?>

        <div class="container">

            <h2> <?php echo __('Products', 'menu-test'); ?></h2>

            <div class="tab-content">

                <input type="button" name="addproduct" id="addproduct" class="button-primary" value="<?php esc_attr_e('Add New Product') ?>">
                <a class="button-primary"  style="background-color: #D92D0B;" href="https://www.meacloudserver.com/meastore/register/" target="_blank"><?php esc_attr_e('Register Plugin') ?> </a> 
                
                
                <a class="button-primary"  style="background-color: #D92D0B;" href="http://www.meacloudserver.com/meastore" target="_blank"><?php esc_attr_e('Upgrade to Pro Version') ?> </a> 
                <br><br>
                <form name="newproduct" id="newproduct" method="post" action="">
                    <!--<input type="hidden" name="page" value="sub-page" />-->
                    <input type="hidden" name="hidden" value="Y"> 
                    <label for="name">Product Name</label>
                    <input type="text" name="name" class="regular-text" /><br><br>
                    <input type="submit" name="addnewdata" class="button-primary" value="<?php esc_attr_e('Add Product') ?>" />

                    <input type="button" name="cancel" id="cancel" class="button-primary" value="<?php esc_attr_e('Cancel') ?>" /><br><br>

                </form>
                <form method="post" action="" onSubmit="if (!confirm('Do you want to delete selected products?')) {
                            return false;
                        }">
                    <table id="company">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="selectAll"></th>
                                <th>Description</th>
                                <th>Short Code</th>
                                <th> Author </th>
                                <th> Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php getProductDetails(); ?>
                        </tbody>
                    </table>
                    <br><input type="submit" name="delete" id="delete" class="button-primary" value="<?php esc_attr_e('Delete Selected Product') ?>"  />
                </form>
            </div>
        </div>
    </body>
</html>