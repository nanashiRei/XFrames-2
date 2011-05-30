<?php
require_once ('classes/XFrames.php');
/** 
 * @author nanashiRei
 * 
 * 
 */
class ErrorPage extends XFrames
{
    private $ErrorTemplate;
    private $Exception;
    
    function __construct ()
    {
        parent::__construct();
        $this->caching = false;
    }
    
    public function SetError($error,$exception)
    {
        $this->ErrorTemplate = $error;
        $this->Exception = $exception;
    }
    
    public function DisplayPage()
    {
        $this->displayError($this->ErrorTemplate, $this->Exception);
    }
}
?>