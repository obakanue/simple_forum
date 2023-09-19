<?php

namespace Src\System;

class ResponseHandler {
    // HTTP status codes
    const HTTP_OK = "200 OK";
    const HTTP_CREATED = "201 Created";
    const HTTP_NO_CONTENT = "204 No Content";
    const HTTP_BAD_REQUEST = "400 Bad Request";
    const HTTP_UNAUTHORIZED = "401 Unauthorized";
    const HTTP_FORBIDDEN = "403 Forbidden";
    const HTTP_NOT_FOUND = "404 Not Found";
    const HTTP_UNPROCESSABLE_ENTITY = "422 Unprocessable Entity";


    // Exceptions
    const CREATE_EXCEPTION_STR = "Exception: Error occurred during creation of ";
    const UPDATE_EXCEPTION_STR = "Exception: Error occurred during update of ";
    const DELETE_EXCEPTION_STR = "Exception: Error occurred during deletion of ";

    public static function sendResponse($response) {
        // Set the response headers
        header($response['status_code_header']);
        header("Content-Type: application/json; charset=UTF-8");

        // Echo the JSON-encoded response
        echo json_encode($response['body']);
    }

    public static function createResponse($data = null, $statusCode = self::HTTP_OK) {	
	$response = [
            'status_code_header' => "HTTP/1.1 $statusCode",
            'body' => $data
	];
        return $response;
    }

    public static function createEmptyResponse($statusCode = self::HTTP_OK) {
    	return self::createResponse(null, $statusCode);
    }

    public static function createOkResponse() {
    	return self::createEmptyResponse();
    }

    public static function  createOkCreatedResponse() {
	return self::createEmptyResponse(self::HTTP_CREATED);
    }

    public static function unprocessableEntityResponse($message = 'Invalid input') {
	    $response = self::createResponse(['error' => $message], self::HTTP_UNPROCESSABLE_ENTITY);
        return $response;
    }
    
    public static function exceptionResponseCreate($tableStr, $eMessage = null) {
        $errorStr = self::CREATE_EXCEPTION_STR . $tableStr;	    
        if ($eMessage) {
            $errorStr .= ': ' . $eMessage;
        }
	return ResponseHandler::unprocessableEntityResponse($errorStr);        
    }

    public static function exceptionResponseUpdate($tableStr, $eMessage = null) {
	return ResponseHandler::unprocessableEntityResponse($tableStr . self::UPDATE_EXCEPTION_STR . $tableStr);        
    }

    public static function exceptionResponseDelete($tableStr, $eMessage = null) {
	return ResponseHandler::unprocessableEntityResponse($tableStr . self::DELETE_EXCEPTION_STR . $tableStr);        
    }

    public static function notFoundResponse($message = 'Not found') {
        $response = self::createResponse(['error' => $message], self::HTTP_NOT_FOUND);
        return $response;
    }

    public static function forbiddenResponse($message = 'Forbidden') {
        $response = self::createResponse(['error' => $message], self::HTTP_FORBIDDEN);
        return $response;
    }

    public static function unauthorizedResponse($message = 'Unauthorized') {
        $response = self::createResponse(['error' => $message], self::HTTP_UNAUTHORIZED);
        return $response;
    }

    public static function addTokenToResponse($response, $token) {
        if (isset($response['body'])) {
            $response['body']['token'] = $token;
        } else {
            $response['body'] = ['token' => $token];
        }
        return $response;
    }   
}

