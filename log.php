<?php
// log.php  ->  log aktivitas dari file (admin)
// File ini cuma "pintu masuk" (mirip index.php di Modul 12):
// bikin objek controller, terus panggil invoke(). Logikanya ada di app/controllers/LogController.php
require_once 'app/init.php';

$controller = new LogController();
$controller->invoke();
