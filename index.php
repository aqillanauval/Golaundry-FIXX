<?php
// index.php  ->  pintu masuk utama, langsung diarahkan ke halaman login
// File ini cuma "pintu masuk" (mirip index.php di Modul 12):
// bikin objek controller, terus panggil invoke(). Logikanya ada di app/controllers/AuthController.php
require_once 'app/init.php';

$controller = new AuthController('index');
$controller->invoke();
