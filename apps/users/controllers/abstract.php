<?php
abstract class AbstractController extends Controller {
    protected $user = null;

    public function init() {
        $this->user = Table::factory('Users')->loadFromSession();
        if ($this->user->isAuthed() === false) {
            // no worries, what about from cookies?
            Log::debug('looking for user identifier in cookies...');
            $user = Table::factory('Users')->loginWithIdentifier();
            if ($user->isAuthed()) {
                // fantastish
                Log::debug('got user from cookie information, adding to session');
                $user->addToSession();
                $this->user = $user;
            }
        }
        $this->assign('user', $this->user);
    }
}
