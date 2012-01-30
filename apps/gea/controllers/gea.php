<?php
require_once("apps/users/controllers/abstract.php");
class GeaController extends AbstractController {
    public function welcome() {
        // get repos
        $data = json_decode(file_get_contents("https://api.github.com/user/repos?type=public&access_token=".$this->user->token."&per_page=100"));
        foreach ($data as $newRepo) {
            $repo = Table::factory('Repositories')->findByGithubId($newRepo->id);
            if (!$repo) {
                $repo = Table::factory('Repositories')->newObject();
            }
            $repo->setValues(array(
                'github_id'   => $newRepo->id,
                'user_id'     => $this->user->getId(),
                'name'        => $newRepo->name,
                'description' => $newRepo->description,
                'clone_url'   => $newRepo->clone_url,
            ));
            $repo->save();
        }
        $this->assign('repos', $this->user->getRepositories());
    }

    public function import() {
        $data = $this->user->getRepositories();

        $repos = array();
        $final = array();
        foreach ($data as $repo) {
            $repos[$repo->id] = $repo;
        }

        foreach ($this->request->getVar('repos') as $repoId => $value) {
            if (isset($repos[$repoId])) {
                $final[] = $repos[$repoId]->getId();
            }
        }
        $sender = new ZMQSocket(new ZMQContext(), ZMQ::SOCKET_PUSH);
        $sender->connect(Settings::getValue("zmq.endpoint"));

        $sender->send(json_encode($final));
    }
}
