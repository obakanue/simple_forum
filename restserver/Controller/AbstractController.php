<?php

namespace Src\Controller;

use Src\System\ResponseHandler;
use PDO;

abstract class AbstractController {
    abstract protected function _initGateway($db);

    abstract protected function _validateGetRequestInput($input);
    // This is an abstract method that should be implemented by child classes.
    // It is responsible for validating the input parameters for GET requests.

    abstract protected function _validatePostRequestInput($input);
    // This is an abstract method that should be implemented by child classes.
    // It is responsible for validating the input parameters for POST requests.

    abstract protected function _validateDeleteRequestInput($input);
    // This is an abstract method that should be implemented by child classes.
    // It is responsible for validating the input parameters for DELETE requests.
    
    abstract protected function _validatePutRequestInput($input);
    // This is an abstract method that should be implemented by child classes.
    // It is responsible for validating the input parameters for PUT requests.

    protected $_db;
    private $_requestMethod;
    protected $_id;
    protected $_Gateway;
    private $_tokenService;

    public function __construct(PDO $db, $requestMethod, $id, $tokenService) {
        $this->_db = $db;
        $this->_requestMethod = $requestMethod;
	$this->_id = $id;
        $this->_tokenService = $tokenService;
        
        $this->_initGateway($db);
    }

    public function processRequest($token) {
	switch ($this->_requestMethod) {
	    case 'GET':
                $response = $this->_handleGetRequest();
                break;
            case 'POST':
                $response = $this->_handlePostRequest();
                break;
            case 'PUT':
                $response = $this->_handlePutRequest();
                break;
            case 'DELETE':
                $response = $this->_handleDeleteRequest();
                break;
            default:
                $response = ResponseHandler::notFoundResponse();
                break;
	}

	if (isset($token)) {
	    $response = ResponseHandler::addTokenToResponse($response, $token);
	}
        ResponseHandler::sendResponse($response); 
    }

    protected function _getAll() {
	try {
            $result = $this->_Gateway->findAll();
            return $result ? ResponseHandler::createResponse($result) : ResponeHandler::notFoundResponse();
	}
	catch (\Exception $e) {
	   return ResponseHandler::createResponse($e->getMessage(), ResponseHandler::HTTP_NO_CONTENT);          }
    }

    protected function _getSingle($id) {
	try {
	    $result = $this->_Gateway->find($id);
            return $result ? ResponseHandler::createResponse($result) : ResponseHandler::notFoundResponse();
	}
	catch (\Exception $e) {
	    return ResponseHandler::createREsponse($e->getMessage(), ResponseHandler::HTTP_NO_CONTENT);
	}
    }

    protected function _getAllForumPosts($columnName, $id) {
        try {
	    $result = $this->_Gateway->findPostsInForum($id);
            return $result ? ResponseHandler::createResponse($result) : ResponseHandler::notFoundRespone();
	}
	catch (\Exception $e) {
	    return ResponseHandler::createREsponse($e->getMessage(), ResponseHandler::HTTP_NO_CONTENT);
	} 
    }

    protected function _validateRequest($input) {
	if (!$this->_validate($input)) {
            return ResponseHandler::unprocessableEntityResponse();
	}
	return null;
    }

    protected function _validate($input) {
        switch ($this->_requestMethod) {
            case 'GET':
                return $this->_validateGetRequestInput($input);
            case 'POST':
                return $this->_validatePostRequestInput($input);
            case 'PUT':
                return $this->_validatePutRequestInput($input);
            case 'DELETE':
                return $this->_validateDeleteRequestInput($input);
            default:
                return FALSE;
        }
    }

    protected function _handleAdminVerifyingProcess($input) {
        return $this->_verifyUserAuthorization($input, USER_TYPE_ADMIN);
    }
    
    protected function _handleUserVerifyingProcess($input) {
        return $this->_verifyUserAuthorization($input);
    }

    // Private and helper methods for request handling and validation...
    private function _handleGetRequest () {
    	if (empty($this->_id)) {
             $response = $this->_getAll();
        }
	else if ($this->_id[0] == "forum") {
            $response = $this->_getAllForumPosts($this->_id[0], $this->_id[1]);
	} 
	else {
            $response = $this->_getSingle($this->_id[0]);
	}
	return $response;
    }

    private function _handlePostRequest() {
	$input = $this->_getInputFromRequest($this->_id[0]);
	$userInvalidResponse = $this->_validateRequest($input);
        if (!empty($userInvalidResponse)) {	
            return $userInvalidResponse;
	}
	try {
	    $result = $this->_Gateway->Insert($input);
	    return $result ? ResponseHandler::createOkCreatedResponse() : ResponseHandler::exceptionResponseCreate($this->_Gateway->getTableStr());
	}
	catch (\Exception $e) {
	    return ResponseHandler::exceptionResponseCreate($this->_Gateway->getTableStr(), $e->getMessage);
	}
    }

    private function _handleDeleteRequest () {
	$input = $this->_getInputFromRequest($this->_id[0]);    
        $userInvalidResponse = $this->_validateRequest($input);	
	if (!empty($userInvalidResponse)) {
	    return $userInvalidResponse;
	}
	try {
	    $result = $this->_Gateway->deleteSingle($input['id']);
	    return $result ? ResponseHandler::createEmptyResponse() : ResponseHandler::exceptionResponseDelete($this->_Gateway->TableStr());
	}
	catch (\Exception $e) {
	    return ResonseHandler::exceptionResponseDelete($this->_Gateway->GetTableStr(), $e->getMessage);
	}
    }

    private function _handlePutRequest() {
	$input = $this->_getInputFromRequest($this->_id[0]);
	$userInvalidResponse = $this->_validateRequest($input);
        if (!empty($userInvalidResponse)) {
	    return $userInvalidResponse;
	}
	try {
	    $result = $this->_Gateway->update($input);
	    return $result ? ResponseHandler::createEmptyResponse() : ResponseHandler::exceptionResponseUpdate($this->_Gateway->getTableStr());
	}
	catch (\Exception $e) {
	    return ResponseHandler::exceptionResponseUpdate($this->_Gateway->getTableStr(), $e->getMessage);
	}
    }

    private function _verifyUserAuthorization($input, $requiredUserType = null) {
	if (!$this->_validate($input)) {
	    return ResponseHandler::unprocessableEntityResponse();
        }
    
        $token = $this->_tokenService->getTokenFromHeader();
        if (!$token || !$this->_tokenService->verifyToken($token)) {
	    return ResponseHandler::unauthorizedResponse();
    
        }
    
        $userInfo = $this->_tokenService->getUserInfoFromToken($token);
	if ($requiredUserType !== null && $userInfo->user_type !== $requiredUserType) {
            return ResponseHandler::forbiddenResponse();
        }
    
        return null;
    } 

    private function _getInputFromRequest($id = null) {
	$input = (array) json_decode(file_get_contents("php://input"), TRUE);
	if (!empty($id)) {
	    $input['id'] = $id;
	}
	return $input;
    }
}

