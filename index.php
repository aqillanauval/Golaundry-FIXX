<?php
require_once 'app/init.php';

$controller = new AuthController('index');
$controller->invoke();
