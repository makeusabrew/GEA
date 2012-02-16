<?php

class Repository extends Object {
    protected $table_name = 'Repositories';
    protected $table = 'repositories';
}

class Repositories extends Table {
    protected $object_name = 'Repository';
    protected $meta = array(
        'columns' => array(
            'github_id' => array(
                'type' => 'number',
            ),
            'user_id' => array(
                'type' => 'foreign_key',
                'table' => 'Users',
            ),
            'clone_url' => array(
                'type' => 'text',
                'title' => 'Clone URL',
            ),
            'name' => array(
                'type' => 'text',
                'title' => 'Project Name',
            ),
            'type' => array(
                'type' => 'select',
                'options' => array(
                    'github' => 'GitHub',
                    'other'  => 'Other',
                ),
                'title' => 'Type',
            ),
            'auth_type' => array(
                'type' => 'select',
                'options' => array(
                    'key' => 'SSH Public Key',
                    'password'  => 'Password',
                ),
                'title' => 'Authentication',
            ),
            'description' => array(
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
