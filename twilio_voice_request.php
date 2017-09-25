<?php

require_once(__DIR__ . '/twilio/Services/Twilio.php')

$response = new Services_Twilio_Twiml();

//read caller's phone number
//$caller = '+447405426189';
$caller = $_POST['Caller'];
$call_sid = $_POST['CallSid'];

//does it belong to a user?
$calling_user = User::ReadUserByLongPhone($caller);

if (is_null($calling_user))
{
	//say this and hangup
	$msg_404 = 'Sorry, we cannot find your MyBoard voicemail account. Good bye.';
	$response->say($msg_404, array('voice' => 'alice'));
}
else
{
	//does user have a voicemail not yet played?
	$vmlist = Voicemail::ReadAllSentTo($calling_user['_id'], false);

	$vm_count = count($vmlist);
	if ($vm_count == 0)
	{
		$msg_no_new = 'You have no new MyBoard voicemails. Good bye.';
		$response->say($msg_no_new, array('voice' => 'alice'));
	}
	else
	{
		$counter = 0;
		$msg_x_new = 'You have ' . $vm_count . ' new MyBoard voicemails.';
		$response->say($msg_x_new, array('voice' => 'alice'));
		foreach ($vmlist as $voicemail)
		{
			++$counter;
			$paying_user = User::ReadById($voicemail['recipient']);

			Call::doCreate(
				$paying_user['_id'], //from
				$calling_user['_id'], //to
				$calling_user['phone'], //phone: (country and number)
				$call_sid,
				time(NULL) //timestamp
			);

			$msg_intro = 'Message number ' . $counter;
			$response->say($msg_intro, array('voice' => 'alice'));

			//message, read by a man
			$response->say(
				$voicemail['message'],
				array('voice' => 'man')
			);

			if ($voicemail['link'] != '')
			{
				$response->play(
					BASE_SERVER . 'p/' . $voicemail['link'],
					array('loop' => 0)
				);
			}

			Voicemail::UpdateVoicemail($voicemail['_id'], time(NULL));

			$response->pause("");
		}
		$msg_no_other = 'You have no other messages. Good bye.';
		$response->say($msg_no_other, array('voice' => 'alice'));
	}
}

$response->hangup();
echo $response;


?>
