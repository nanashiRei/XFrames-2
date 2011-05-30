<?php

require dirname(__FILE__).'/pages/PageExample.php';

@list(,,$var1,$othervar,$extravar) = $_GET;

$xf = new PageExample();
$xf->DisplayPage();


?>