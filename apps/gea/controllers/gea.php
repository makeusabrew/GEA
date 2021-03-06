<?php
require_once("apps/users/controllers/abstract.php");
class GeaController extends AbstractController {
    public function init() {
        parent::init();

        switch ($this->path->getAction()) {
            case "user_profile":
                if ($this->user->isAuthed() === false) {
                    $this->redirect("/");
                    throw new CoreException("Not Authed");
                }
                break;
            default:
                break;
        }
    }

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

    public function user_profile() {
        // do stats
        //$user = Table::factory('Users')->findByUsername($this->getMatch('username'));
        $user = $this->user;
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


        /**
         * last year
         * by month
         */
        $commits = Table::factory('Commits')->findAllForUserForDays($user->getId(), 365);

        $repos = array();
        foreach ($commits as $commit) {
            if (!isset($repos[$commit->r_name])) {
                $repos[$commit->r_name] = array();
            }
        }

        ksort($repos);

        // stacked bar chart
        $initialDate = strtotime("-365 days", Utils::getTimestamp());
        $labels = array();
        for ($i = 0; $i < 12; $i++) {
            $labels[] = $curDate = date("M Y", strtotime("+{$i} months", $initialDate));
            foreach ($repos as $key => $val) {
                $repos[$key][$i] = null;
                foreach ($commits as $commit) {
                    if ($commit->r_name == $key && date("M Y", $commit->date) == $curDate) {
                        $repos[$key][$i] ++;
                    }
                }
            }
        }
        $this->assign('year_stacked_labels', $labels);
        $this->assign('year_commits_stacked', $repos);
    }

    public function my_projects() {
        $this->assign('projects', $this->user->getRepositories());
    }

    public function add_project() {
        $this->assign('columns', Table::factory('Repositories')->getColumns());

        if ($this->request->isPost()) {
            $repo = Table::factory('Repositories')->newObject();
            $data = $this->filterRequest("type", "name", "clone_url", "auth_type");
            $data['user_id'] = $this->user->getId();
            if ($repo->setValues($data)) {
                $repo->save();
                $sender = new ZMQSocket(new ZMQContext(), ZMQ::SOCKET_PUSH);
                $sender->connect(Settings::getValue("zmq.endpoint"));

                $sender->send(json_encode(array($repo->getId())));
                return $this->redirect("/");
            }

            $this->setErrors(
                $repo->getErrors()
            );
        }
    }

    public function user_commits() {
        $this->assign('commits', Table::factory('Commits')->findAllForUser($this->user->getId()));
    }

    protected function filterRequest() {
        $final = array();
        foreach (func_get_args() as $key) {
            if ($this->request->getVar($key) !== null) {
                $final[$key] = $this->request->getVar($key);
            }
        }
        return $final;
    }
            
}
