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

    public function findAllForUserForDays($user_id, $days) {
        $sql = "SELECT ".$this->getColumnString("c").",
        r.name as r_name
        FROM `commits` c
        INNER JOIN `repositories` r
        ON (c.repository_id=r.id)
        INNER JOIN `users` u
        ON (u.email=c.email)
        WHERE c.date >= ? AND u.id = ?
        GROUP BY c.id
        ORDER BY c.date DESC";
        
        $params = array(
            strtotime("-{$days} days", Utils::getTimestamp()),
            $user_id
        );
        return $this->queryAll($sql, $params);
    }

    public function findAllForUser($user_id) {
        $sql = "SELECT ".$this->getColumnString("c").",
        r.name as r_name
        FROM `commits` c
        INNER JOIN `repositories` r
        ON (c.repository_id=r.id)
        INNER JOIN `users` u
        ON (u.email=c.email)
        WHERE u.id = ?
        GROUP BY c.id
        ORDER BY c.date DESC";
        
        $params = array(
            $user_id
        );
        return $this->queryAll($sql, $params);
    }
}
