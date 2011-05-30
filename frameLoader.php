<?php

require_once dirname(__FILE__).'/classes/XFrames.php';
require_once dirname(__FILE__).'/pages/ErrorPage.php';

if(empty($_GET['p']))
{
	$xf = new XFrames;
	$xf->displayError('genericError', new Exception('Requested page was empty', ERR_PAGE_EMPTY_REQUEST));
	die();
}

if(!file_exists(dirname(__FILE__).'/'.$_GET['p'].'.php'))
{
	$xf = new ErrorPage();
	$xf->SetError('genericError', new Exception('Requested page does not exist', ERR_PAGE_NOT_FOUND));
	$xf->DisplayPage();
	die();
}

if(!empty($_GET['q']))
{
	$kvpairs = explode('/',$_GET['q']);
	foreach($kvpairs as $kv)
	{
	    if(empty($kv)) continue;
		$_GET[] = $kv;
	}	
}

include dirname(__FILE__).'/'.$_GET['p'].'.php';

?>