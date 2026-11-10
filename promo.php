<?php
// promo.php  ->  CRUD promo + upload foto (admin)
// File ini cuma "pintu masuk" (mirip index.php di Modul 12):
// bikin objek controller, terus panggil invoke(). Logikanya ada di app/controllers/PromoController.php
require_once 'app/init.php';

$controller = new PromoController();
$controller->invoke();
