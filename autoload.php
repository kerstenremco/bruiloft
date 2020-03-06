<?php
include_once 'config.php';
require_once 'vendor/autoload.php';
spl_autoload_register(function($class) {
    $class = str_replace("\\", DIRECTORY_SEPARATOR, $class);
	include_once $_SERVER['DOCUMENT_ROOT'] . CONF_DIRECTORY . $class . '.php';
});
?>