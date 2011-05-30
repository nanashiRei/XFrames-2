<?php

class Auth
{
	const Cookie = 'xf2_auth';
	const Pagesecret = 'fs940uj5hmub6op39mu4o9p3bm8u6p3j4';
	const Algorythm = 'sha512';
	const UsernameRegex = "/^[a-zA-Z0-9]{3,20}$/";
}

class Flag
{
	const Enum = 0;
	const Value = 1;
	const State = 2;
}

class User
{
	private $dbID;
	private $isAuthed;
	private $isFormAuthed;
	private $setCookie;
	private $password;
	private $key;
	private $xf;
	
	public $username;
	public $Flags;
	
	public function __construct($xf,$id=null)
	{
		$this->Error = false;
		$this->LastError = "";
		$this->isAuthed = false;
		$this->isFormAuthed = false;
		$this->setCookie = true;
		$this->xf = $xf;
		if($id != null) $this->_authByID($id);
	}
	
	public function CreateAccount($username,$password,$repeat)
	{
		if(empty($username) || empty($password)) return false;
		if(!preg_match(Auth::UsernameRegex,$username))
		{
			throw new XFrames_User_Exception('Username does not match the requirements', ERR_USER_BAD_USERNAME);
		}
		if($password != $repeat)
		{
			throw new XFrames_User_Exception('Passwords do not match', ERR_USER_BAD_PASSWORD);
		}
		$query = sprintf("SELECT * FROM `%saccounts` WHERE `username` LIKE '%%%s%%' LIMIT 1",$this->xf->config['MySQL']['prefix'],$this->xf->mysql->real_escape_string($username));
		$user = $this->xf->mysqlQuery($query);
		if(!$user->num_rows)
		{
		    $insUserQuery = sprintf("INSERT INTO `%saccounts` (`username`,`password`) VALUES ('%s','%s')",
		        $this->xf->config['MySQL']['prefix'],
		        $this->xf->mysql->real_escape_string($username),
		        $this->xf->mysql->real_escape_string($password)
		    );
			$this->xf->mysqlQuery($insUserQuery);
		}
		else
		{
			throw new XFrames_User_Exception('User already exists', ERR_USER_ALREADY_EXISTS);
		}
	}
	
	private function _authByID($id)
	{
		if(!is_numeric($id)) return false;
		$userRes = $this->xf->mysqlQuery("SELECT * FROM `authme` WHERE `id` = " . $id . " LIMIT 1");
		if($userRes->num_rows)
		{
			$dbUser = $userRes->fetch_assoc();
			$this->isAuthed = true;
			$this->isFormAuthed = false;
			$this->username = $dbUser['username'];
			$this->dbID = $dbUser['id'];
			$this->key = $dbUser['key'];
			$this->_flags();
			return $this->isAuthed;
		}
		return false;
	}
	
	public function HasKey()
	{
		if(empty($this->key)) return false;
		return true;
	}
	
	public function enableCookie($bool=true)
	{
		$this->setCookie = $bool;
	}
	
	public function IsAuthed()
	{
		return $this->isAuthed;
	}
	
	public function AuthByCookie($cookie)
	{
		if(!empty($cookie))
		{
			$userRes = $this->mysqlQuery(sprinft("SELECT * FROM `%saccounts` WHERE `key` = '%s' LIMIT 1",
			    $this->xf->config['MySQL']['prefix'],
			    $this->xf->mysqlReal_escape_string($cookie)
			));
			if($userRes->num_rows)
			{
				$dbUser = $userRes->fetch_assoc();
				$this->isAuthed = true;
				$this->isFormAuthed = false;
				$this->username = $dbUser['username'];
				$this->dbID = $dbUser['id'];
				$this->key = $dbUser['key'];
				$this->_flags();
				return $this->isAuthed;
			}
		}
		$this->isAuthed = false;
		$this->isFormAuthed = false;
		return $this->isAuthed;
	}
	
	public function SetCookie()
	{
		if($this->isFormAuthed && $this->setCookie)
		{
			$key = hash_hmac(Auth::Algorythm,$this->password,Auth::Pagesecret);
			setcookie(Auth::Cookie,$key,time() + 14 * 24 * 60 * 60,'/');
			$this->mysqlQuery(sprintf("UPDATE `%saccounts` SET `key` = '%s' WHERE `id` = %d",
			    $this->xf->config['MySQL']['prefix'],
			    $this->xf->mysql->real_escape_string($key),
			    $this->dbID
			));
			$this->key = $key;
			return true;
		}
		return false;
	}
	
	public function Logout()
	{
		if($this->isAuthed):
			$this->mysqlQuery(sprintf("UPDATE `%saccounts` SET `key` = '' WHERE `id` = %d",
			    $this->xf->config['MySQL']['prefix'],
			    $this->dbID
			));
			$this->isAuthed = false;
			$this->isFormAuthed = false;
			setcookie(Auth::Cookie,FALSE);
		endif;
	}
	
	public function AuthByForm($username,$password)
	{
		$userRes = $this->mysqlQuery(sprintf("SELECT * FROM `%saccounts` WHERE `username` = LOWER('%s') AND `password` = MD5('%s') LIMIT 1",
		    $this->xf->config['MySQL']['prefix'],    
		    $this->xf->mysqlReal_escape_string($username),
		    $this->xf->mysqlReal_escape_string($password)
		));
		if($userRes->num_rows)
		{
			$this->isAuthed = true;
			$this->isFormAuthed = true;
			$dbUser = $userRes->fetch_assoc();
			$this->username = $dbUser['username'];
			$this->password = $password;
			$this->dbID = $dbUser['id'];
			$this->_flags();
			return $this->isAuthed;
		}
		else
		{
			$this->isAuthed = false;
			$this->isFormAuthed = false;
			return $this->isAuthed;
		}
	}
	
	public function SaveChanges()
	{
		if($this->isAuthed)
		{
			foreach($this->Flags as $key => $data)
			{
				$flagRes = $this->mysql->query(sprintf("SELECT `id`,`key` FROM `%saccounts_flags` WHERE `id` = %d AND `key` = LOWER('%s') LIMIT 1",
				    $this->xf->config['MySQL']['prefix'],
				    $key
				));
				if($flagRes->num_rows)
				{
					$this->mysqlQuery(sprintf("UPDATE `%saccount_flags` SET `value` = '%s', `type` = '%s', `data` = '%s' WHERE `id` = %d AND `key` = LOWER('%s')",
					    $this->xf->config['MySQL']['prefix'],
					    $data->value,
					    $data->type,
					    $data->data,
					    $this->dbID,
					    $key
					));
				}
				else
				{
					$this->xf->mysqlQuery(sprintf("INSERT INTO `%saccounts_flags` (`id`,`key`,`value`,`type`,`data`) VALUES (%d,LOWER('%s'),'%s','%s','%s')",
					    $this->xf->config['MySQL']['prefix'],
					    $this->dbID,
					    $key,
					    $data->value,
					    $data->type,
					    $data->data
					));
				}
			}
		}
		return false;
	}
	
	public function AddEnumFlag($flag,$enum,$set=0)
	{
		$this->Flags->{$flag}->type = 'enum';
		$this->Flags->{$flag}->data = implode(',',$enum);
		$this->SetEnum($flag,$set);
	}
	
	public function SetEnum($flag,$set=0)
	{
		if(isset($this->Flags->{$flag}))
		{
			if(is_numeric($set)):
				$this->Flags->{$flag}->value = $this->Flags->{$flag}->data[$set];
			else:
				if(in_array($set,$this->Flags->{$flag}->data))
					$this->Flags->{$flag}->value = $set;
			endif;
		}
		return false;
	}
	
	public function SetFlag($flag,$value)
	{
		if(isset($this->Flags->{$flag}))
		{
			switch($this->Flags->{$flag}->type)
			{
				case 'value':
					$this->Flags->{$flag}->value = $value;
					return true;
					break;
				case 'state':
					if(is_bool($value)):
						$this->Flags->{$flag}->value = $value;
					else:
						return false;
					endif;
					return true;
					break;
				case 'enum':
					return $this->SetEnum($flag,$value);
					break;
			}
		}
		return false;
	}
	
	public function AddFlag($flag,$type=Flag::State,$value=true)
	{
		if(!isset($this->Flags->{$flag}))
		{
			$this->Flags->{$flag} = new stdClass;
			switch($type)
			{
				case Flag::Enum:
					$this->AddEnumFlag($flag,$value);
					return true;
					break;
				case Flag::Value:
					$this->Flags->{$flag}->type = 'value';
					$this->Flags->{$flag}->value = $value;
					$this->Flags->{$flag}->data = '';
					return true;
					break;
				case Flag::State:
					if(is_bool($value)):
						$this->Flags->{$flag}->type = 'state';
						$this->Flags->{$flag}->data = '';
						$this->Flags->{$flag}->value = $value;
					else:
						return false;
					endif;
					break;
			}
		}	
		return false;
	}
	
	private function _flags()
	{
		if($this->isAuthed)
		{
			$this->Flags = new stdClass;
			$flagRes = $this->mysqlQuery(sprintf("SELECT * FROM `%accounts_flagss` WHERE `id` = %d",$this->dbID));
			while($flag = $flagRes->fetch_assoc())
			{
				if($flag['type'] == 'enum')
				{
					$flag['data'] = explode(',',$flag['data']);
				}
				$this->Flags->{$flag['key']} = (object)$flag;
			}
		}
	}
}