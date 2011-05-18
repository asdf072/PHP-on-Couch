<?php

/****
* Usage:
* Instead of -- $client = new couchClient($dsn, 'songs_db', $opts);
* Use -- $client = couchManager::getInstance()->createClient($dsn, 'songs_db', $opts);
* Retrieve client from anywhere -- $client = couchManager::getInstance()->getClient('songs_db');
*/

namespace PhpOnCouch;

class couchManager
{
	private $_client_instances = array();
	private static $_self_instance = NULL;
	
	private function __construct(){}
	
	public static function getInstance()
	{
		if(is_null(self::$_self_instance))
		{
			self::$_self_instance = new couchManager;
		}
		return self::$_self_instance;
	}
	
	public function registerClient(couchClient $client)
	{
		if(!($client instanceof couchClient))
		{
			throw new couchManagerException("Registration is limited to couchClient.");
		}
		$db_name = $client->getDatabaseName();
		$this->_client_instances[$db_name] = $client;
	}
	
	public function createClient($dsn, $dbname, $options = array())
	{
		// Maybe just return the client if already registered?
		if(isset($this->_client_instances[$dbname]))
		{
			throw new couchManagerException("Client for $dbname already registered. " .
				"Use couchManager::getClient($dbname) instead.");
		}
		$client = new couchClient($dsn, $dbname, $options);
		$this->registerClient($client);
		return $client;
	}
	
	/****
	* If no $db_name, assumes one connection is being used.
	*/
	public function getClient($db_name = NULL)
	{
		if($db_name)
		{
			if(!isset($this->_client_instances[$db_name]))
			{
				throw new couchManagerException("No client available for $db_name database.");
			}
			return $this->_client_instances[$db_name];
		}
		elseif(count($this->_client_instances) == 1)
		{
			return end($this->_client_instances);
		}
		$exception_msg = (count(self::$_client_instances) > 1) ?
			"You must specify a database name if using more than one connection." :
			"No client connections were established yet.";
		throw new couchManagerException($exception_msg);
	}
}

class couchManagerException extends \Exception{}