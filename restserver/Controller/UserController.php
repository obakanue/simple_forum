<?php

namespace Src\Controller;

use Src\TableGateways\UserGateway;
use Src\System\ResponseHandler;

class UserController extends AbstractController {
    protected function _initGateway($db) {
        $this->_Gateway = new UserGateway($db);
    }

    protected function _validateGetRequestInput($input) {
        return TRUE;
    }

    protected function _validatePostRequestInput($input) {
        return isset($input['name']) && isset($input['password']);
    }

    protected function _validateDeleteRequestInput($input) {
        return isset($input['id']);
    }

    protected function _validatePutRequestInput($input) {
        return isset($input['name']) || isset($input['password']) || isset($input['admin']);
    }

    protected function _validateRequest($input) {
        return $this->_handleAdminVerifyingProcess($input);
    }
}

