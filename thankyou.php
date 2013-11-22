<?php
session_start(); 
include_once('DBConnect.php');
include_once('booking_mailer-dd.php');
include_once('booking_settings.php');

//print sessions dates in sent email
function print_all_sessions_html($first_session_date, $selected_session, $format="U") {
        $first_session_exploded = explode("/", $first_session_date);

        $american_date = $first_session_exploded[1]."/".$first_session_exploded[0]."/".$first_session_exploded[2];
        $first_session_timestamp = strtotime($american_date." 06:00");

        if($selected_session=="dayOne") {
            $number_sessions = TRAINING_DAY_ONE_NUMSESSIONS;
            $dict['SL_non_training_days_location'] = "Isleworth";
        } elseif($selected_session=="dayTwo") {
            $number_sessions = TRAINING_DAY_TWO_NUMSESSIONS;
            $dict['SL_non_training_days_location'] = "Isleworth";
        } elseif($selected_session=="trialWed") {
            $number_sessions = TRIAL_DAY_ONE_NUMSESSIONS;
            $dict['SL_non_training_days_location'] = "Isleworth";
        } elseif($selected_session=="trialSat") {
            $number_sessions = TRIAL_DAY_TWO_NUMSESSIONS;
            $dict['SL_non_training_days_location'] = "Isleworth";
        } elseif($selected_session=="dayOneChiswick") {
            $number_sessions = TRAINING_DAY_CHISWICK_ONE_NUMSESSIONS;
            $dict['SL_non_training_days_location'] = "Chiswick";
        } elseif($selected_session=="dayTwoChiswick") {
            $number_sessions = TRAINING_DAY_CHISWICK_TWO_NUMSESSIONS;
            $dict['SL_non_training_days_location'] = "Chiswick";
		} elseif($selected_session=="trialMonChiswick") {
            $number_sessions = TRIAL_DAY_CHISWICK_ONE_NUMSESSIONS;
            $dict['SL_non_training_days_location'] = "Chiswick";
        } elseif($selected_session=="trialSatChiswick") {
            $number_sessions = TRIAL_DAY_CHISWICK_TWO_NUMSESSIONS;
            $dict['SL_non_training_days_location'] = "Chiswick";
        }

        $session_print = "";

        $this_session = $first_session_timestamp;
        $x=0;

        include_once('classes/DbConnection.php');
        $DBConnection = new DbConnection();

        while($x<$number_sessions) {
            $dict['SL_non_training_days_date'] = gmdate("j/n/Y",$this_session);
            $isNonTrainingDay = $DBConnection->rowExistsInTable("SL_non_training_days",$dict);

            if($isNonTrainingDay) {
                $session_print .= "NO TRAINING: ".gmdate($format,$this_session)."<br />";
            } else {
                $x++;
                $session_print .= gmdate($format,$this_session)."<br />";
            }
            $this_session += 604800;
        }

        return $session_print;
    }

// Include the library================================================================================================
include_once 'dd-test/lib/GoCardless.php';


GoCardless::$environment = 'production';
//this uses development keys, must be changed for production
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







// default code from gocardless - resource_uri and state are optional
$confirm_params = array(
  'resource_id'    => $_GET['resource_id'],
  'resource_type'  => $_GET['resource_type'],
  'resource_uri'   => $_GET['resource_uri'],
  'signature'      => $_GET['signature']
);

// State is optional
if (isset($_GET['state'])) {
  $confirm_params['state'] = $_GET['state'];
}
//echo '<pre>';
//var_dump($confirm_params['state']);
//echo '<br><br><br>';
//var_dump($_GET['state']);
//echo '</pre>';

// Returns the confirmed resource if successful, otherwise throws an exception
$pre_auth = GoCardless::confirm_resource($confirm_params);
$transaction_id = $pre_auth->id ;// returns the ID


//if session fails, we charge the minimum for 10 weeks
if(isset($confirm_params['state']['subtotal'])){
	$price = $confirm_params['state']['subtotal'];
	//echo '<h1>$confirm_params[state][subtotal] '.$confirm_params['state']['subtotal'].'</h1>';
	}else{
		$price = 70;
		}


//===========================================================================================================================================


//$link details
include('connectpro.php');

//error escape
if(!$link){die('Connect Error (' . mysqli_connect_errno() . ') '. mysqli_connect_error());}

//testing input //$testdate is replaced by starting date variable, can come via $_SESSION //$testlocation should come from session variable
//need to convert array to string


//session needs to be active or it breaks //we take values from session
if(isset($confirm_params['state']['refer_uri']) && isset($confirm_params['state']['day'])){
	
	$testlocation = $confirm_params['state']['refer_uri'];
	
	if($confirm_params['state']['refer_uri']=='Isleworth'){
		
		//switch tells if 1 or 2 sessions and location a or b
		switch($confirm_params['state']['day'][0]){
			case 'dayOne':
			//code
			$startday = $confirm_params['state']['wed_start'][0];
			$dayofweek = 'Wednesdays';
			break;
			
			case 'dayTwo':
			//code
			$startday = $confirm_params['state']['sat_start'][0];
			$dayofweek = 'Saturdays';
			break;
			
			case 'dayCombined':
			//code 25 > 23
			if(date('Y-m-d',strtotime($confirm_params['state']['wed_start'][0])) > date('Y-m-d',strtotime($confirm_params['state']['sat_start'][0]))){
				$startday = $confirm_params['state']['wed_start'][0];
				}else{
				$startday = $confirm_params['state']['sat_start'][0];
				}
				$dayofweek = 'Wednesdays and Saturdays';
			break;
			
			default:
			//code
			echo 'there was an error 1a';
			}
		
		}else if($confirm_params['state']['refer_uri']=='Chiswick'){
			
		switch($confirm_params['state']['day'][0]){
			case 'dayOneChiswick':
			//code
			$startday = $confirm_params['state']['mon_start_chiswick'][0];
			$dayofweek = 'Mondays';
			break;
			
			case 'dayTwoChiswick':
			//code
			$startday = $confirm_params['state']['sat_start_chiswick'][0];
			$dayofweek = 'Saturdays';
			break;
			
			case 'dayCombinedChiswick':
			//code
			if(date('Y-m-d',strtotime($confirm_params['state']['mon_start_chiswick'][0])) > date('Y-m-d',strtotime($confirm_params['state']['sat_start_chiswick'][0]))){
				$startday = $confirm_params['state']['mon_start_chiswick'][0];
				}else{
				$startday = $confirm_params['state']['sat_start_chiswick'][0];
				}
				$dayofweek = 'Mondays and Saturdays';
			break;
			
			default:
			//code
			echo 'there was an error 2b';
			//echo '<br>';
			//var_dump($confirm_params['state']['day']);
			//echo '<br>';
			//var_dump($confirm_params['state']['day'][0]);
			}
			
			}
	
	
	
	
	}
	//else{ echo 'There was an error. We will contact you. E001' ; exit;}
//end if isset $session E001 = no $confirm_params['state']['day']


$testdate = date('Y-m-d',strtotime(str_replace('/', '-', $startday)));

//this makes sure it gets all non-training days until the end of the week

if(date('l',$testdate)!='Saturday'){
	$testdate2 = date('Y-m-d',strtotime($testdate.' next saturday'));
}else{
	$testdate2 = $testdate;
	}

//sql query //select distinct week number from function that converts string to date in x column, expecting values d m Y. from table y where loation equals $testlocation variable and string converted to date value from column z is between $testdate variable and the interval of 10 week from $testdate (the number 2 in the week functions is the mode, 1-53 with week starting on sunday)
$retrieve = mysqli_query($link,"SELECT DISTINCT WEEK(STR_TO_DATE(SL_non_training_days.SL_non_training_days_date, '%d/%m/%Y'),2) FROM SL_non_training_days WHERE SL_non_training_days_location ='".$testlocation."'  AND STR_TO_DATE(SL_non_training_days.SL_non_training_days_date, '%d/%m/%Y') BETWEEN '".$testdate."' AND DATE_ADD('".$testdate2."', INTERVAL 10 WEEK) ");

//count how many different weeks
$found = mysqli_num_rows($retrieve); 

?>












<?php
//run payment for the first time
$pre_auth = GoCardless_PreAuthorization::find($transaction_id);
$bill_details = array(
  'name'    => '10 Training Sessions',
  'amount'  => $price
);
$bill = $pre_auth->create_bill($bill_details);

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<?php
	require_once("classes/BookingForm-dd.php");
	require_once("booking_settings.php");
?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <title>Welcome to SL Football Academy</title>
    <link href="css/inner-stylesheet.css" rel="stylesheet" type="text/css" />
    <link rel="shortcut icon" href="images/favicon.ico"  />
    <link href="css/menu.css"  type="text/css" rel="stylesheet"  />
    <link href="css/custom.css" rel="stylesheet" type="text/css" />
    <script type="text/jscript"  src="js/menu.js"></script>
</head>

<body onload="pageLoaded()">
<div id="maincontainer">
    <div id="centreur">
        <?php include_once('includes/header.php'); ?>

        <div id="wrapper">
            <h1>BOOKING CONFIRMATION</h1>
            <span class="content">
<?php
//if successful, will work out next payment and insert in to table
if($bill){
	//$found is the number of holidays within $testdate and 10 weeks 
	$result = $found;
	//add that to 10 week sessions
	$np = 10+$result;
	
	//need to build for next payment, db and tables
	//$sql = "INSERT INTO table (id,transaction_id,first_payment,next_payment) VALUES ()";
	
	//$date is today, first payment
	$date = date('d-m-Y', strtotime($testdate));
	$datet = date('Y-m-d',strtotime($date));
	//find out when next payment is. look up holiday table where column > today //num row //retrieve results //if results , number results = $variable
	//$nextday is today + number of weeks to work out next payment
	$nextday = date('d-m-Y', strtotime($date.' +'.$np.' week'));
	$nextdayt = date('Y-m-d',strtotime($nextday));
	//echoes //this is where the mysql records the transactions per kid
	$todayd = date('Y-m-d');
	//if 2 children, we do this to record the each individual an not the total paid
	$pricet = $confirm_params['state']['subtotal']/$confirm_params['state']['numChildren'];
	
	//we check how many children are being paid for in a loop
		for ($i = 1; $i <= $confirm_params['state']['numChildren']; $i++) {
    		//echo '<br/>First payment on '.$date.'<br/>Next Payment on '.$startday.'<br/>Transaction id '.$transaction_id.'<br/> Kid id '.$confirm_params['state']['child'][$i].'<br/>Today\'s date '.$todayd.'<br/>Booking Id '.$confirm_params['state']['bookingID'].' <br/>';
			//echo $date.' '.$np.' '.$result.' '.$confirm_params['state']['mon_start_chiswick'][0].' '.$confirm_params['state']['sat_start_chiswick'][0].'<br/>';			
			
			
			
			
			//it is not running============================================ after the session breaks
			$sql_rec = "INSERT INTO SL_dd_info (dd_id,dd_date,dd_transaction_id,dd_firstsession_payment,dd_nextsession_payment,dd_kid_id,dd_training_location,dd_amount_paid,dd_user_email,dd_training_day) 
										VALUES ('','$todayd','$transaction_id','$datet','$nextdayt','".$confirm_params['state']['child'][$i-1]."','".$confirm_params['state']['refer_uri']."','$pricet','".$confirm_params['state']['AuthUsername']."','".$confirm_params['state']['day'][0]."')";
										
			if(mysqli_query($link,$sql_rec)){
				//echo('Details recorded');
				$ourref = mysqli_insert_id($link);
				
				$sql="UPDATE SL_bookings SET SL_bookings_status='Paid',SL_bookings_totalpaid='$price' WHERE SL_bookings_id='".$confirm_params['state']['bookingID']."'";
				$result = mysqli_query($link,$sql);
				
				}	else{
					echo mysql_errno($link) . ": " . mysql_error($link) . "\n";
					}				
										
		}
		

		
//==============================================================================================================================================================================================================		
		
		$sql9 = "SELECT * FROM SL_login WHERE SL_login.SL_login_username='".$confirm_params['state']['AuthUsername']."'";
		$result9=mysql_query($sql9);
		while ($row9=mysql_fetch_array($result9)){
	      	extract($row9);
		}
		
		$sendToAddress="Parent Name:<br />".$SL_login_parentfirstname." ".$SL_login_parentsecondname."<br />Parent Phone Number: $SL_login_parentmobile<br />Parent Email Address: $SL_login_username";
		
		// create html for basket representation in email
		

				
				$BasketHTML .= "
				<table width='90%'><tr><td colspan='2' align='left'>Booking ID: SportsLinx_WT_".$ourref."<br />$BookingInfo</td></tr>
				<tr><td colspan='2'>
					Parent/Carer Name: ".$SL_login_parentfirstname." ".$SL_login_parentsecondname."<br />
					Parent/Carer Email: $SL_login_username<br />
					Parent/Carer Phone: $SL_login_parentmobile<br />
				</td>
				
				<tr><td colspan='2'><hr /></td></tr><tr><th align='left'>Child</th><th align='right'>Cost</th></tr><tr><td colspan='2'><hr /></td></tr>";

				for($si = 1; $si <= $confirm_params['state']['numChildren']; $si++){
					
				$sql7 = "SELECT * FROM SL_children WHERE SL_children.SL_child_id='".$confirm_params['state']['child'][$si-1]."'";
				
				$result7=mysql_query($sql7);
				if(!$result7)
				{
					$BasketHTML .="<tr><td colspan='2'>There was an error retrieving the booking details. Please check your paypal email for booking information.</td></tr>";
				}
				$num7 = mysql_num_rows($result7);
			    if ($num7 > 0)
			    {
					while ($row7=mysql_fetch_array($result7))
				    {
						extract($row7);
					
						$child_firstname = str_replace("'","&apos;",$SL_child_firstname);
                        $child_surname = str_replace("'","&apos;",$SL_child_secondname);
                        $child_dob = $SL_child_DOB_day."/".$SL_child_DOB_month."/".$SL_child_DOB_year;
						$locationt = $confirm_params['state']['refer_uri'];
						//find day and location
						$BasketHTML .= "<tr><td align='left'><b>$child_firstname $child_surname, DOB $child_dob - $dayofweek at $locationt</b></td>";
						//$BasketHTML .= "<td align='right'>&#163;".number_format($SL_bookingskid_cost,2)."</td></tr>";


                        $BasketHTML .= "<tr><td align='left' colspan='2'>Sessions:<br />";

                        if($confirm_params['state']['day'][$si-1]=="dayOne") {
                            $BasketHTML .= print_all_sessions_html($confirm_params['state']['wed_start'][$si-1], "dayOne", "D jS M");
                        } elseif($confirm_params['state']['day'][$si-1]=="dayTwo") {
                            $BasketHTML .= print_all_sessions_html($confirm_params['state']['sat_start'][$si-1], "dayTwo", "D jS M");
                        } elseif($confirm_params['state']['day'][$si-1]=="dayCombined") {
                            $BasketHTML .= print_all_sessions_html($confirm_params['state']['wed_start'][$si-1], "dayOne", "D jS M");
                            $BasketHTML .= "<br /><br />";
                            $BasketHTML .= print_all_sessions_html($confirm_params['state']['sat_start'][$si-1], "dayTwo", "D jS M");
                        } elseif($confirm_params['state']['day'][$si-1]=="dayOneChiswick") {
                            $BasketHTML .= print_all_sessions_html($confirm_params['state']['mon_start_chiswick'][$si-1], "dayOneChiswick", "D jS M");
                        } elseif($confirm_params['state']['day'][$si-1]=="dayTwoChiswick") {
                            $BasketHTML .= print_all_sessions_html($confirm_params['state']['sat_start_chiswick'][$si-1], "dayTwoChiswick", "D jS M");
                        } elseif($confirm_params['state']['day'][$si-1]=="dayCombinedChiswick") {
                            $BasketHTML .= print_all_sessions_html($confirm_params['state']['mon_start_chiswick'][$si-1], "dayOneChiswick", "D jS M");
                            $BasketHTML .= "<br /><br />";
                            $BasketHTML .= print_all_sessions_html($confirm_params['state']['sat_start_chiswick'][$si-1], "dayTwoChiswick", "D jS M");
                        } else {
							$BasketHTML .= "TBC";
						}

                        $BasketHTML .= "</td></tr>";
						$BasketHTML .= "<tr><td colspan='2' style='height:30px;'></td></tr>";
					}
				}
			
				}

			$BasketHTML .= "<tr><td colspan='1' align='right'>Total Amount Paid:</td><td align='right'>£ $price</td></tr><tr><td colspan='2'><hr /></td></tr>";
			$BasketHTML .= "<tr><td colspan='1' align='right'>Your next payment will be on or after the</td><td align='right'>$nextday</td></tr><tr><td colspan='2'><hr /></td></tr>";
			$BasketHTML .="</table>";
		
		
//==============================================================================================================================================================================================================		
		
		
		//sends the emails
		sendemail($confirm_params['state']['AuthUsername'],'1',$transaction_id,$BasketHTML,$ourref,0);
		sendemail('sean@slfootballacademy.co.uk','2',"-",$BasketHTML,$confirm_params['state']['AuthUsername'],$transaction_id);
		
		
		$dontdestroy = array('AuthUsername','UserIsAdmin','UserFirstName','UserLastName');


		$date3 = date('d-m-Y');
		$date3 = date('d-m-Y', strtotime($date3 . " +2 days"));
		//$date3 = strtotime($date3.' +2 days');
		//display alert at homepage
		echo "Thank you for booking on to this training class.  Your transaction has been completed.<br /><br />";
		echo 'Your first payment will be on or after the '.$date3.'. Your second payment is scheduled to be taken on the '.$nextday.'<br /><br />';
		//echo 'You will be charged £'.$confirm_params['state']['subtotal'].'<br/><br/>.';
        echo "A receipt for your purchase has been emailed to your email ".$confirm_params['state']['AuthUsername'].".<br /><br />
		Please <a class='yellowlink' href='weekly_training_chiswick.php'>click here</a> to return to the main booking page.<br /><br />";
	
	//echo'<pre>';
	//var_dump($confirm_params['state']);
	//echo'</pre>';
	
	
	}
		

	        foreach($_SESSION as $sees_key => $sess_val ){
            if(!in_array($sees_key, $dontdestroy)){
                unset($_SESSION[$sees_key]);    
            }   
        }	
	?>
            </span>
        </div>
    </div>
<?php include_once 'footer1.php';?>
</div>

</body>
</html>