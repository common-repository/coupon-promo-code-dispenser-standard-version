<?php
function getDefaultEmail($product) {
    global $wpdb;
    $table = $wpdb->prefix . 'coupon_emails';
    $results = $wpdb->get_results("SELECT default_email FROM $table WHERE product_name='$product'");
    $email = $results[0]->default_email;
    return $email;
}

function getAltEmail($product) {
    global $wpdb;
    $table = $wpdb->prefix . 'coupon_emails';
    $results = $wpdb->get_results("SELECT alt_email FROM $table WHERE product_name='$product'");
    return $results[0]->alt_email;
}

function getCodes($product) {
    global $wpdb;
    global $isEmailExists;
    $table = $wpdb->prefix . 'coupons'; //Good practice
    $email_address = $_POST["email"];
    $alreadyExistsEmail = $wpdb->get_results("SELECT * FROM $table where sent_to_email = '$email_address' AND product_name='$product'");
    
    if ((count((array) $alreadyExistsEmail)) > 0) {
        $isEmailExists = TRUE;
        return $isEmailExists;
        
    } else {
        $results = $wpdb->get_results("SELECT * FROM $table where valid=true AND product_name='$product' limit 1");
        $NumRows = count((array) $results);
        //$RandNum = rand(0, $NumRows);
        $code = $results[0]->codes;

        //$updateSentCode = $wpdb->prepare("UPDATE $table SET valid = false WHERE valid=true AND product_name=%s AND codes=%s LIMIT %d",$product,$code,1);
        //$wpdb->query($updateSentCode);
        //$wpdb->update($table, array('valid' => false), array('codes' => $code));
        $isEmailExists = FALSE;
        return $code;
    }
}

function getAvailableCodes($product) {
    global $wpdb;
    $table = $wpdb->prefix . 'coupons'; //Good practice
        $results = $wpdb->get_results("SELECT * FROM $table WHERE valid=TRUE AND product_name='$product' " );
    $NumRows = count((array) $results);
    $i = 0;
    while ($i < $NumRows) {
        print $results[$i]->codes . "\n";
        $i++;
    }
}

function getNumberOfAvailableCodes($product){
    global $wpdb;
    $table = $wpdb->prefix . 'coupons'; //Good practice
        $results = $wpdb->get_results("SELECT * FROM $table WHERE valid=TRUE AND product_name='$product'" );
    $numberOfAvailableCodes = count((array) $results);
    return $numberOfAvailableCodes;
}

function getSentCodes($product) {
    global $wpdb;
    $table = $wpdb->prefix . 'coupons'; //Good practice
    $results = $wpdb->get_results("SELECT * FROM $table WHERE valid=false AND product_name='$product'");
    $NumRows = count((array) $results);
    $i = 0;
    if ($NumRows > 0) {
        while ($i < $NumRows) {
            if($results[$i]->is_signedup == 0){
                $issignedup = "No";
            } else {
                $issignedup = "Yes";
            }
            print "<tr> <td>" . $results[$i]->codes . "</td> <td>" . $results[$i]->sent_to_email . "</td><td>" .$results[$i]->sent_on."</td><td>" .$issignedup."</td></tr>";
            //print $results[$i]->codes."\t";
            //print $results[$i]->sent_to_email. "\n";
            $i++;
        }
    } else {
        print "No codes have been sent.";
    }
}

function getNumberOfSentCodes($product){
    global $wpdb;
    $table = $wpdb->prefix . 'coupons'; //Good practice
    $results = $wpdb->get_results("SELECT * FROM $table WHERE valid=false AND product_name='$product'");
    $numberOfSentCodes = count((array) $results);
    return $numberOfSentCodes;
}

function getSettingDetails($column,$product){
     global $wpdb;
    $table = $wpdb->prefix . 'coupon_settings'; //Good practice
    $results = $wpdb->get_results("SELECT * FROM $table WHERE product_name='$product' ");
    $NumRows = count((array) $results);
    if ($NumRows > 0) {
    $rows = get_object_vars($results[0]);
    return $rows[$column];
    }
}


function getCSVFormattedData ($product){
global $wpdb;
    
    $table = $table = $wpdb->prefix . 'coupons';
    $results = $wpdb->get_results("SELECT codes,sent_to_email,sent_on,is_signedup FROM $table WHERE valid=false AND product_name='$product'");
    $NumRows = count((array) $results);
    $columns = array("Code","Email Adress","Sent On","Signed Up for Updates");
    
    $i = 0;
    $rows = array();
    $rows[0] = $columns;
    foreach ($results as $line) {
        $row = (array) $line;
        if( $row['is_signedup'] == 0){
            $row['is_signedup'] = "No";
        } else {
            $row['is_signedup'] = "Yes";
        }
        array_push($rows, $row);
    }
    
    foreach ($rows as $line) {
        
        foreach ($line as $value) {
            if (next($line)==true)
                echo $value . ",";
            else 
                echo $value;
        }
        echo "\n";
    }
}

function getProductDetails(){
    global $wpdb;
    $table = $table = $wpdb->prefix . 'coupon_company';
    $results = $wpdb->get_results("SELECT * FROM $table");
    $NumRows = count((array) $results);
    $i = 0;
    if ($NumRows > 0) {
        while ($i < $NumRows) {
            
            $id = $i+1;
            $url = menu_page_url( 'setting-page', false ).'&name='.$results[$i]->company_name;
            $complete_url = wp_nonce_url($url , 'trash-post_'.$post->ID, 'product_settings');

            print "<tr> <td><input type='checkbox' name='product[$id]' id='$id' value=".$results[$i]->id."></td> <td>" . '<a href='. esc_url( add_query_arg( array( 'locale' => $default_locale ), $complete_url )). '>'. $results[$i]->company_name.'</a>'
                    . "</td> <td>" . $results[$i]->short_code. "</td><td>" .$results[$i]->created_by."</td><td>" .$results[$i]->added_on."</td></tr>";
            
            //print $results[$i]->codes."\t";
            //print $results[$i]->sent_to_email. "\n";
            $i++;
        }
    } 
}
?>