<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
/*
	* This file is to use verify payment from PayPal
	* this function will run when PayPal send information to site
	* User need to place http://siteurl.com/?action=IPN_Handler in IPN field for PayPal
*/

add_action( 'init', 'paypal_ipn' );
function paypal_ipn() {
         
    global $wp;
     
    if (isset($_GET['action']) && $_GET['action']=='IPN_Handler') {
                                 
        if(check_ipn()) {
         
            ipn_request($IPN_status = true);
         
        } else {
         
            ipn_request($IPN_status = false);
        }
     
     }
 
}
function check_ipn() {
 
     $ipn_response = !empty($_POST) ? $_POST : false;
 
     if ($ipn_response == false) {
         
         return false;
         
     }
 
     if ($ipn_response && check_ipn_valid($ipn_response)) {
 
         header('HTTP/1.1 200 OK');
 
         return true;
     }
}
function check_ipn_valid($ipn_response) {
 
    $eventer_paypal_server = eventer_get_settings('eventer_paypal_payment_type');
    $paypal_adr = ($eventer_paypal_server=="1")?"https://ipnpb.paypal.com/cgi-bin/webscr":"https://ipnpb.sandbox.paypal.com/cgi-bin/webscr";
     //$paypal_adr = 'https://ipnpb.sandbox.paypal.com/cgi-bin/webscr'; // sandbox mode
      
     // use https://ipnpb.paypal.com/cgi-bin/webscr for live
      
     // Get received values from post data
      
     $validate_ipn = array('cmd' => '_notify-validate');
   
     $validate_ipn += stripslashes_deep($ipn_response);
 
     // Send back post vars to paypal
 
     $params = array(
         'body' => $validate_ipn,
         'sslverify' => false,
         'timeout' => 60,
         'httpversion' => '1.1',
         'compress' => false,
         'decompress' => false,
         'user-agent' => 'paypal-ipn/'
      );
 
      // Post back to get a response
 
      $response = wp_safe_remote_post($paypal_adr, $params);
 
      // check to see if the request was valid
 
      if (!is_wp_error($response) && $response['response']['code'] >= 200 && $response['response']['code'] < 300 && strstr($response['body'], 'VERIFIED')) {
 
          return true;
 
      }
 
      return false;
 
}
function ipn_request($IPN_status) {
         
     $ipn_response = !empty($_POST) ? $_POST : false;
 
     $ipn_response['IPN_status'] = ( $IPN_status == true ) ? 'Verified' : 'Invalid';
 
     $posted = stripslashes_deep($ipn_response);
		if($ipn_response['IPN_status']=="Verified")
		{
			$ipn_email_content = apply_filters('the_content', eventer_get_settings( 'payment_confirmation_content' ));
			$transaction_id = (isset($posted['txn_id']))?$posted['txn_id']:'';
			$registrant = (isset($posted['option_name1']))?$posted['option_name1']:'';
            $registrant = eventer_decode_security_registration($registrant);
            $registrant = (is_array($registrant) && isset($registrant['reg_id']))?$registrant['reg_id']:'';
            if($registrant=='') return;
			$registrant_email = (isset($posted['option_name2']))?$posted['option_name2']:'';
			$payment_status = (isset($posted['payment_status']))?$posted['payment_status']:'';
			$amount_paid = (isset($posted['payment_gross']))?$posted['payment_gross']:'';
			$amount_paid = ($amount_paid=='' && isset($posted['mc_gross']))?$posted['mc_gross']:'';
			$eventer_id = (isset($posted['item_number']))?$_POST['item_number']:'';
			$paypal_details = serialize($posted);
            $payment_status = ($payment_status=='Completed')?'completed':$payment_status;
			$update_in = array('transaction_id' => $transaction_id, 'status'=>$payment_status, 'paypal_details'=>$paypal_details, 'amount'=>$amount_paid);
			$vals_in = array("%s", "%s", "%s", "%f");
			eventer_update_registrant_details($update_in, $registrant, $vals_in);
            $registrants = eventer_get_registrant_details('id', $registrant);
            apply_filters('eventer_status_changed_completed', $registrants);
			$email_sent_status = eventer_pass_email_registration($registrant, "2");
		}
}
