<?php
session_start();

unset($_SESSION['admin_is_logged_in']);
unset($_SESSION['admin_rk']);

header( 'Location: index.php' );
?>