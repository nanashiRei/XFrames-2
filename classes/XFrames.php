<?php

error_reporting(E_ALL);

require dirname(__FILE__).'/../include/constants.php';
require dirname(__FILE__).'/exceptions/ExceptionLoader.php';
require dirname(__FILE__).'/XTheme.php';
require dirname(__FILE__).'/User.php';
require dirname(__FILE__).'/Smarty.class.php';

/** 
 * @author nanashiRei
 * 
 * 
 */
abstract class XFrames extends Smarty {
	
	protected $config;
	
	public $mysql;
	public $Navigation;	
	public $Theme;
	public $Environment;
	public $User;
	
	/**
	 * 
	 */
	function __construct() 
	{
		// Construct Smarty
		parent::__construct();
		
		ExceptionLoader::LoadExceptions();
		
		// Do XFrames Stuff
		try {
			// Load Config
			$this->config = parse_ini_file(dirname(__FILE__) . '/../include/config.ini',true);
			
			// Load and configure Smarty
			$this->template_dir = $this->config['Smarty']['templates'];
			$this->compile_dir = $this->config['Smarty']['compiled'];
			$this->cache_dir = $this->config['Smarty']['cached'];
			$this->compile_check = false;
			$this->force_compile = true;
			
			$this->caching = false;
			
			$this->assignByRef('XF', $this, true);
			
			// Load Theming
			$this->Theme = new XTheme;
			
			// Verify Config
			$this->validateConfig();
			
            // Connect to MySQLi
			$this->mysql = new MySQLi(
			    $this->config['MySQL']['host'],
				$this->config['MySQL']['username'],
				$this->config['MySQL']['password'],
				$this->config['MySQL']['database'],
				$this->config['MySQL']['port']
			);
			if($this->mysql->connect_error)
			{
			    throw new mysqli_sql_exception($this->mysql->connect_error, $this->mysql->connect_errno);
			}
			
			// Read the navigation
			$this->loadNavigation();
			
			// Set User object
			$this->User = new User($this);
			if(isset($_COOKIE[Auth::Cookie]))
			    $this->User->AuthByCookie($_COOKIE[Auth::Cookie]);
		}
		catch(mysqli_sql_exception $e)
		{
			$this->displayError('mysqlError',$e);
		}
		catch(XFrames_Config_Exception $e)
		{
			$this->displayError('configError',$e);
		}
		catch(Exception $e)
		{
			$this->displayError('genericError', $e);
		}
	}
	
	function __destruct()
	{
	    parent::__destruct();
	    $this->mysql->close();
	}
	
	public function displayError($template,$exception)
	{
		$this->assignByRef('Exception', $exception, true);
		$this->display('errors/' . $template . '.tpl');
		die();
	}
	
	private function validateConfig()
	{
		if(
			!isset($this->config['MySQL']) ||
			!isset($this->config['Smarty']) ||
			!isset($this->config['global']) ||
			!isset($this->config['Modules'])
		)
		{
			throw new XFrames_Config_Exception('Config incomplete', ERR_CONFIG_INCOMPLETE);
		}	
	}
	
	private function loadNavigation()
	{
	    $this->Navigation = new stdClass;
	    $query = sprintf("SELECT * FROM `%snavigation_categories` ORDER BY `order` ASC",$this->config['MySQL']['prefix']);
	    $naviElmts = $this->mysqlQuery($query);
	    while($naviElmt = $naviElmts->fetch_object())
	    {
	        $this->Navigation->{$naviElmt->order} = $naviElmt;
	        if($naviElmt->hasSub == true)
	        {
	            $this->Navigation->{$naviElmt->order}->subItems = $this->loadNavigationSubItems($naviElmt->id);
	        }
	    }
	    $naviElmts->free();
	}
	
	private function loadNavigationSubItems($cid)
	{
	    if(is_numeric($cid))
	    {
	        $subItemsObj = new stdClass;
	        $subItems = $this->mysqlQuery(sprintf("SELECT * FROM `%snavigation_subitems` WHERE `catId` = %d ORDER BY `order` ASC",$this->config['MySQL']['prefix'],$cid));
	        if($subItems->num_rows)
	        {
	            while($subItem = $subItems->fetch_object())
	            {
	                $subItemsObj->{$subItem->order} = $subItem;
	            }
	        }
	        return $subItemsObj;
	    }
	}
	
	function getConfig($section,$key)
	{
		if(isset($this->config[$section]))
		{
			if(isset($this->config[$section][$key]))
			{
				return $this->config[$section][$key];
			}
			else 
			{
				throw new XFrames_Config_Exception('Undefined key in section "'. $section . '": ' . $key , ERR_CONFIG_UNDEFINED_KEY);
			}
		}
		else
		{
			throw new XFrames_Config_Exception('Undefined section "' . $section . '"', ERR_CONFIG_UNDEFINED_SECTION);
		}
	}
	
	function assignEnvironment()
	{
	    for($arg = 0; $arg < func_num_args(); $arg++)
	    {
	        if(!isset($_GET[$arg])) continue;
	        $var = func_get_arg($arg);
	        $this->Environment[$var] = $_GET[$arg];
	        $this->assign($var,$this->Environment[$var],true);
	    }
	}
	
	function __call($name,$args)
	{
	    //TODO: Wie mach ich das??   
	    if(preg_match("/(mysql|user)([a-zA-Z]+)/",$name,$fnmatch))
	    {
	        switch($fnmatch[1])
	        {
	            case 'mysql':
	                if(method_exists($this->mysql,strtolower($fnmatch[2])))
	                {
	                    return call_user_func_array(array($this->mysql,strtolower($fnmatch[2])), $args);
	                }
	                break;   
	        }
	    }
	}
	
	// Abstract stuff
	
	abstract protected function DisplayPage();
}

?>