<?php

/** 
 * @author nanashiRei
 * 
 * 
 */
class ExceptionLoader {
	
	public static function LoadExceptions() {
		$eDir = dir(dirname(__FILE__));
		while($exceptionClass = $eDir->read())
		{
			if($exceptionClass[0] == '.' || $exceptionClass == basename(__FILE__)) continue;
			include dirname(__FILE__).'/'.$exceptionClass;
		}
	}
}

?>