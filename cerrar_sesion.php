<?php
//Para cerrar la sesion del usuario y redirigirlo a la página de login
//se cierra la sesión y se destruyen todas las variables de sesión, luego se redirige al usuario a la página de inicio de sesión.

session_start();
$_SESSION = array();
session_destroy();
header("Location: login.php");
exit;