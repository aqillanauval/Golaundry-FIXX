<?php
// dashboard-admin.php  ->  dashboard admin
// File ini cuma "pintu masuk" (mirip index.php di Modul 12):
// bikin objek controller, terus panggil invoke(). Logikanya ada di app/controllers/DashboardAdminController.php
require_once 'app/init.php';

$controller = new DashboardAdminController();
$controller->invoke();
