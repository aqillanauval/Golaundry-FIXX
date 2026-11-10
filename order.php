<?php
// order.php  ->  buat / edit / batalin pesanan (pelanggan)
// File ini cuma "pintu masuk" (mirip index.php di Modul 12):
// bikin objek controller, terus panggil invoke(). Logikanya ada di app/controllers/OrderController.php
require_once 'app/init.php';

$controller = new OrderController();
$controller->invoke();
