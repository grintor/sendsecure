<?php
header("content-type: text/html; charset=UTF-8");
require_once('./sqliConnect.php');
require_once('./functions.php');
require_once('./htmlpurifier-4.8.0-standalone/HTMLPurifier.standalone.php');
$HTMLPurifier = new HTMLPurifier();
require_once('./smarty-3.1.29/Smarty.class.php');
$smarty = new Smarty;

$sqlRequest = sprintf("SELECT
	uniqid,
	_to,
	_from,
	_datetime,
	_subject,
	_replyto,
	_cc,
	UNCOMPRESS(AES_DECRYPT(_parts, UNHEX('%s'), '%s')) AS _parts,
	UNCOMPRESS(AES_DECRYPT(_attachments, UNHEX('%s'), '%s')) AS _attachments
	FROM emails WHERE uniqid = '%s'",
	mysqli_real_escape_string($mysqli, $_GET['key']),
	mysqli_real_escape_string($mysqli, $_GET['id']),
	mysqli_real_escape_string($mysqli, $_GET['key']),
	mysqli_real_escape_string($mysqli, $_GET['id']),
	mysqli_real_escape_string($mysqli, $_GET['id'])
);

if (!$sqlResult = $mysqli->query($sqlRequest)) {
    print 'DB query failed';
	print ': ' . $mysqli->error;
	print "\nquery: " . $sqlRequest;
    exit;
}

$sqlResult = mysqli_fetch_array($sqlResult, MYSQL_ASSOC);

$id = $sqlResult['uniqid'];

$parts = json_decode($sqlResult['_parts'], true);
if(isset($parts[1])){ // part[1] means we have html
	$message = $parts[1]['content'];
	$message = $HTMLPurifier->purify($message);
} elseif (isset($parts[0])) { // if we don't have a part[1] then we only have a text message in part[0]
	$message = '<pre>' . htmlspecialchars($parts[0]['content']) . '</pre>';
	$message = $HTMLPurifier->purify($message);
} else {
	$message = "NO MSG";
}

$from = addressListHTML(json_decode($sqlResult['_from'], true));
$to = addressListHTML(json_decode($sqlResult['_to'], true));

$cc = json_decode($sqlResult['_cc'], true);
if ($cc){
	$cc = addressListHTML(json_decode($sqlResult['_cc'], true));
	$cc = "<p><span class = 'vars'>CC:</span><span>" . $cc . "</span></p>";
} else {
	$cc = null;	// if $cc is an empty array, make it null (smarty hates empty arrays)
}

$replyTo = json_decode($sqlResult['_replyto'], true);
if ($replyTo){
	$replyTo = addressListHTML(json_decode($sqlResult['_replyto'], true));
	$replyTo = "<p><span class = 'vars'>Reply To:</span><span>" . $replyTo . "</span></p>";
} else {
	$replyTo = null; // if $replyTo is an empty array, make it null (smarty hates empty arrays)
}


$attachmentArr = json_decode($sqlResult['_attachments'], true);
$attachments = null;
$index = 0;
foreach($attachmentArr as $f) {
	// the part that is used in each instance of the list
	$x = '<a href="download.php?id='. $id .'&index=' . $index . '&key=' . $_GET['key'] . '">' . $f['filename'] . '</a>';
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

$date = $sqlResult['_datetime'];
$date = strtotime($date);
$date = date('l, F jS, Y \a\t h:i:s A', $date);

$subject = $sqlResult['_subject'];

$smarty->assign('id', $_GET['id']);
$smarty->assign('index', $_GET['index']);
$smarty->assign('key', $_GET['key']);
$smarty->assign('subject', $subject);
$smarty->assign('attachments', $attachments);
$smarty->assign('from', $from);
$smarty->assign('date', $date);
$smarty->assign('to', $to);
$smarty->assign('replyTo', $replyTo);
$smarty->assign('cc', $cc);
$smarty->assign('message', $message);
$smarty->display('read.tpl');

	
?>
