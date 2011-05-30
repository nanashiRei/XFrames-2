<?php

$conf = parse_ini_file(dirname(__FILE__).'/include/config.ini',true);
header('Location: ' . $conf['global']['Path'] . '/xF/VERSION');
die('REDIRECTING...');

?>
