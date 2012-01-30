<?php

class User extends Object {
    /**
     * keep track of whether this user is authed (logged in)
     * or not
     */
    protected $isAuthed = false;

    protected $preferences = null;

    /**
     * bung this user's ID in the session
     */
    public function addToSession() {
        Log::debug('adding user ID ['.$this->getId().'] to session, setting cookie identifier ['.$this->identifier.']');
        $s = Session::getInstance();
        $s->user_id = $this->getId();
        $s->identifier = $this->identifier;

        // cookie piggybacks too!
        CookieJar::getInstance()->setCookie('iv_identifier', $this->identifier, (time()+31536000));
    }
    
    /**
     * remove this user from the session
     */
    public function logout() {
    	$s = Session::getInstance();
    	unset($s->user_id);
        unset($s->identifier);
        CookieJar::getInstance()->setCookie('iv_identifier', "", time()-3600);
        $this->setAuthed(false);
    }

    /**
     * is this user authenticated?
     */
    public function isAuthed() {
        return $this->isAuthed;
    }

    /**
     * update this user's authed state
     */
    public function setAuthed($authed) {
        $this->isAuthed = $authed;
    }

}

class Users extends Table {

    protected $meta = array(
        "columns" => array(
            "username" => array(
                "type" => "text",
            ),
            "name" => array(
                "type" => "text",
            ),
            "email" => array(
                "type" => "email",
            ),
            "secret" => array(
                "type" => "secret",
            ),
        ),
    );

    public function loadFromSession() {
        $s = Session::getInstance();
        $id = $s->user_id;
        if ($id === NULL) {
            return new User();
        }
        $user = $this->read($id);
        if (!$user) {
            // oh dear
            Log::debug("Could not find user id [".$id."]");
            return new User();
        }
        if ($user->identifier != $s->identifier) {
            Log::debug("User identifier [".$user->identifier."] does not match sID [".$s->identifier."] - logging out");
            return new User();
        }
        $user->setAuthed(true);
        return $user;
    }

    public function loginWithIdentifier() {
        $identifier = CookieJar::getInstance()->getCookie('iv_identifier');

        if ($identifier === null) {
            // oh well, cya
            return new User();
        }
        $user = $this->find(array(
            'identifier' => $identifier,
        ));
        if ($user === false) {
            Log::debug('could not find user for identifier ['.$identifier.']');
            return new User();
        }
        $user->setAuthed(true);
        return $user;
    }
}
