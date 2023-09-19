<?php

namespace Src\TableGateways;

use Aura\SqlQuery\Common\SelectInterface;
use Aura\SqlQuery\Common\InsertInterface;
use Aura\SqlQuery\Common\UpdateInterface;

class UserGateway extends AbstractGateway {

    public function __construct($db) {
        parent::__construct($db, 'user');
    }

    public function findByName($name) { 
	$query = $this->_getFindByNameQuery($name);
        return $this->executeQuery($query)[0];
    }

    protected function _getFindAllQuery(): SelectInterface {
        $query = $this->createSelectQuery();
        $query->cols(['id', 'name', 'password', 'admin'])
              ->from($this->tableStr);

        return $query;
    }

    protected function _getFindQuery($id): SelectInterface {
        $query = $this->createSelectQuery();
        $query->cols(['id', 'name', 'password', 'admin'])
              ->from($this->tableStr)
              ->where('id = :id')
              ->bindValue('id', $id);

        return $query;
    }

    private function _getFindByNameQuery($name): SelectInterface { 
        $query = $this->createSelectQuery();
        $query->cols(['id', 'name', 'password', 'admin'])
              ->from($this->tableStr)
              ->where('name = :name')
              ->bindValue('name', $name);

        return $query;
    }

    protected function _getInsertQuery($input): InsertInterface {
        $query = $this->createInsertQuery()->into($this->tableStr)
            ->cols(['name', 'password', 'admin'])
            ->bindValues([
                'name' => $input['name'],
                'password' => $input['password'],
                'admin' => $input['admin']
            ]);
        return $query;
    }


    protected function _getUpdateQuery($input): UpdateInterface {
        $query = $this->createUpdateQuery()->table($this->tableStr);
        $query->where('id = :id')
              ->bindValue('id', $input['id']);

        if (isset($input['name'])) {
            $query->cols(['name'])
                  ->bindValue('name', $input['name']);
        }

        if (isset($input['password'])) {
            $query->cols(['password'])
                  ->bindValue('password', $input['password']);
        }

        if (isset($input['admin'])) {
            $query->cols(['admin'])
                  ->bindValue('admin', $input['admin']);
        }

        return $query;
    }
}

