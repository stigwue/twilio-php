<?php

require_once(__DIR__ . '/twilio/Services/Twilio.php')


header('Content-type: text/xml');

$caller = TWILIO_NUMBER;
$call_sid = $_POST['CallSid'];

//requests:
$caller_id = $_POST['caller_id'];
$caller_user = User::ReadById($caller_id);


$called_id = $_POST['myboard_id'];

$called_user = User::ReadById($called_id);
$called_number = Phone::Format($called_user['phone']['number'], Phone::PHONE_LONG, $called_user['phone']['country'], false);

if (!is_null($called_user))
{
	//check credit
 	$has_credit = Plan::checkPlan($caller_user, 'calls');
 	if ($has_credit)
 	{
 		$timelimit = $caller_user['plan']['total_calls'] - $caller_user['plan']['calls'];
 		//put a limit on calls, 4 hours or lower, see https://www.twilio.com/docs/api/twiml/dial#attributes-time-limit
 		if ($timelimit > 14400)
 		{
 			$timelimit = 14400;
 		}
 		echo '<Response>
		    <Dial callerId="' . $caller . '" timeLimit="' . $timelimit . '">' .
		        '<Number>' . $called_number . '</Number>' .
		    '</Dial>
		</Response>';

		Call::doCreate(
			$caller_id, //from
			$called_user['_id'], //to
			$called_user['phone'], //phone: (country and number)
			$call_sid,
			time(NULL) //timestamp
		);
	}
	else
	{
		$response = new Services_Twilio_Twiml();
		$msg = 'This is the MyBoard service.';
		$msg2 = 'Sorry, the user called is not reachable.';
		$response->say($msg, array('voice' => 'alice'));
		$response->pause("");
		$response->say($msg2, array('voice' => 'alice'));
		$response->hangup();
		echo $response;
	}
}
else
{
	$response = new Services_Twilio_Twiml();
	$msg = 'This is the MyBoard service.';
	$msg2 = 'Sorry, the user may not exist.';
	$response->say($msg, array('voice' => 'alice'));
	$response->pause("");
	$response->say($msg2, array('voice' => 'alice'));
	$response->hangup();
	echo $response;
}

?>
