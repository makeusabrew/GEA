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

    public function stats() {
        $this->assign('commits', Table::factory('Commits')->findAll(array(
            'email' => $this->user->email,
        ), null, "`date` DESC"));
    }

    public function user_profile() {
        // do stats
        $user = Table::factory('Users')->findByUsername($this->getMatch('username'));
        if (!$user) {
            throw new CoreException('User not found', CoreException::PATH_REJECTED);
        }

        $commits = Table::factory('Commits')->findAllForUserForDays($user->getId(), 7);

        // pie chart stuff
        $commits_week = array();
        foreach ($commits as $commit) {
            if (!isset($commits_week[$commit->r_name])) {
                $commits_week[$commit->r_name] = 1;
            } else {
                $commits_week[$commit->r_name] ++;
            }
        }
        $this->assign('commits_week', $commits_week);

        $commits = Table::factory('Commits')->findAllForUserForDays($user->getId(), 30);

        $repos = array();
        foreach ($commits as $commit) {
            if (!isset($repos[$commit->r_name])) {
                $repos[$commit->r_name] = array();
            }
        }

        ksort($repos);

        // stacked bar chart
        $initialDate = strtotime("-30 days", Utils::getTimestamp());
        $labels = array();
        for ($i = 0; $i < 30; $i++) {
            $labels[] = $curDate = date("F jS", strtotime("+{$i} days", $initialDate));
            foreach ($repos as $key => $val) {
                $repos[$key][$i] = null;
                foreach ($commits as $commit) {
                    if ($commit->r_name == $key && date("F jS", $commit->date) == $curDate) {
                        $repos[$key][$i] ++;
                    }
                }
            }
        }
        $this->assign('stacked_labels', $labels);
        $this->assign('commits_stacked', $repos);
    }
}
