<?php

PathManager::loadPaths(
    array("/hi", "welcome"),
    array("/stats", "stats"),
    array(
        "pattern" => "/import",
        "action"  => "import",
        "method"  => "POST",
    )
);
