<?php

/** 
 * @author nanashiRei
 * 
 * 
 */
class XMySQLi 
{
	private $con;
	private $results;
    private $connection;
    
    public static $instance;
	
	function __construct($connection) 
	{
        $this->connection = $connection;    
		
		$this->connect();
	}
	
	public static function getInstance($connection)
	{
	    if(self::$instance == null)
	    {
	        self::$instance = new XMySQLi($connection);
	    }
	    else
	    {
	        return self::$instance;
	    }
	}
	
	private function connect()
	{
		if(!$this->con)
		{
			$this->con = new MySQLi($this->connection->host,$this->connection->username,$this->connection->password,$this->connection->database,$this->connection->port);
    		if($this->con->connect_error){
    			throw new mysqli_sql_exception($this->con->connect_error, $this->con->connect_errno);
    			unset($this->con);
    		}
		}
	}
	
	public function query($query,$id=0)
	{
	    $this->connect();
	    $this->results[$id] = $this->con->query($query);
	}
}

?>