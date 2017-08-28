<?php

namespace Kapi\Service;

use PDOStatement;

class ServiceStatement extends PDOStatement
{
    /**
     * @param array $parameters
     * @param array $types
     */
	public function bind(array $parameters, array $types = array())
	{
		if (!$parameters) return;

		foreach ($parameters as $parameter => $value) {
			$this->bindValue(is_int($parameter) ? $parameter + 1 : $parameter, $value, $types[$parameter] ?: null);
		}
	}
}