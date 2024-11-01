<?php
/*  
 * Swipe HQ Checkout IPN
 * 04.09.2013 Optimizer Corp.
*/
/*
* default info
*/

global $wpdb,$wp_query,$wp_rewrite,$blog_id,$eshopoptions;
$detailstable=$wpdb->prefix.'eshop_orders';
$derror=__('There appears to have been an error, please contact the site admin','eshop');

//sanitise
include_once(WP_PLUGIN_DIR.'/eshop/cart-functions.php');
$_POST=sanitise_array($_POST);


/*
* reqd info for your gateway
*/
include_once (WP_PLUGIN_DIR.'/eshop-swipehq/eshop-swipe-hq.php');
// Setup class
require_once(WP_PLUGIN_DIR.'/eshop-swipehq/swipehq-class.php');  // include the class file
$p = new swipehq_class;             // initiate an instance of the class


/*
* reqd info /end
*/

$this_script = site_url();
global $wp_rewrite;
if($eshopoptions['checkout']!=''){
	$p->autoredirect=add_query_arg('eshopaction','redirect',get_permalink($eshopoptions['checkout']));
}else{
	die('<p>'.$derror.'</p>');
}

// if there is no action variable, set the default action of 'process'
if(!isset($wp_query->query_vars['eshopaction']))
	$eshopaction='process';
else
	$eshopaction=$wp_query->query_vars['eshopaction'];



if(!function_exists(post_to_url)){
    function post_to_url($url, $body){
         $ch = curl_init ($url);
         curl_setopt ($ch, CURLOPT_POST, 1);
         curl_setopt ($ch, CURLOPT_POSTFIELDS, $body);
         curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
         $html = curl_exec ($ch);
         curl_close ($ch);
         return $html;
    }
}


switch ($eshopaction) {
   case 'process':
        if($eshopoptions['status']!='live' && is_user_logged_in() &&  current_user_can('eShop_admin')||$eshopoptions['status']=='live'){
                $echoit .= $p->submit_swipehq_post();
        }
   break;
}
?>