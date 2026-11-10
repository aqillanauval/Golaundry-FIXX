<?php
// riwayat.php  ->  riwayat pesanan + ulasan (pelanggan)
// File ini cuma "pintu masuk" (mirip index.php di Modul 12):
// bikin objek controller, terus panggil invoke(). Logikanya ada di app/controllers/RiwayatController.php
require_once 'app/init.php';

$controller = new RiwayatController();
$controller->invoke();
