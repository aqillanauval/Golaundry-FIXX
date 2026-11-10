<?php
// karyawan.php  -  FRONT CONTROLLER (bagian "C" di MVC)
// Halaman ini hanya membuat objek controller lalu memanggil invoke().
// Logika ada di app/controllers/KaryawanController.php, tampilan di app/views/.
require_once __DIR__ . '/app/bootstrap.php';

$controller = new KaryawanController();
$controller->invoke();
