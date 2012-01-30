<?php
require_once('apps/twitterusers/helpers/twitter_api.php');
require_once('apps/api/helpers/deferred_api.php');
require_once('apps/faavorite/controllers/abstract.php');

class UsersController extends AbstractController {

    public function init() {
        parent::init();
        switch ($this->path->getAction()) {
            case "login":
            case "authed":
                if ($this->user->isAccount()) {
                    // go away
                    $this->redirect("/", "You're already logged in!");
                    throw new CoreException("Already logged in");
                }
                break;
            case "logout":
                if ($this->user->isAccount() === false) {
                    $this->redirect("/");
                    throw new CoreException("Not Authed");
                }
                break;
            default:
                break;
        }
    }

    public function login() {
        try {
            $twitterObj = new EpiTwitter(Settings::getValue('twitter.consumer_key'), Settings::getValue('twitter.consumer_secret'));

            Log::debug('Redirecting to twitter auth URL');

            $authedUrl = $this->request->getBaseHref()."authed";
            if ($this->request->getVar('target') !== null) {
                $authedUrl .= "?target=".urlencode($this->request->getVar('target'));
            }

            $twitterUrl = $twitterObj->getAuthenticateUrl(null, array(
                'oauth_callback' => $authedUrl,
            ));

            Log::debug("URL [".$twitterUrl."]");

            return $this->redirect($twitterUrl);
        } catch (Exception $e) {
            // uh oh
            Log::debug('could not get oauth URL');
            return $this->redirect('/', 'Uh oh! Couldn\'t get twitter auth URL');
        }
    }

    public function logout() {
        $this->user->logout();
        return $this->redirect(array(
            "app" => "faavorite",
            "controller" => "Faavorite",
            "action" => "index",
        ), "Bye! Come back soon!");
    }

    public function authed() {
        $twitterObj = new EpiTwitter(Settings::getValue('twitter.consumer_key'), Settings::getValue('twitter.consumer_secret'));

        try {
            $twitterObj->setToken($this->request->getVar('oauth_token'));
            $token = $twitterObj->getAccessToken();
            $twitterObj->setToken($token->oauth_token, $token->oauth_token_secret);

            $details = $twitterObj->get_accountVerify_credentials();
        } catch (Exception $e) {
            return $this->redirect(array(
                "app" => "faavorite",
                "controller" => "Faavorite",
                "action" => "index",
            ), "Oops! There was a problem logging into Twitter. Please try again");
        }

        $newFavourites = 0;
        $newUser = false;

        $user = Table::factory('Users')->findByTwitterId($details->id);
        if ($user === false) {

            $newFavourites = $details->favourites_count;
            $newUser = true;

            Log::debug('creating new account for username ['.$details->screen_name.']');

            $user = Table::factory('Users')->newObject();

            $user->setValues(array(
                'username' => $details->screen_name,
                'name' => $details->name,
                'twitter_id' => $details->id,
                'favourites_count' => $details->favourites_count,
                'followers_count' => $details->followers_count,
                'friends_count' => $details->friends_count,
                'description' => $details->description,
                'profile_image_url' => $details->profile_image_url,
                'oauth_token' => $token->oauth_token,
                'oauth_token_secret' => $token->oauth_token_secret,
                'type' => 'account',
                'identifier' => sha1(mt_rand().$token->oauth_token_secret),
                'fetch_interval' => 60,
            ));
            $user->save();
            StatsD::increment("user.registrations");
        } else {
            Log::debug('authenticating known user ['.$details->screen_name.']');

            $newFavourites = $details->favourites_count - $user->countUserFavourites();

            // sync?
            $updates = array();
            if ($details->screen_name != $user->username) {
                $updates['username'] = $details->screen_name;
            }
            if ($details->profile_image_url != $user->profile_image_url) {
                $updates['profile_image_url'] = $details->profile_image_url;
            }
            if ($token->oauth_token != $user->oauth_token) {
                $updates['oauth_token'] = $token->oauth_token;
            }
            if ($token->oauth_token_secret != $user->oauth_token_secret) {
                $updates['oauth_token_secret'] = $token->oauth_token_secret;
            }
            if ($details->favourites_count != $user->favourites_count) {
                $updates['favourites_count'] = $details->favourites_count;
            }
            if ($details->followers_count != $user->followers_count) {
                $updates['followers_count'] = $details->followers_count;
            }
            if ($details->friends_count != $user->friends_count) {
                $updates['friends_count'] = $details->friends_count;
            }
            if ($details->description != $user->description) {
                $updates['description'] = $details->description;
            }

            if (count($updates)) {
                Log::debug('syncing twitter details...');
                $user->updateValues($updates, true);
                $user->save();
            }
            StatsD::increment("user.logins");
        }

        // sets cookies too
        $user->addToSession();
        $this->user = $user;

        //
        // @todo obviously we shouldn't really be fetching *all* favourites like this on login!
        //

        // if user's physical user_favourites count != $user->favourites_count
        // then re-fetch twitter favourites if user != new

        // (re)establish friends (people you're following) on login
        try {
            $result = $twitterObj->get('/friends/ids.json', array(
                'user_id' => $this->user->twitter_id,
                'cursor' => -1,
                'stringify_ids' => true,
            ));

            Table::factory('UserFriends')->deleteAllForUser($this->user->getId());
            $friends = array();

            foreach($result->response['ids'] as $id) {
                $friends[] = array(
                    'user_id' => $this->user->getId(),
                    'friend_id' => $id,
                );
            }
            Log::debug("adding batch of ".count($friends)." friends...");
            Table::factory('UserFriends')->addBatch($friends);
        } catch (EpiTwitterException $e) {
            // deal with it properly...
            die($e->getMessage());
        }

        $message = "Hi, <strong>".$user->username."</strong>!";

        if ($this->request->getVar('target') !== null) {
            return $this->redirect($this->request->getVar('target'), $message);
        } else {
            if ($newUser) {
                return $this->redirect(array(
                    "app" => "faavorite",
                    "controller" => "Faavorite",
                    "action" => "welcome",
                ), $message);
            } else {
                return $this->redirect(array(
                    "app" => "faavorite",
                    "controller" => "Faavorite",
                    "action" => "index",
                ), $message);
            }
        }
    }

    public function account() {
        //
    }

    public function update_account() {
        if (!$this->request->isPost()) {
            return $this->redirect("/");
        }

        // @todo figure out validation for awkward one-to-manys like this where validation differs
        // based on key
        // @todo make this more performant too - lots of DB queries to delete then add individually...
        $this->user->clearPreferences();
        $data = array_intersect_key($this->request->getPost(), array('email' => true, 'email_digests' => true));
        foreach ($data as $key => $value) {
            $preference = Table::factory('UserPreferences')->newObject();
            $preference->setValues(array(
                'user_id' => $this->user->getId(),
                'key' => $key,
                'value' => $value,
            ));
            $preference->save();
        }
        return $this->redirectAction("account", "Settings Updated");
    }
}
