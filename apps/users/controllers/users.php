<?php
require_once('apps/users/controllers/abstract.php');

class UsersController extends AbstractController {

    public function init() {
        parent::init();
        switch ($this->path->getAction()) {
            case "login":
            case "auth":
            case "authed":
                if ($this->user->isAuthed()) {
                    // go away
                    $this->redirect("/", "You're already logged in!");
                    throw new CoreException("Already logged in");
                }
                break;
            case "logout":
                if ($this->user->isAuthed() === false) {
                    $this->redirect("/");
                    throw new CoreException("Not Authed");
                }
                break;
            default:
                break;
        }
    }

    public function login() {
        //
        return $this->redirect(
            Settings::getValue("github.auth_url")
            ."?client_id=".Settings::getValue("github.client_id")
            ."&redirect_uri=".$this->request->getBaseHref()."auth"
            ."&scope=public_repo"
        );
    }

    public function auth() {
        $code = $this->request->getVar('code');
        if ($code === null) {
            die("handle");
        }

        $fields = "client_id=".Settings::getValue("github.client_id")
            ."&client_secret=".Settings::getValue("github.secret")
            ."&redirect_uri=".$this->request->getBaseHref()."auth"
            ."&code=".$code;


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, Settings::getValue("github.token_url"));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);

        if (preg_match("/access_token=([a-f0-9]+)/", $result, $matches)) {
            $token = $matches[1];
            $data = json_decode(
                file_get_contents("https://api.github.com/user?access_token=".$token)
            , true);
            var_dump($data);
            die();
        } else {
            die("no token");
        }
    }
}
