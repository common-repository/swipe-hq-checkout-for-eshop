<?php
if ('swipe-class.php' == basename($_SERVER['SCRIPT_FILENAME']))
     die ('<h2>Direct File Access Prohibited</h2>');
     
/*******************************************************************************
 *                      Swipe HQ Checkout IPN Integration Class
 *******************************************************************************
 *      Author:     Optimizer Corporation
 *      Based on:   Sample Extra Payment Gateway
 *      
 *******************************************************************************
*/

class swipehq_class {
    
   var $last_error;                 // holds the last error encountered
   var $ipn_response;               // holds the IPN response from paypal   
   var $ipn_data = array();         // array contains the POST values for IPN
   var $fields = array();           // array holds the fields to submit to paypal
   
   function swipehq_class() {
       
      // initialization constructor.  Called when class is created.
      $this->last_error = '';
      $this->ipn_response = '';
    
   }
   
   function add_field($field, $value) {
      // adds a key=>value pair to the fields array, which is what will be 
      // sent to extra as POST variables.  If the value is already in the 
      // array, it will be overwritten.
      $this->fields["$field"] = $value;
   }

   function submit_swipehq_post() {
      // The user will briefly see a message on the screen that reads:
      // "Please wait, your order is being processed..." and then immediately
      // is redirected to extra.
      $echo = "<form method=\"post\" class=\"eshop eshop-confirm\" action=\"".$this->autoredirect."\"><div>\n";
      $echo .= $this->eshop_submit_extra_post($_POST);
       /*
       * Changes the standard text of the redirect page.
      */
      $echo .='<label for="ppsubmit" class="finalize"><small>'.__('<strong>Note:</strong> Submit to finalize order at Swipe Checkout.','eshop').'</small><br />
      <input class="button submit2" type="submit" id="ppsubmit" name="ppsubmit" value="'.__('Proceed to Checkout &raquo;','eshop').'" /></label>';
	  $echo.="</div></form>\n";
      
      return $echo;
   }


    function post_to_url($url, $body){
         $ch = curl_init ($url);
         curl_setopt ($ch, CURLOPT_POST, 1);
         curl_setopt ($ch, CURLOPT_POSTFIELDS, $body);
         curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
         $html = curl_exec ($ch);
         curl_close ($ch);
         return $html;
    }
    
    function eshop_submit_extra_post($_POST){
        global $eshopoptions, $blog_id;
        
        $Cost=$_POST['amount'];
        if(isset($_POST['tax']))
                $Cost += $_POST['tax'];
        if(isset($_SESSION['shipping'.$blog_id]['tax'])) $Cost += $_SESSION['shipping'.$blog_id]['tax'];
        $theid=$eshopoptions['swipehq']['email'];
        $Cost=number_format($Cost,2);
        $refid=uniqid(rand());
        $_POST['extraoption1'] = $refid;
        $checkid=md5($_POST['extraoption1'].$theid);

        orderhandle($_POST,$checkid);
        $product_details  = '';
        $num_prods = $_POST['numberofproducts'];
        for($x=1;$x<=$num_prods;$x++){
            $product_details .= $_POST['quantity_'.$x] . ' x ' . $_POST['item_name_'.$x] . '<br/>';
        }

        $params = array(
            'merchant_id'           => $eshopoptions['swipehq']['merchant_id'],
            'api_key'               => $eshopoptions['swipehq']['api_key'],
            'td_item'               => $_POST['extraoption1'],
            'td_description'        => $product_details,
            'td_amount'             => $Cost,
            'td_default_quantity'   => 1,
            'td_user_data'          => $_POST['extraoption1'],
            'td_currency'           => strtoupper(trim($eshopoptions['swipehq']['currency'])),
            'td_callback_url'       => site_url().'/index.php?swipehq=redirect',
            'td_lpn_url'            => site_url().'/index.php?swipehq=callback'
        );
        $response = post_to_url(trim($eshopoptions['swipehq']['api_url'],'/').'/createTransactionIdentifier.php', $params);
        $response_data = json_decode($response);

        switch($response_data->response_code){
            case 400:
                 wp_die( __( 'API Access Denied', 'swipehq' ) );
            break;
            case 402:
                wp_die( __( 'API System Error', 'swipehq' ) );
            break;
            case 403:
                wp_die( __( 'Not Enough Parameters', 'swipehq' ) );
            break;
            case 404:
                wp_die( __( 'API result missing', 'swipehq' ) );
            break;
            case 407:
                wp_die( __( 'Inactive Swipe Checkout Account', 'swipehq' ) );
            break;
            case 200:
                $echortn='<input type="hidden" name="transaction_identifier" value="'.$response_data->data->identifier.'" />';
                return $echortn;
            break;
            default:
                wp_die( __( 'There has been a problem connecting to API server', 'swipehq' ) );
            break;
        }
   }   
   function validate_ipn() {
      // generate the post string from the _POST vars aswell as load the
      // _POST vars into an arry so we can play with them from the calling
      // script.
      foreach ($_REQUEST as $field=>$value) { 
         $this->ipn_data["$field"] = $value;
      }
     
   }
}  
?>