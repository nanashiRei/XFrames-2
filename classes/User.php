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
	public $LastError;
	public $Error;
	
	public function __construct($id=null,$xf)
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
		if(!preg_match(Auth::UsernameRegex,$username)):
			$this->Error = true;
			$this->LastError = 'Username does not match against the rules';
			return false;
		endif;
		if($password != $repeat):
			$this->Error = true;
			$this->LastError = 'Passwords do not match';
			return false;
		endif;
		$query = sprintf("SELECT * FROM `%saccounts` WHERE `username` LIKE '%%%s%%' LIMIT 1",$this->xf->config['MySQL']['prefix'],$this->xf->mysql->real_escape_string($username));
		$user = $this->xf->mysql->query($query);
		if(!$user->num_rows)
		{
		    $insUserQuery = sprintf("INSERT INTO `%saccounts` (`username`,`password`) VALUES ('%s','%s')",
		        $this->xf->config['MySQL']['prefix'],
		        $this->xf->mysql->real_escape_string($username),
		        $this->xf->mysql->real_escape_string($password)
		    );
			$this->xf->mysql->query($insUserQuery);
		}
		else
		{
			$this->Error = true;
			$this->LastError = 'Account already exists';
		}
	}
	
	private function _authByID($id)
	{
		if(!is_numeric($id)) return false;
		$this->mysql->query("SELECT * FROM `authme` WHERE `id` = " . $id . " LIMIT 1");
		if($this->mysql->isNext()):
			$dbUser = $this->mysql->getNext();
			$this->isAuthed = true;
			$this->isFormAuthed = false;
			$this->username = $dbUser['username'];
			$this->dbID = $dbUser['id'];
			$this->key = $dbUser['key'];
			$this->_flags();
			return $this->isAuthed;
		endif;
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
		if(!empty($cookie)):
			$this->mysql->query("SELECT * FROM `authme` WHERE `key` = '" . MySQL::escape($cookie) ."' LIMIT 1");
			if($this->mysql->isNext()):
				$dbUser = $this->mysql->getNext();
				$this->isAuthed = true;
				$this->isFormAuthed = false;
				$this->username = $dbUser['username'];
				$this->dbID = $dbUser['id'];
				$this->key = $dbUser['key'];
				$this->_flags();
				return $this->isAuthed;
			endif;
		endif;
		$this->isAuthed = false;
		$this->isFormAuthed = false;
		return $this->isAuthed;
	}
	
	public function SetCookie()
	{
		if($this->isFormAuthed && $this->setCookie):
			$key = hash_hmac(Auth::Algorythm,$this->password,Auth::Pagesecret);
			setcookie(Auth::Cookie,$key,time() + 14 * 24 * 60 * 60,'/');
			$this->mysql->update('authme',array('key' => $key),'`id` = ' . $this->dbID);
			$this->key = $key;
			return true;
		endif;
		return false;
	}
	
	public function Logout()
	{
		if($this->isAuthed):
			$this->mysql->update('authme',array('key' => ''),'`id` = ' . $this->dbID);
			$this->isAuthed = false;
			$this->isFormAuthed = false;
			setcookie(Auth::Cookie,FALSE);
		endif;
	}
	
	public function AuthByForm($username,$password)
	{
		$this->mysql->query("SELECT * FROM `authme` WHERE `username` = LOWER('" . MySQL::escape($username) . "') AND `password` = MD5('" . MySQL::escape($password) . "') LIMIT 1");
		if($this->mysql->isNext()):
			$this->isAuthed = true;
			$this->isFormAuthed = true;
			$dbUser = $this->mysql->getNext();
			$this->username = $dbUser['username'];
			$this->password = $password;
			$this->dbID = $dbUser['id'];
			$this->_flags();
			return $this->isAuthed;
		else:
			$this->isAuthed = false;
			$this->isFormAuthed = false;
			return $this->isAuthed;
		endif;
	}
	
	public function SaveChanges()
	{
		if($this->isAuthed):
			foreach($this->Flags as $key => $data):
				$this->mysql->query("SELECT `id`,`key` FROM `auth_flags` WHERE `id` = " . $this->dbID . " AND `key` = LOWER('" . $key . "') LIMIT 1");
				if($this->mysql->isNext()):
					$this->mysql->update('auth_flags',array('value'=>$data->value,'type'=>$data->type,'data'=>$data->data),"`id` = " . $this->dbID . " AND `key` = LOWER('" . $key . "')");
				else:
					$this->mysql->insert('auth_flags',array('id'=>$this->dbID,'key'=>strtolower($key),'value'=>$data->value,'type'=>$data->type,'data'=>$data->data));
				endif;
			endforeach;
		endif;
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
		if(isset($this->Flags->{$flag})):
			if(is_numeric($set)):
				$this->Flags->{$flag}->value = $this->Flags->{$flag}->data[$set];
			else:
				if(in_array($set,$this->Flags->{$flag}->data))
					$this->Flags->{$flag}->value = $set;
			endif;
		endif;
		return false;
	}
	
	public function SetFlag($flag,$value)
	{
		if(isset($this->Flags->{$flag})):
			switch($this->Flags->{$flag}->type):
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
			endswitch;
		endif;
		return false;
	}
	
	public function AddFlag($flag,$type=Flag::State,$value=true)
	{
		if(!isset($this->Flags->{$flag})):
			$this->Flags->{$flag} = new stdClass;
			switch($type):
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
			endswitch;
		endif;	
		return false;
	}
	
	private function _flags()
	{
		if($this->isAuthed):
			$this->Flags = new stdClass;
			$this->mysql->query("SELECT * FROM `auth_flags` WHERE `id` = " . $this->dbID);
			while($this->mysql->isNext()):
				$pair = $this->mysql->getNext();
				if($pair['type'] == 'enum'):
					$pair['data'] = explode(',',$pair['data']);
				endif;
				$this->Flags->{$pair['key']} = (object)$pair;
			endwhile;
		endif;
	}
}