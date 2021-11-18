<?php

	abstract class BD{
	
		abstract function connect();
		abstract function query($querySql = "");
		abstract function next($queryResult = null);
		abstract function escapeString($aString);
		abstract function numRows();
		
	}

?>
