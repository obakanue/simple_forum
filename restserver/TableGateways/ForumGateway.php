<?php

namespace Src\TableGateways;

use Aura\SqlQuery\Common\SelectInterface;
use Aura\SqlQuery\Common\InsertInterface;
use Aura\SqlQuery\Common\UpdateInterface;

class ForumGateway extends AbstractGateway {

    public function __construct($db) {
        parent::__construct($db, 'forum');
    }

    protected function _getFindAllQuery(): SelectInterface {
        $query = $this->createSelectQuery();
        $query->cols(['id', 'name'])
              ->from($this->tableStr);
        return $query;
    }

    protected function _getFindQuery($id): SelectInterface {
        $query = $this->createSelectQuery();
        $query->cols(['id', 'name'])
              ->from($this->tableStr)
              ->where('id = :id')
              ->bindValue('id', $id);
        return $query;
    }

    protected function _getInsertQuery($input): InsertInterface {
        $query = $this->createInsertQuery()->into($this->tableStr);
        $query->cols(['name'])
              ->bindValue('name', $input['name']);
        return $query;
    }

    protected function _getUpdateQuery($input): UpdateInterface {
        $query = $this->createUpdateQuery()->table($this->tableStr);
        $query->cols(['name'])
              ->bindValue('name', $input['name'])
              ->where('id = :id')
              ->bindValue('id', $input['id']);
        return $query;
    }
}


