<?php
session_start();
session_unset();
session_destroy();

header('Location: https://odymaterialy.skauting.cz/');
die();
?>
