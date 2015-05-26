<?php

// DIRECTORY_SEPARATOR
defined('DS') ? null : define('DS', DIRECTORY_SEPARATOR);

// SITE_ROOT
defined('SITE_ROOT') ? null : define('SITE_ROOT', __DIR__.DS.'..');

//LIB_PATH
defined('LIB_PATH') ? null : define('LIB_PATH', __DIR__);

// Load the config file first
require_once(LIB_PATH.DS."config.php");

// Load basic functions
require_once(LIB_PATH.DS."functions.php");

// Load core objects
require_once(LIB_PATH.DS."database.php");
require_once(LIB_PATH.DS."database_object.php");
require_once(LIB_PATH.DS."session.php");
require_once(LIB_PATH.DS."pagination.php");

// Load database related classes
require_once(LIB_PATH.DS."user.php");
require_once(LIB_PATH.DS."photograph.php");
require_once(LIB_PATH.DS."album.php");

?>
