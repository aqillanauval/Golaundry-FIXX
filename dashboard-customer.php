<?php
// dashboard-customer.php  ->  dashboard pelanggan
// File ini cuma "pintu masuk" (mirip index.php di Modul 12):
// bikin objek controller, terus panggil invoke(). Logikanya ada di app/controllers/DashboardCustomerController.php
require_once 'app/init.php';

$controller = new DashboardCustomerController();
$controller->invoke();
