<?php
	//for the database
	include_once('class.BD.php');

	class MySQL extends BD{
	
		var $dbLink = null;	//Connection à la base de données
		var $connected = false;
		var $query = "";	//Dernière requete passée
		var $result = "";	//Derniers résultats
		var $numRows = 0;
		var $insertId = 0;	//InsertId de la dernière requete
		var $mysqlHost;
		var $mysqlUser;
		var $mysqlPassword;
		var $mysqlBD;
		private $queryResult = false;	// résultat d'un query

		function __construct($h,$db,$u,$p){
			$this->mysqlHost=$h;
			$this->mysqlDB=$db;
			$this->mysqlUser=$u;
			$this->mysqlPassword=$p;
			//Constructeur de l'objet
			$this->connected = $this->connect();
			if (!$this->connected)
				die(display_error("Impossible de se connecter à la base de données MySQL"));
		}

		function __destruct()
		{
			if ($this->connected)
				mysqli_close($this->dbLink);
		}
		
		function connect(){
			//Méthode qui connecte php à la base de données MySQL
			//Retourne true en cas de succès
			// global $mysqlHost, $mysqlBD, $mysqlUser, $mysqlPassword;
			$this->dbLink = mysqli_connect($this->mysqlHost, $this->mysqlUser, $this->mysqlPassword);
			if ($this->dbLink == FALSE)
				return false;
			return mysqli_select_db($this->dbLink,$this->mysqlDB);
			//mysql_select_db retourne vrai en cas de succès
		}

		function query($querySql = ""){
			//Méthode qui permet d'exécuter une requete MySQL
			//Retourne null en cas d'erreur

			if ($querySql == "")
				return null;
			$this->queryResult = mysqli_query($this->dbLink, $querySql);
			$this->query = $querySql;

			if ($this->queryResult === FALSE){
				die(display_error("Erreur MySQL : ".mysqli_error()."<br/><br/>$querySql<br/>"));
				return null;
			}

			if ($this->queryResult !== TRUE){
				//Résultat d'un SELECT, SHOW, DESCRIBE, EXPLAIN
				$this->numRows = mysqli_num_rows($this->queryResult);
			}else{
				//Résultat d'un UPDATE / INSERT /DELETE
				$this->result = mysqli_affected_rows($this->dbLink);
				$this->numRows = $this->result;
				$this->insertId = mysqli_insert_id($this->dbLink);
			}
			return $this->queryResult;
		}
		
		function next($queryResult = null)
		{	// enregistre dans result le prochain résultat d'un SELECT ou false si fini
			// retourne vrai si il reste des résultats
			// retourne false sinon
			if ($queryResult == null)
				$queryResult = $this->queryResult;
			if ( $row = mysqli_fetch_object($queryResult) )
			{
				$this->result = $row;
				return $row;
			}
			//else
			$this->result = false;
			return false;
		}

		function escapeString($aString){
			return mysqli_real_escape_string($this->dbLink,$aString);
		}

		function numRows(){
			return $this->numRows;
		}
	}

?>
