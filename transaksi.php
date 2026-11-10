<?php
// transaksi.php  ->  CRUD transaksi + pencarian (admin)
// File ini cuma "pintu masuk" (mirip index.php di Modul 12):
// bikin objek controller, terus panggil invoke(). Logikanya ada di app/controllers/TransaksiController.php
require_once 'app/init.php';

$controller = new TransaksiController();
$controller->invoke();
