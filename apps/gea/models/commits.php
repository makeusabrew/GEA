<?php

class Commit extends Object {
}

class Commits extends Table {
    protected $meta = array(
        'columns' => array(
            'repository_id' => array(
                'type' => 'foreign_key',
                'table' => 'Repositories',
            ),
            'hash' => array(
                'type' => 'text',
            ),
            'email' => array(
                'type' => 'text',
            ),
            'date' => array(
                'type' => 'number',
            ),
            'message' => array(
                'type' => 'text',
            ),
        ),
    );

    public function findByGithubId($id) {
        return $this->find(array(
            'github_id' => $id,
        ));
    }
}
