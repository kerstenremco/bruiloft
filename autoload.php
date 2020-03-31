<?php
require_once 'config.php';
require_once 'helpers/errorHandler.php';
require_once 'vendor/autoload.php';
spl_autoload_register(function($class) {
    $class = str_replace("\\", DIRECTORY_SEPARATOR, $class);
	include_once dirname(__FILE__).'/'.$class.'.php';
});
?>