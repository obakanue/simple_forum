<?php

namespace Src\Controller;

use Src\TableGateways\ForumGateway;

class ForumController extends AbstractController {
    protected function _initGateway($db) {
        $this->_Gateway = new ForumGateway($db);
    }
    
    protected function _validateGetRequestInput($input) {
        return TRUE;
    }

    protected function _validatePostRequestInput($input) {
        return isset($input['name']);
    }

    protected function _validateDeleteRequestInput($input) {
        return TRUE;
    }

    protected function _validatePutRequestInput($input) {
        return isset($input['name']);
    }
}
