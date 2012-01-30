<?php
require_once("apps/users/controllers/abstract.php");
class GeaController extends AbstractController {

    public function welcome() {
        // get repos
        $data = json_decode(
            file_get_contents("https://api.github.com/user/repos?type=public&access_token=".$this->user->token)
        , true);
        /*
        $data = json_decode(
            file_get_contents(
                "https://api.github.com/repos/makeusabrew/jaoss/commits?access_token=".$this->user->token."&per_page=100")
        , true);
        var_dump($data); die();
        */
        $repos = array();
        foreach ($data as $repo) {
            $r = new stdClass();
            $r->name = $repo['name'];
            $repos[] = $r;
        }
        $this->assign('repos', $repos);
    }
}
