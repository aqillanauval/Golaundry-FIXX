<?php
// profile.php  ->  ubah profil & hapus akun
// File ini cuma "pintu masuk" (mirip index.php di Modul 12):
// bikin objek controller, terus panggil invoke(). Logikanya ada di app/controllers/ProfileController.php
require_once 'app/init.php';

$controller = new ProfileController();
$controller->invoke();
