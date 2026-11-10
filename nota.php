<?php
// nota.php  ->  nota pembayaran
// File ini cuma "pintu masuk" (mirip index.php di Modul 12):
// bikin objek controller, terus panggil invoke(). Logikanya ada di app/controllers/NotaController.php
require_once 'app/init.php';

$controller = new NotaController();
$controller->invoke();
