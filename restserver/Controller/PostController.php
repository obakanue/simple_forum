<?php
namespace Src\Controller;

use Src\TableGateways\PostGateway;

class PostController extends AbstractController {
    protected function _initGateway($db) {
        $this->_Gateway = new PostGateway($db);
    }
    
    protected function _validateGetRequestInput($input) {
	return TRUE;
    }
    
    protected function _validatePostRequestInput($input) {
	$inputValidated = isset($input['forum_id']) && isset($input['user_id']) && isset($input['message']);
        if (!$inputValidated) {
	    return $inputValidated;
	}
	$result = $this->_Gateway->foreignKeysExists($input);
	return $result;
    }
    
    protected function _validateDeleteRequestInput($input) {
        return isset($input['id']);
    }
    
    protected function _validatePutRequestInput($input) {
        return isset($input['message']) && isset($input['id']);
    }

    protected function _validateRequest($input) {
        return $this->_handleUserVerifyingProcess($input);
    }
}

