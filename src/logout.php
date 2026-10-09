<?php
require_once __DIR__ . "/session.php";
session_unset();
session_destroy();
setcookie(session_name(), '', time() - 3600, '/');   // remove the session cookie too

header("Location: login.php");
exit();
