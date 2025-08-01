<?php
function load_config($path = "shared/config.json") {
    $json = file_get_contents($path);
    return json_decode($json, true);
}
?>
