<?php

$mysqli = new mysqli('127.0.0.1', 'sendsecure', '4rfvgy7u', 'sendsecure');
if ($mysqli->connect_errno) {
    echo 'could not contact DB';
    exit;
}

if (!$sqlResult = $mysqli->query('SET @@session.block_encryption_mode = "aes-256-cbc"')) {
    echo 'Failed to initilize encryption';
    exit;
}

?>
