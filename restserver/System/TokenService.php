<?php
namespace Src\System;

use Src\TableGateways\UserGateway;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class TokenService {
    private static $tokenExpiration = 3600; // Time (in seconds)
    private $userGateway;

    public function __construct($db) {
	$this->userGateway = new UserGateway($db);
    }
    

    public function createToken() {	
	$userInfo = self::_getUserInfoFromBasicAuth();
	// Check if a token already exists in the session
	if (isset($_SESSION['token'])) {
	    // Token already exists and there is no user credentials
	    if(empty($userInfo)) {
		return null;
	    }
        }
   
	$userInfo = $this->determineUserType($userInfo);
        $payload = array(
            'user_id' => $userInfo['id'],
	    'user_type' => $userInfo['user_type']
	);
        $token = $this->generateToken($payload);
	self::saveTokenToSession($token);
        return $token;
    }

    private static function _getUserInfoFromBasicAuth() {
        $header = self::_getUserFromAuthorizationHeader();
	if (empty($header)) {
	    return null;
	}
	return self::_extractBasicAuth($header);
    }

    private static function _extractBearerToken($authorizationHeader) {
        if (strpos($authorizationHeader, 'Bearer') === 0) {
            return substr($authorizationHeader, strlen('Bearer '));
        }
        return null;
    }

    private static function _extractBasicAuth($authorizationHeader) {
        if (strpos($authorizationHeader, 'Basic') === 0) {
            // Basic authentication
            $encodedCredentials = substr($authorizationHeader, strlen('Basic '));
            $decodedCredentials = base64_decode($encodedCredentials);
            list($username, $password) = explode(':', $decodedCredentials);
            return ["name" => $username, "password" => $password];
	}
	return null;
    }


    private static function _getUserFromAuthorizationHeader() {
        if (!isset($_SERVER['HTTP_AUTHORIZATION'])) {
            return null;
        }
        $authorizationHeader = $_SERVER['HTTP_AUTHORIZATION'];   
        return $authorizationHeader;
    }


    private static function _authenticateUser($password, $passwordDb) {
        // User authentication
	// Return true if the authentication is successful, false otherwise
        return password_verify($password, $passwordDb);
    }

    public static function verifyToken($token) {
        // Verify the token integrity and expiration
	// Return true if the token is valid, false otherwise
        try {
	    $decodedToken = \Firebase\JWT\JWT::decode($token, new Key('secret_key', 'HS256'));
            return true;
        } catch (\Exception $e) {
            // Token verification failed
            return false;
        }
    }

    private function determineUserType($userInfo) {
	if (!empty($userInfo)) {
	    $user = $this->userGateway->findByName($userInfo['name']);
	    // Authenticate user
	    if (!empty($user) && $this->_authenticateUser($userInfo['password'], $user['password'])) {
                $userInfo['id'] = $user['id'];   
		    if ($user['admin']) {
            	        $userInfo['user_type'] = USER_TYPE_ADMIN;
		    } 
		    else {	
	                $userInfo['user_type'] = USER_TYPE_USER;
		    }
	    }
       	}
	// If authentication fails or there was no password applied return GUEST as user type
	if (!isset($userInfo['user_type'])) {
		$userInfo['id'] = null;
		$userInfo['user_type'] = USER_TYPE_GUEST;
	}
	return $userInfo;
    }

    private function generateToken($payload) {
	// Generate a token based on the payload data
        $token = \Firebase\JWT\JWT::encode($payload, 'secret_key', 'HS256');
        return $token;
    }

    private function saveTokenToSession($token) {
        // Save the token in the user session
        // $_SESSION represents the user session variable
        $_SESSION['token'] = $token;
    }

    public static function getUserInfoFromToken($token) {
        try {
	    $decodedToken = \Firebase\JWT\JWT::decode($token, new Key ('secret_key', 'HS256'));
            return $decodedToken;
        } catch (\Exception $e) {
            // Token verification failed
            return null;
        }
    }

    public static function getTokenFromHeader() {
	$header = self::_getUserFromAuthorizationHeader();
	if (empty($header)) {
	    return null; 
	}
        return self::_extractBearerToken($header);
    }
}

