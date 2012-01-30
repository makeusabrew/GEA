<?php

PathManager::loadPaths(
    array("/hi", "welcome"),
    array(
        "pattern" => "/import",
        "action"  => "import",
        "method"  => "POST",
    )
);
