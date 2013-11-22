<? session_start(); ?>
<?php
// Include the library
include_once 'lib/GoCardless.php';

// Set config vars production mode
GoCardless::$environment = 'production';
$account_details = array(
  'app_id'        => '',
  'app_secret'    => '',
  'merchant_id'   => '',
  'access_token'  => ''
);
/*
//sandbox
$account_details = array(
  'app_id'        => '',
  'app_secret'    => '',
  'merchant_id'   => '',
  'access_token'  => ''
);
*/
// Initialize GoCardless
GoCardless::set_account_details($account_details);
?>

<?php
$payment_details = array(
  'max_amount'      => $_SESSION['subtotal'],
  'name'            => 'Football Training Sessions',
  'user'	=> array(
  	'first_name' => $_SESSION['UserFirstName'],
    'last_name' => $_SESSION['UserLastName'],
    'email' => $_SESSION['AuthUsername']
   ),
  'interval_length' => 10,
  'interval_unit'   => 'week',
  //this is crashing=============
  'state' => array(
  	'refer_uri' => $_SESSION['refer_uri'],
	'numChildren' => $_SESSION['numChildren'],
	'bookingID' => $_SESSION['bookingID'],
	'subtotal' => $_SESSION['subtotal'],
	'AuthUsername' => $_SESSION['AuthUsername'],
	'day' => $_SESSION['day'],
	'sat_start_chiswick' => $_SESSION['sat_start_chiswick'],
	'mon_start_chiswick' => $_SESSION['mon_start_chiswick'],
	'sat_start' => $_SESSION['sat_start'],
	'wed_start' => $_SESSION['wed_start'],
	'child' => $_SESSION['child'],
  )
  //================
);
$url = GoCardless::new_pre_authorization_url($payment_details);
// Display the link
echo '<a href="'.$url.'"><img src="images/button_ConfirmPurchase.png" alt="confirm"/></a>';
	//echo'<pre>';
	//var_dump($_SESSION);
	//echo'</pre>';
?>