<?php
/*
Plugin Name: Swipe Checkout for eShop
Plugin URI: http://www.swipehq.com
Description: Swipe Payment Gateway for eShop
Version: 3.7.0
Author: Swipe
Author URI: http://www.swipehq.com

    Copyright 2013 Optimizer HQ  (email : support@optimizerhq.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/
register_activation_hook(__FILE__,'eshopswhipehq_activate');
add_filter('plugin_action_links', 'swipe_eshop_action_links', 10, 2 );

function swipe_eshop_action_links( $links, $pluginLink ){
	if($pluginLink != 'eshop-swipehq/eshop-swipe-hq.php') return $links;
	$plugin_links = array(
			'<a href="' . admin_url( 'options-general.php?page=eshop-settings.php&mstatus=Merchant#eshop-m-swipehq' ) . '">' . __( 'Settings', 'Optimizer' ) . '</a>',
	);
	return array_merge( $plugin_links, $links );
}


function eshopswhipehq_activate(){
    /*
    * Activation routines
    */
    global $wpdb;
    $opts=get_option('active_plugins');
    $eshopthere=false;
    foreach($opts as $opt){
            if($opt=='eshop/eshop.php')
                    $eshopthere=true;
    }
    if($eshopthere==false){
        deactivate_plugins('eshop-swipe-hq.php'); //Deactivate ourself
        wp_die(__('ERROR! eShop is not active.','eshop'));
    }
    /*
    * insert email template for use with this merchant gateway, if 151 is changed, then ipn.php needs amending as well
    */
    $table = $wpdb->prefix ."eshop_emails";
    $esubject=__('Your order from ','eshop').get_bloginfo('name');
    $wpdb->query("INSERT INTO ".$table." (id,emailType,emailSubject) VALUES ('151','".__('Automatic Swipe HQ Checkout email','eshop')."','$esubject')");
}
add_action('eshop_setting_merchant_load','eshopswipehqpage');
function eshopswipehqpage($thist){
    /*
    * adding the meta box for this gateway
    */
    add_meta_box('eshop-m-swipehq', __('Swipe Checkout','eshop'), 'swipehq_box', $thist->pagehook, 'normal', 'core');
}

function swipehq_box($eshopoptions) {
    /*
    * the meta box content, obviously you have to set up the required fields for your gateway here
    */
    if(isset($eshopoptions['swipehq'])){
        $eshopswipehq = $eshopoptions['swipehq'];
    }else{
        $eshopswipehq['email']          ='';
        $eshopswipehq['merchant_id']    ='';
        $eshopswipehq['api_key']        ='';
        $eshopswipehq['currency']       ='';
        $eshopswipehq['api_url']        ='';
        $eshopswipehq['payment_url']    ='';
    }
    //add the image
    $eshopmerchantimgpath=WP_PLUGIN_DIR.'/eshop-swipehq/checkout-logo.png';
    $eshopmerchantimgurl=WP_PLUGIN_URL.'/eshop-swipehq/checkout-logo.png';
    $dims[3]='';
    if(file_exists($eshopmerchantimgpath))
    $dims=getimagesize($eshopmerchantimgpath);
    echo '<fieldset>';
    echo '<p class="eshopgatswipehq"><img src="'.$eshopmerchantimgurl.'" '.$dims[3].' alt="Swipe Checkout" title="Swipe Checkout" /></p>'."\n";
?>
	    <p class="cbox">
	        <input id="eshop_methodswipehq" name="eshop_method[]" type="checkbox" value="swipehq"<?php if(in_array('swipehq',(array)$eshopoptions['method'])) echo ' checked="checked"'; ?> />
	        <label for="eshop_methodswipehq" class="eshopmethod">
	            <?php _e('Accept payment by Swipe Checkout','eshop'); ?>
	        </label>
	    </p>
	    
	    <label for="eshop_swipehqmerchantid">
	    	<span style="font-weight: bold; display: block"><?php _e('Merchant ID','eshop'); ?></span>
	    	<span style="color:gray; font-style:italic">Find this in your Swipe Merchant login under Settings -> API Credentials</span>
	    </label>
	    <input id="eshop_swipehqmerchant_id" name="swipehq[merchant_id]" type="text" value="<?php echo $eshopswipehq['merchant_id']; ?>" size="30" maxlength="50" /><br />
	    
	    <label for="eshop_swipehqapikey">
	    	<span style="font-weight: bold; display: block"><?php _e('API Key','eshop'); ?></span>
	    	<span style="color:gray; font-style:italic">Find this in your Swipe Merchant login under Settings -> API Credentials</span>
	    </label>
	    <input id="eshop_swipehqapi_key" name="swipehq[api_key]" type="text" value="<?php echo $eshopswipehq['api_key']; ?>" size="60" /><br />
	    
	    <label for="eshop_swipehqapi_url">
	    	<span style="font-weight: bold; display: block"><?php _e('Api Url','eshop'); ?></span>
	    	<span style="color:gray; font-style:italic">Find this in your Swipe Merchant login under Settings -> API Credentials</span>
	    </label>
	    <input id="eshop_swipehqapi_url" name="swipehq[api_url]" type="text" value="<?php echo $eshopswipehq['api_url']; ?>" size="60" /><br />
	    
	    <label for="eshop_swipehqpayment_key">
	    	<span style="font-weight: bold; display: block"><?php _e('Payment Page Url','eshop'); ?></span>
	    	<span style="color:gray; font-style:italic">Find this in your Swipe Merchant login under Settings -> API Credentials</span>
	    </label>
	    <input id="eshop_swipehqpayment_key" name="swipehq[payment_url]" type="text" value="<?php echo $eshopswipehq['payment_url']; ?>" size="60" /><br />
    
	    <label for="eshop_swipehqcurrency">
	    	<span style="font-weight: bold; display: block"><?php _e('Currency','eshop'); ?></span>
	    	<span style="color:gray; font-style:italic">Find this in your Swipe Merchant login under Settings -> API Credentials</span>
	    </label>
	    <input id="eshop_swipehqcurrency" name="swipehq[currency]" type="text" value="<?php echo $eshopswipehq['currency']; ?>" size="20" maxlength="10" /><br />
	    
	    <label for="eshop_swipehqemail">
	    	<span style="font-weight: bold; display: block"><?php _e('Email address', 'eshop'); ?></span>
	    	<span style="color: gray; font-style:italic">Send eShop Order Confirmation emails to</span>
	    </label>
	    <input id="eshop_swipehqemail" name="swipehq[email]" type="text" value="<?php echo $eshopswipehq['email']; ?>" size="30" maxlength="50" /><br />
    
    </fieldset>
    
    <script>
         function check_config(){
             var elementToRemove = jQuery("#check_config_results");
             if(elementToRemove!=null && typeof(elementToRemove)!="undefined"){
                 elementToRemove.remove();
             }

              var mainForm = document.getElementById("eshop-settings");
              var elementToInsert = document.createElement("div");
              elementToInsert.setAttribute("id", "check_config_results");
              elementToInsert.setAttribute("style", "width:100%;height:100%");
              elementToInsert.innerHTML = "<p style=\"line-height:1;font-size:50px\">Checking config, please wait...</p>";
              jQuery(mainForm).append(elementToInsert);
              

              var merchantId = jQuery("input[name=\"swipehq[merchant_id]\"]").val();
              var apiKey = jQuery("input[name=\"swipehq[api_key]\"]").val();
              var apiURL = jQuery("input[name=\"swipehq[api_url]\"]").val();
              var paymentURL = jQuery("input[name=\"swipehq[payment_url]\"]").val();
              
              var currencyEntered = jQuery("input[name=\"swipehq[currency]\"]").val();


              
              
              var testUrl = <?php echo "\"".WP_PLUGIN_URL."/".plugin_basename(dirname(__FILE__))."/test-plugin.php\"" ?>;



              var urlToLoad = testUrl+"?merchant_id="+merchantId+"&api_key="+apiKey+"&api_url="+apiURL+"&payment_page_url="+paymentURL+((currencyEntered!=null && typeof(currencyEntered!="undefined"))?("&currency="+currencyEntered):"");

              jQuery("#check_config_results").load(urlToLoad);


        }
        
        jQuery(document).ready(function(){
            var main_form = document.getElementById("eshop-settings");
            var buttonToInsert = document.createElement("input");
            buttonToInsert.setAttribute("type", "button");
            buttonToInsert.setAttribute("value", "Check Config");
            buttonToInsert.setAttribute("name", "checkconfig");
            buttonToInsert.setAttribute("onclick", "check_config()");
            jQuery(main_form).append(buttonToInsert);
        });
    </script>
<?php
}

add_filter('eshop_setting_merchant_save','swipehqsave',10,2);
function swipehqsave($eshopoptions,$posted){
    /*
    * save routine for the fields you added above
    */
    global $wpdb;
    $swipehqpost['email']          =$wpdb->escape($posted['swipehq']['email']);
    $swipehqpost['merchant_id']    =$wpdb->escape($posted['swipehq']['merchant_id']);
    $swipehqpost['api_key']        =$wpdb->escape($posted['swipehq']['api_key']);
    $swipehqpost['currency']       =$wpdb->escape($posted['swipehq']['currency']);
    $swipehqpost['api_url']        =$wpdb->escape($posted['swipehq']['api_url']);
    $swipehqpost['payment_url']    =$wpdb->escape($posted['swipehq']['payment_url']);

    $eshopoptions['swipehq']=$swipehqpost;
    return $eshopoptions;
}

add_filter('eshop_merchant_img_swipehq','swipehqimg');
function swipehqimg($array){
    /*
    * adding the image for this gateway, for use on the front end of the site
    */
    $array['path']=WP_PLUGIN_DIR.'/eshop-swipehq/checkout-logo.png';
    $array['url']=WP_PLUGIN_URL.'/eshop-swipehq/checkout-logo.png';
    return $array;
}

add_filter('eshop_mg_inc_path','swipehqpath',10,2);
function swipehqpath($path,$paymentmethod){
    /*
    * adding another necessary link for the instant payment notification of your gateway
    */
    if($paymentmethod=='swipehq')
            return WP_PLUGIN_DIR.'/eshop-swipehq/ipn.php';
    return $path;
}

add_filter('eshop_mg_inc_idx_path','swipehqidxpath',10,2);
function swipehqidxpath($path,$paymentmethod){
    /*
    * adding the necessary link to the class for this gateway
    */
    if($paymentmethod=='swipehq')
            return WP_PLUGIN_DIR.'/eshop-swipehq/swipehq-class.php';
    return $path;
}

//message on fail.
add_filter('eshop_show_success', 'eshop_swipehq_return_fail',10,3);
function eshop_swipehq_return_fail($echo, $eshopaction, $postit){
    if($eshopaction=='swipehq_return'){
        switch($_REQUEST['result']){
            case 'accepted':
                $echo .= "Thank you for shopping with us. Your account has been charged and your transaction is successful. We will be shipping your order to you soon.</p>\n";
            break;
            case 'test-accepted':
                $echo .= "Test transaction result is Test Accepted.</p>\n";
            break;
            case 'test-declined':
                $echo .= "Test transaction result is Test Declined.</p>\n";
            break;
            case 'declined':
                $echo .= '<font color="red">Transaction Declined. We\'re sorry, but the transaction has failed.</font>';
            break;
            default:
                $echo .= '<font color="red">Authorization Denied. We\'re sorry, but we cannot identify this transaction.</font>';
            break;
        }
    }
    return $echo;
}

if(!function_exists('post_to_url')){
    function post_to_url($url, $body){
         $ch = curl_init($url);
         curl_setopt ($ch, CURLOPT_POST, 1);
         curl_setopt ($ch, CURLOPT_POSTFIELDS, $body);
         curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
         $html = curl_exec ($ch);
         curl_close ($ch);
         return $html;
    }
}

//Swipe Checkout Listener
add_action('init','swipehq_listener');
function swipehq_listener(){
    global $wpdb,$wp_query,$wp_rewrite,$blog_id,$eshopoptions;
    $detailstable=$wpdb->prefix.'eshop_orders';
    if(isset($_REQUEST['swipehq'])){
        switch($_REQUEST['swipehq']){
            case 'callback':
                $posted = $_REQUEST;
                if(isset($posted['status']) && isset($posted['identifier_id']) && isset($posted['transaction_id']) && isset($posted['td_user_data'])){

                    $theid=$eshopoptions['swipehq']['email'];
                    $checked=md5($posted['td_user_data'].$theid);

                    //Validate Transaction
                    $params = array(
                        'merchant_id'       => $eshopoptions['swipehq']['merchant_id'],
                        'api_key'           => $eshopoptions['swipehq']['api_key'],
                        'transaction_id'    => $posted['transaction_id'],
                        'identifier_id'     => $posted['identifier_id']
                    );

                    $txn_id = $wpdb->escape($posted['td_user_data']);

                    $response = post_to_url(trim($eshopoptions['swipehq']['api_url'],'/').'/verifyTransaction.php', $params);
                    $response_data = json_decode($response);
                    if($response_data->response_code == 200){
                        if($response_data->data->status == 'accepted' && $response_data->data->transaction_approved == 'yes'){
                            $ok='yes';
                            $sys_notes = "Swipe Checkout: Transaction Accepted. Credit card has been charged. Swipe Transaction ID: ".$posted['transaction_id'];
                            $query2=$wpdb->query("UPDATE $detailstable set admin_note='".$sys_notes."',user_notes='".$sys_notes."' where checkid='$checked'");
                            eshop_mg_process_product($txn_id,$checked);
                        }
                        elseif($response_data->data->status == 'test-accepted'){
                            $ok='yes';
                            $sys_notes = "Swipe Checkout: Test Transaction Accepted. Swipe Transaction ID: ".$posted['transaction_id'];
                            $query2=$wpdb->query("UPDATE $detailstable set admin_note='".$sys_notes."',user_notes='".$sys_notes."' where checkid='$checked'");
                            eshop_mg_process_product($txn_id,$checked);
                        }
                        elseif($response_data->data->status == 'test-declined'){
                            $ok='yes';
                            $sys_notes = "Swipe Checkout: Test Transaction Declined. Swipe Transaction ID: ".$posted['transaction_id'];
                            $query2=$wpdb->query("UPDATE $detailstable set admin_note='".$sys_notes."',user_notes='".$sys_notes."' where checkid='$checked'");
                            eshop_mg_process_product($txn_id,$checked,'Failed');
                        }
                        else{
                            $sys_notes = "Swipe Checkout: Transaction Declined. Credit card has not been charged. Swipe Transaction ID: ".$posted['transaction_id'];
                            $query2=$wpdb->query("UPDATE $detailstable set admin_note='".$sys_notes."',user_notes='".$sys_notes."' where checkid='$checked'");
                            eshop_mg_process_product($txn_id,$checked,'Failed');
                            $ok='no';
                        }
                    }
                    else{
                        $sys_notes = "Swipe Checkout: Unauthorized Transaction. Process Aborted.";
                        $query2=$wpdb->query("UPDATE $detailstable set admin_note='".$sys_notes."',user_notes='".$sys_notes."' where checkid='$checked'");
                        eshop_mg_process_product($txn_id,$checked,'Failed');
                        $ok='no';
                    }

                    if($ok=='yes'){
			//only need to send out for the successes!
			//lets make sure this is here and available
			include_once(WP_PLUGIN_DIR.'/eshop/cart-functions.php');
			eshop_send_customer_email($checked, '151');
                    }

                }
		$_SESSION = array();
		session_destroy();
            break;
            case 'redirect':
                $redir_url = add_query_arg(array('eshopaction'=>'swipehq_return','result'=>$_REQUEST['result']),get_permalink($eshopoptions['cart_success']));
                header( 'Location: '.$redir_url );
                exit();
            break;
        }
        
    }
}

function eshop_swipehq_redirect($data){
    global $eshopoptions;
    if( headers_sent( ) ){
        echo '<script type="text/javascript">location.href="'.trim($eshopoptions['swipehq']['payment_url'], '/').'/?checkout=true&identifier_id='.$data['transaction_identifier'].'";</script>';
    }else{
        header( 'Location: '.trim($eshopoptions['swipehq']['payment_url'], '/').'/?checkout=true&checkid='.$checkid.'&identifier_id='.$data['transaction_identifier']);
    }
}



?>