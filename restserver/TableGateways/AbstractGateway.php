<?php

namespace Src\TableGateways;

use Aura\SqlQuery\Common\SelectInterface;
use Aura\SqlQuery\Common\InsertInterface;
use Aura\SqlQuery\Common\UpdateInterface;
use Aura\SqlQuery\Mysql\Select;
use Aura\SqlQuery\QueryFactory;

use PDO;

abstract class AbstractGateway {
    // This is an abstract class that serves as a base gateway for interacting with database tables.
    // It contains common methods and properties that can be shared among multiple gateways.

    abstract protected function _getFindAllQuery(): SelectInterface;
    // This is an abstract method that should be implemented by child classes.
    // It is responsible for returning the query object to retrieve all records from the table.

    abstract protected function _getFindQuery($id): SelectInterface;
    // This is an abstract method that should be implemented by child classes.
    // It is responsible for returning the query object to retrieve a specific record from the table.

    abstract protected function _getInsertQuery($input): InsertInterface;
    // This is an abstract method that should be implemented by child classes.
    // It is responsible for returning the query object to insert a new record into the table.

    abstract protected function _getUpdateQuery($input): UpdateInterface;
    // This is an abstract method that should be implemented by child classes.
    // It is responsible for returning the query object to update an existing record in the table.

    protected $db;
    protected $tableStr;
    protected $queryFactory;
 
    public function __construct($db, $tableStr) {
        $this->db = $db;
        $this->tableStr = $tableStr;
        $this->queryFactory = new \Aura\SqlQuery\QueryFactory('mysql');
    }

    public function findAll() {
        $query = $this->_getFindAllQuery();
        return $this->executeQuery($query);
    }

    public function find($id) {
        $query = $this->_getFindQuery($id);
        return $this->executeQuery($query);
    }

    public function insert($input) {
	$query = $this->_getInsertQuery($input);
        return $this->executeCommand($query);
    }

    public function deleteSingle($id) {
        $query = $this->queryFactory->newDelete();
        $query->from($this->tableStr)
              ->where('id = :id')
              ->bindValue('id', $id);
        return $this->executeCommand($query);
    }

    public function update($input) {
        $query = $this->_getUpdateQuery($input);
        $query->where('id = :id')
              ->bindValue('id', $input['id']);
        return $this->executeCommand($query);
    }

    public function getTableStr() {
    	return $this->tableStr;
    }

    public function foreignKeysExists($input): bool {
	$query = $this->_getForeignKeysExistsQuery($input);
        $result = $this->executeQuery($query);
        return count($result) > 0;
    }	

    protected function executeStatement($query) {
	$sql = $query->getStatement();
        $bindValues = $query->getBindValues();
	$statement = $this->db->prepare($sql);
        $this->bindValuesToStatement($statement, $bindValues);
        $statement->execute();
    
        return $statement;
    }
    
    protected function executeCommand($query) {
	$statement = $this->executeStatement($query);
        $rowCount = $statement->rowCount();
        $statement->closeCursor();
        return $rowCount > 0;
    }
    
    protected function executeQuery($query) {
        $statement = $this->executeStatement($query);
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        $statement->closeCursor();
        return $result;
    }

    protected function createSelectQuery(): SelectInterface {
        return $this->queryFactory->newSelect();
    }

    protected function createInsertQuery(): InsertInterface {
        return $this->queryFactory->newInsert();
    }

    protected function createUpdateQuery(): UpdateInterface {
        return $this->queryFactory->newUpdate();
    }
    
    private function bindValuesToStatement($statement, $bindValues) {
        foreach ($bindValues as $key => $value) {
            $statement->bindValue($key, $value);
        }
    } 
}

