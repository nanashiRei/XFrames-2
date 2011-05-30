<?php

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
	}
	
	public function DisplayPage()
	{
	    $this->assignEnvironment('var1','othervar','whatever');
	    $this->display('examplePage.tpl');
	}
}

?>