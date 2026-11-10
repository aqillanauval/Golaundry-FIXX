<?php
// karyawan.php  ->  CRUD karyawan (admin)
// File ini cuma "pintu masuk" (mirip index.php di Modul 12):
// bikin objek controller, terus panggil invoke(). Logikanya ada di app/controllers/KaryawanController.php
require_once 'app/init.php';

$controller = new KaryawanController();
$controller->invoke();
