<?php

PathManager::loadPaths(
    array("/hi", "welcome"),
    array("/stats", "stats"),
    array(
        "pattern" => "/import",
        "action"  => "import",
        "method"  => "POST",
    ),
    array(
        "/(?P<username>[a-z0-9_-]+)", "user_profile"
    )
);
