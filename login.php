<?php
require_once 'app/init.php';

$controller = new AuthController('login');
$controller->invoke();
