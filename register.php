<?php
// register.php  ->  halaman daftar akun baru
// File ini cuma "pintu masuk" (mirip index.php di Modul 12):
// bikin objek controller, terus panggil invoke(). Logikanya ada di app/controllers/AuthController.php
require_once 'app/init.php';

$controller = new AuthController('register');
$controller->invoke();
