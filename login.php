<?php
session_start();
unset($_SESSION['autenticado']);
unset($_SESSION['2fa_pendiente']);
unset($_SESSION['Usuario']);
include("login_form.php");
?>
