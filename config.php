<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
define('DOMAIN', 'http://bruiden.local/');
define('DB_NAME', 'bruiden');
define('DB_HOST', 'localhost');
define('DB_USER', 'bruiden');
define('DB_PASSWORD', 'bruiden');
define('MAIL_PORT', 2525);
define('MAIL_USERNAME', 'MAILTRAP-GEBRUIKERSNAAM-HIER');
define('MAIL_PASSWORD', 'MAILTRAP-WACHTWOORD-HIER');
define('MAIL_SMTP', 'smtp.mailtrap.io');
define('MAIL_FROM', ['info@bruidenapp.nl' => 'BruidenApp']);
define('GIFTS_IMG_PATH', 'public/img/gifts/');
define('WEDDINGS_IMG_PATH', 'public/img/weddings/');
?>
