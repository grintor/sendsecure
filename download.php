<?php
require_once('./sqliConnect.php');

$sqlRequest = sprintf(
		"SELECT * FROM emails WHERE uniqid = '%s'",
		mysqli_real_escape_string($mysqli, $_GET['id'])
);

if (!$sqlResult = $mysqli->query($sqlRequest)) {
    echo 'DB query failed';
	echo $mysqli->error;
    exit;
}

$sqlResult = mysqli_fetch_array($sqlResult, MYSQL_ASSOC);

$attachments = json_decode($sqlResult['attachments'], true);

$attachment = base64_decode($attachments[$_GET['index']]['content']);

header("Content-Disposition: attachment; filename=\"" . $attachments[$_GET['index']]['filename'] . "\"");
header("Content-Type: application/octet-stream");
header("Content-Length: " . strlen($attachment));
header("Connection: close");

print $attachment;

?>
