<?php
header("content-type: text/html; charset=UTF-8");
require_once('./sqliConnect.php');

require_once('./smarty-3.1.29/Smarty.class.php');
$smarty = new Smarty;

require_once('./functions.php');

require_once('./html2text-0.3.4/html2text.php');

$sqlRequest = sprintf("SELECT
	uniqid,
	_to,
	_from,
	_cc,
	_replyto,
	_datetime,
	_rcpttos,
	_subject,
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

$index = $_GET['index'];

// get the email address from _rcpttos based on the index
$from[0]['email'] = json_decode($sqlResult['_rcpttos'], true)[$_GET['index']];
$from[0]['name']  = null;
// get the name (if availble) corrasponding to that email address from _to
foreach(json_decode($sqlResult['_to'], true) as $t) {
	if ($t['email']==$from[0]['email']){
		$from[0]['name']=$t['name'];
	}
}


$subject = 'RE: ' . $sqlResult['_subject'];
$date = $date = date('l, F jS, Y \a\t h:i:s A');;

$oldFrom = addressListHTML(json_decode($sqlResult['_from'], true), ', ');

// there might be a _reply-to in which case, we would ignore the _from
$toArr = json_decode($sqlResult['_replyto'], true);
if (!$toArr){
	$toArr = json_decode($sqlResult['_from'], true);
}
$to = addressListHTML($toArr);

$cc = null;
if ($_GET['reply']=='all') { // the user choose reply-to-all
	// the $cc will be a conbination of the origional message _cc and _to
	$ccArr = array_merge(json_decode($sqlResult['_to'], true), json_decode($sqlResult['_cc'], true));
	$ccArr = removeFromArray($ccArr, $from[0]['email'], 'email'); // remove self from the reply to group
	$cc = addressListHTML($ccArr);
	$cc = "<p><span class = 'vars'>CC:</span><span>" . $cc . "</span></p>";
}

$oldMessageArr = json_decode($sqlResult['_parts'], true);
if ($oldMessageArr[1]) { // if there is html, let us convert it, if there is not html, use the text one.
	$oldMessage = convert_html_to_text($oldMessageArr[1]['content']);
} else {
	$oldMessage = $oldMessageArr[0]['content'];
}
$oldMessage = str_replace("\r", '', $oldMessage);
$oldMessage = str_replace("\n", '<br />&gt; ', $oldMessage);
$oldMessage = '&gt; ' . $oldMessage;
$oldMessage = 'On ' . $date . ' ' . $oldFrom . ' wrote:' . '<br /><br />' . $oldMessage;
$oldMessage = '<span id="oldMsg" style="color:#666666;line-height:1;">' . $oldMessage . '</span>';
$oldMessage = htmlspecialchars($oldMessage);




$message = '<br /><br />';
$message .= $oldMessage;

$smarty->assign('id', $_GET['id']);
$smarty->assign('key', $_GET['key']);
$smarty->assign('index', $_GET['index']);
$smarty->assign('reply', $_GET['reply']);

$smarty->assign('subject', $subject);
$smarty->assign('attachments', null);
$smarty->assign('from', addressListHTML($from));
$smarty->assign('date', $date);
$smarty->assign('to', $to);
$smarty->assign('cc', $cc);
$smarty->assign('message', $message);
$smarty->display('reply.tpl');
?>
