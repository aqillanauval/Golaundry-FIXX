<?php
// pelanggan.php  ->  CRUD akun pelanggan (admin)
// File ini cuma "pintu masuk" (mirip index.php di Modul 12):
// bikin objek controller, terus panggil invoke(). Logikanya ada di app/controllers/PelangganController.php
require_once 'app/init.php';

$controller = new PelangganController();
$controller->invoke();
