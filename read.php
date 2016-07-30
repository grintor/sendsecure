<?php
header("content-type: text/html; charset=UTF-8");
require_once('./sqliConnect.php');
require_once('./htmlpurifier-4.8.0-standalone/HTMLPurifier.standalone.php');
$HTMLPurifier = new HTMLPurifier();
require_once('./smarty-3.1.29/Smarty.class.php');
$smarty = new Smarty;


// $sqlRequest = sprintf(
		// "SELECT * FROM emails WHERE uniqid = '%s'",
		// mysqli_real_escape_string($mysqli, "579a432c798633.60071576")
// );

$sqlRequest = "SELECT * FROM emails ORDER BY timestamp DESC LIMIT 1";

if (!$sqlResult = $mysqli->query($sqlRequest)) {
    echo 'DB query failed';
	echo $mysqli->error;
    exit;
}

$sqlResult = mysqli_fetch_array($sqlResult, MYSQL_ASSOC);

$id = $sqlResult['uniqid'];

$parts = json_decode($sqlResult['parts'], true);
if(isset($parts[1])){
	$message = $parts[1]['content'];
	$message = $HTMLPurifier->purify($message);
} elseif (isset($parts[0])) {
	$message = '<pre>' . htmlspecialchars($parts[0]['content']) . '</pre>';
	$message = $HTMLPurifier->purify($message);
} else {
	$message = "NO MSG";
}

$to = null;
foreach(json_decode($sqlResult['to'], true) as $t) {
	if(!$to){
		$to = '&quot;' . $t['name'] . '&quot;' . ' &lt;' . $t['email'] . '&gt;';
	} else {
		$to .= ',<br />' . '&quot;' . $t['name'] . '&quot;' . ' &lt;' . $t['email'] . '&gt;';
	}
}

$from = null;
foreach(json_decode($sqlResult['from'], true) as $f) {
	// the part that is used in each instance of the list
	$x = '&quot;' . $f['name'] . '&quot;' . ' &lt;' . $f['email'] . '&gt;';
	if(!$from){
		// the part that is only used for the first element of the list
		$from = $x;
	} else {
		// the part that is used for each subsequent element of the list
		$from .= ',<br />' . $x;
	}
}

$attachmentArr = json_decode($sqlResult['attachments'], true);
$attachments = null;
$index = 0;
foreach($attachmentArr as $f) {
	// the part that is used in each instance of the list
	$x = '<a href="download.php?id='. $id .'&index=' . $index . '">' . $f['filename'] . '</a>';
	$index++;
	if(!$attachments){
		// the part that is only used for the first element of the list
		$attachments = '<p><span class = "vars">Attachments:</span><span>' . $x;
	} else {
		// the part that is used for each subsequent element of the list
		$attachments .= ' | ' . $x;
	}
}
// the part that is appended at the end of the list
if($attachments) $attachments .= '</span></p>';

$date = $sqlResult['datetime'];
$date = strtotime($date);
$date = date('l, F jS, Y \a\t h:i:s A', $date);

$subject = $sqlResult['subject'];

$smarty->assign('subject', $subject);
$smarty->assign('attachments', $attachments);
$smarty->assign('from', $from);
$smarty->assign('date', $date);
$smarty->assign('to', $to);
$smarty->assign('message', $message);
$smarty->display('read.tpl');

	
?>
