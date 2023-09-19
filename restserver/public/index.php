<?php

require "../bootstrap.php";
use Src\Controller\ForumController;
use Src\Controller\PostController;
use Src\Controller\UserController;
use Src\System\ResponseHandler;
use Src\System\TokenService;

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', $uri);

// Remove empty values and reset array keys
$uri = array_values(array_filter($uri));

// Extract the endpoint
$endpoint = array_shift($uri) ?? null; 

if (!$endpoint) {
    header("HTTP/1.1 404 Not Found");
    exit();
}

$requestMethod = $_SERVER["REQUEST_METHOD"];

try {
    $Controller = null;
    if ($endpoint != "login") {
        $token = $tokenService->createToken();
    }
    switch ($endpoint) {
        case "forum":
            $Controller = new ForumController($dbConnection, $requestMethod, $uri, $tokenService); 
	    break;
        case "post":
            $Controller = new PostController($dbConnection, $requestMethod, $uri, $tokenService);
            break;
        case "user":
            $Controller = new UserController($dbConnection, $requestMethod, $uri, $tokenService);
	    break;
	case "login":
	    $token = $tokenService->createToken();
	    break;
        default:
            header("HTTP/1.1 404 Not Found");
            exit();
            break;
    }
    isSet($Controller) ? $Controller->processRequest($token) : ResponseHandler::sendResponse(ResponseHandler::createResponse(array('body' => array('token' => $token))));
}
catch (Exception $e) {
    $code = 500;
    $message = "Internal server error";
    header("HTTP/1.1 500 Server Error");  
    echo json_encode(array("error" => array("code" => $code, "message" => $message)));
    exit();
}
