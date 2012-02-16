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
    array(
        "/(?P<username>[a-z0-9_-]+)", "user_profile"
    )
);
