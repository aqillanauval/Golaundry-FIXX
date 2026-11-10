<?php
// impor.php  ->  impor data lama .txt ke database (admin)
// File ini cuma "pintu masuk" (mirip index.php di Modul 12):
// bikin objek controller, terus panggil invoke(). Logikanya ada di app/controllers/ImporController.php
require_once 'app/init.php';

$controller = new ImporController();
$controller->invoke();
