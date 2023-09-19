<?php

namespace Src\TableGateways;

use Aura\SqlQuery\Common\SelectInterface;
use Aura\SqlQuery\Common\InsertInterface;
use Aura\SqlQuery\Common\UpdateInterface;

class PostGateway extends AbstractGateway
{
	
    public function __construct($db) {
        parent::__construct($db, 'post');
    }

    public function findPostsInForum($id) {
        $select = $this->createSelectQuery();
        $select->cols([
                'post.*',
                'user.name AS name'
            ])
            ->from($this->tableStr)
            ->leftJoin('user', 'post.user_id = user.id')
	    ->where('post.forum_id = :forum_id')
            ->orderBy(['post.created DESC'])
	    ->bindValue('forum_id', $id);
        return $this->executeQuery($select);
    } 

    protected function _getFindAllQuery(): SelectInterface{
        $query = $this->createSelectQuery();
        $query->cols(['id', 'forum_id', 'user_id', 'message', 'created'])
              ->from($this->tableStr);

        return $query;
    }

    protected function _getFindQuery($id): SelectInterface {
        $query = $this->createSelectQuery();
        $query->cols(['id', 'forum_id', 'user_id', 'message', 'created'])
              ->from($this->tableStr)
              ->where('id = :id')
              ->bindValue('id', $id);

        return $query;
    }

    protected function _getInsertQuery($input): InsertInterface {
        $query = $this->createInsertQuery()->into('post');
        $query->cols(['forum_id', 'user_id', 'message'])
              ->set('created', 'NOW()');
        $query->bindValue('forum_id', $input['forum_id']);
        $query->bindValue('user_id', $input['user_id']);
        $query->bindValue('message', $input['message']);
    
        return $query;
    }

    protected function _getUpdateQuery($input): UpdateInterface {
        $query = $this->createUpdateQuery()->table($this->tableStr);
	$query->where('id = :id')
	      ->bindValue('id', $input['id'])
	      ->cols(['message'])
              ->bindValue('message', $input['message']);

        return $query;
    }

    protected function _getForeignKeysExistsQuery($input): SelectInterface {
        $query = $this->createSelectQuery()
            ->cols(['1'])
            ->from('user')
            ->join(
                'INNER',
                'forum',
                'user.id = :user_id AND forum.id = :forum_id'
            )
            ->bindValue('user_id', $input['user_id'])
            ->bindValue('forum_id', $input['forum_id']); 
        return $query;
    }
}

