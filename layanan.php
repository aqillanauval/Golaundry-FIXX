<?php
// layanan.php  ->  CRUD layanan (admin)
// File ini cuma "pintu masuk" (mirip index.php di Modul 12):
// bikin objek controller, terus panggil invoke(). Logikanya ada di app/controllers/LayananController.php
require_once 'app/init.php';

$controller = new LayananController();
$controller->invoke();
