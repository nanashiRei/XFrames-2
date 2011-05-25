<?php

error_reporting(0);

require dirname(__FILE__).'/../include/constants.php';
require dirname(__FILE__).'/exceptions/ExceptionLoader.php';
//require dirname(__FILE__).'/MySQLConnection.php';
//require dirname(__FILE__).'/XMySQLi.php';
require dirname(__FILE__).'/XTheme.php';
require dirname(__FILE__).'/Smarty.class.php';

/** 
 * @author nanashiRei
 * 
 * 
 */
class XFrames extends Smarty {
	
	protected $mysql;
	protected $config;
	protected $navigation;
	
	public $Theme;
	
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
				$this->config['MySQL']['prefix'],
				$this->config['MySQL']['port']
			);
			if($this->mysql->connect_error)
			{
			    throw new mysqli_sql_exception($this->mysql->connect_error, $this->mysql->connect_errno);
			}
			
			// Read the navigation
			$this->loadNavigation();
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
	    $this->navigation = new stdClass;
	    $naviElmts = $this->mysql->query(sprintf("SELECT * FROM `%snavigation_categories` ORDER BY `order` ASC",$this->config['MySQL']['prefix']));
	    while($naviElmt = $naviElmts->fetch_object())
	    {
	        $this->navigation->{$naviElmt->order} = $naviElmt;
	        if($naviElmt->hasSub == true)
	        {
	            $this->navigation->{$naviElmt->order}->subItems = $this->loadNavigationSubItems($naviElmt->id);
	        }
	    }
	    $naviElmts->free();
	}
	
	private function loadNavigationSubItems($cid)
	{
	    if(is_numeric($cid))
	    {
	        $subItemsObj = new stdClass;
	        $subItems = $this->mysql->query(sprintf("SELECT * FROM `%snavigation_subitems` WHERE `catId` = %d ORDER BY `order` ASC",$this->config['MySQL']['prefix'],$cid));
	        if($subItems->num_rows)
	        {
	            while($subItem = $subItems->fetch_object)
	            {
	                $subItemsObj->{$subItem->order} = $subItem;
	            }
	        }
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
}

?>