<?php

PathManager::loadPaths(
    array("/hi", "welcome"),
    array("/projects", "my_projects"),
    array("/projects/add", "add_project"),
    array(
        "pattern" => "/import",
        "action"  => "import",
        "method"  => "POST",
    ),
    array("/stats", "user_profile"),
    array("/stats/commits", "user_commits")
);
