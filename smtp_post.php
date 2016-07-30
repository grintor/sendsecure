<?php

require_once('./sqliConnect.php');

$message_data = json_decode(file_get_contents('php://input'), true);

file_put_contents("message_data_str.txt", print_r($message_data, true));

$uniqid = uniqid('', true);

$sqlRequest = sprintf(
		"INSERT INTO emails VALUES (UTC_TIMESTAMP, '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')",
		mysqli_real_escape_string($mysqli, $message_data['_smtpuser']),
		mysqli_real_escape_string($mysqli, $message_data['_mailfrom']),
		mysqli_real_escape_string($mysqli, json_encode($message_data['_rcpttos'])),
		mysqli_real_escape_string($mysqli, json_encode($message_data['_peer'])),
		mysqli_real_escape_string($mysqli, $message_data['encoding']),
		mysqli_real_escape_string($mysqli, $message_data['datetime']),
		mysqli_real_escape_string($mysqli, $message_data['subject']),
		mysqli_real_escape_string($mysqli, json_encode($message_data['headers'])),
		mysqli_real_escape_string($mysqli, json_encode($message_data['from'])),
		mysqli_real_escape_string($mysqli, json_encode($message_data['to'])),
		mysqli_real_escape_string($mysqli, json_encode($message_data['reply-to'])),
		mysqli_real_escape_string($mysqli, json_encode($message_data['cc'])),
		mysqli_real_escape_string($mysqli, json_encode($message_data['parts'])),
		mysqli_real_escape_string($mysqli, json_encode($message_data['attachments'])),
		mysqli_real_escape_string($mysqli, $uniqid)
	);

if (!$sqlResult = $mysqli->query($sqlRequest)) {
    echo 'DB query failed';
    exit;
}

print 'mail posted';

foreach($message_data['_rcpttos'] as $rcptto) {
	$headers = 'From: "' . $message_data['from'][0]['name'] . '" <do-not-reply@sendsecure.org>';
	$subject = '[SECURE] ' . $message_data['subject'];
	$message = 'You have a secure message. Click here to read it' . "\r\n" .
	'https://www.sendsecure.org/read.php?id=' . $uniqid;
	mail($rcptto, $subject, $message, $headers);
	
}



?>
