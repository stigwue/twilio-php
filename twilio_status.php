<?php

require_once(__DIR__ . '/twilio/Services/Twilio.php')

header('Content-type: text/xml');

$call = Call::ReadBySid($_POST['CallSid']);

if (isset($_POST['CallStatus']))
{
	if (!is_null($call))
	{
		switch ($_POST['CallStatus'])
		{
			case 'queued':
				//check if the user has credit, if not, play message and hangup
				//$twiml = new Services_Twilio_Twiml();
			break;

			case 'completed':
				Call::UpdateCallTimestamp($call['_id'], $_POST['CallStatus'], time(NULL), $_POST['CallDuration']);

				//was this person called from MyBoard for the first time?
				//Send them a message stating so.
			break;

			default:
				Call::UpdateCallTimestamp($call['_id'], $_POST['CallStatus'], time(NULL));
			break;
		}
	}
	else
	{
		$message = "Call status could not be updated";
		Log::logRawMessage(time(NULL), $call['user_id'], Log::ERROR, $message);
		return true;
	}
}

?>
