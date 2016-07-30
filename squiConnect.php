<?php

$mysqli = new mysqli('127.0.0.1', 'blah', 'blah', 'blah');
if ($mysqli->connect_errno) {
    echo 'could not contact DB';
    exit;
}

?>
