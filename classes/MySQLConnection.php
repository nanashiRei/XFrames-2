<?php
/** 
 * @author nanashiRei
 * 
 * 
 */
class MySQLConnection
{
    public $host;
    public $username;
    public $password;
    public $database;
    public $port;
    public $prefix;
    
    function __construct ($host,$user,$pass,$database,$port=3306,$prefix='xf2_')
    {
        $this->host = $host;
        $this->username = $user;
        $this->password = $pass;
        $this->database = $database;
        $this->port = $port;
        $this->prefix = $prefix;
    }
}
?>