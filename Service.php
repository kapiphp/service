<?php

namespace Kapi\Service;

use PDO;

abstract class Service extends PDO {

	public function __construct($dsn, $username = null, $password = null, $options = null)
	{
		parent::__construct($dsn, $username, $password, $options);
		$this->exec('SET CHARACTER SET UTF8');

		$this->setAttribute(self::ATTR_STATEMENT_CLASS, array(__NAMESPACE__.'\\ServiceStatement'));
		$this->setAttribute(self::ATTR_ERRMODE, self::ERRMODE_EXCEPTION);
		$this->setAttribute(self::ATTR_DEFAULT_FETCH_MODE, self::FETCH_ASSOC);
	}

	public function setDatabase($database)
	{
		$this->query('USE ' . $database);
	}

	public function query($statement, $mode = self::ATTR_DEFAULT_FETCH_MODE, $arg3 = null, array $ctorargs = array())
	{
		$mode = $mode === self::ATTR_DEFAULT_FETCH_MODE ? $this->getAttribute(self::ATTR_DEFAULT_FETCH_MODE) : $mode;
		switch($mode){
			case self::FETCH_CLASS:
				return parent::query($statement, $mode, $arg3, $ctorargs);
				break;
			case self::FETCH_COLUMN:
			case self::FETCH_INTO:
			case self::FETCH_FUNC:
				return parent::query($statement, $mode, $arg3);
				break;
			default:
				return parent::query($statement, $mode);
				break;
		}
	}

	protected function _getResult($statement, $mode = self::ATTR_DEFAULT_FETCH_MODE, $arg3 = null, array $ctorargs = array())
	{
		$result = $this->query($statement, $mode, $arg3, $ctorargs)->fetchAll();

		if(is_array($result) && $result){
			if(!(count($result)-1)){
				$result = $result[0];
				if(!(count($result)-1)){
					$result = current($result);
				}
			}
		}

		return $result;
	}

	protected function _transaction($statement, array $input_parameters = array(), array $data_types = array())
	{
		try{
			$this->beginTransaction();

			$service_statement = $this->prepare($statement);
			$service_statement->bind($input_parameters, $data_types);
			$service_statement->execute();

			$this->commit();
		}catch(\PDOException $e){
			$this->rollBack();
		}
	}

	protected function _genUid()
	{
		return uniqid(rand(), true);
	}
}