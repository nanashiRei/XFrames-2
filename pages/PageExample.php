<?php

//require_once dirname(__FILE__).'/../classes/XFrames.php';

/** 
 * @author nanashiRei
 * 
 * 
 */
class PageExample extends XFrames 
{
	function __construct()
	{
		parent::__construct();
		$this->caching = false;
		
		$this->display('examplePage.tpl');
	}	
}

?>