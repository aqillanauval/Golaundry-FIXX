<?php
require_once 'app/init.php';

$controller = new AuthController('register');
$controller->invoke();
