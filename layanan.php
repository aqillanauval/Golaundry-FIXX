<?php
// layanan.php  -  FRONT CONTROLLER (bagian "C" di MVC)
// Halaman ini hanya membuat objek controller lalu memanggil invoke().
// Logika ada di app/controllers/LayananController.php, tampilan di app/views/.
require_once __DIR__ . '/app/bootstrap.php';

$controller = new LayananController();
$controller->invoke();
