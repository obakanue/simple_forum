<?php
require 'vendor/autoload.php';

session_start();

use Dotenv\Dotenv;
use Src\System\DatabaseConnector;
use Src\System\TokenService;

$dotenv = new DotEnv(__DIR__);
$dotenv->load();

define('USER_TYPE_GUEST', 0);
define('USER_TYPE_USER', 1);
define('USER_TYPE_ADMIN', 2);

$db = new DatabaseConnector();
$dbConnection = $db->getConnection();

// Retrieve or create the TokenService instance from the session
$tokenService = new TokenService($dbConnection);

