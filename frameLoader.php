<?php

require_once dirname(__FILE__).'/classes/XFrames.php';

if(empty($_GET['p']))
{
	$xf = new XFrames;
	$xf->displayError('genericError', new Exception('Requested page was empty', ERR_PAGE_EMPTY_REQUEST));
	die();
}

if(!file_exists(dirname(__FILE__).'/'.$_GET['p'].'.php'))
{
	$xf = new XFrames;
	$xf->displayError('genericError', new Exception('Requested page does not exist', ERR_PAGE_NOT_FOUND));
	die();
}

if(!empty($_GET['q']))
{
	$kvpairs = explode('/',$_GET['q']);
	foreach($kvpairs as $kvpair)
	{
		if(trim($kvpair) == '') continue;
		$kv = explode('-',$kvpair,2);
		if(count($kv) > 1)
		{
			$kv[1] = (strtolower($kv[1]) == "no" ? false : $kv[1]);
			$kv[1] = (strtolower($kv[1]) == "yes" ? true : $kv[1]);
			$_GET[$kv[0]] = $kv[1];
		}
		else
		{
			$_GET[$kv[0]] = true;
		}
	}	
}

include dirname(__FILE__).'/'.$_GET['p'].'.php';

?>