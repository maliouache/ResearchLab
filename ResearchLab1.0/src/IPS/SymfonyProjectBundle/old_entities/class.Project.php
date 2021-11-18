<?php

class Project {
    var $id=-1;
    var $name="";
    var $importance="";
    var $deadline="";
    var $domain="";
    var $comment="";

    function __construct($BD,$idProject = -1)
	{	// construct a project
		$this->id = $idProject;
		if ($this->id != -1 && is_numeric($this->id)){
			$this->load($BD);
		}
	}
    
    function isLoaded(){
		return ($this->id != -1);
    }
    
    function load($BD)
	{

		// retrieve the project
		$sql = "SELECT * FROM PROJECT WHERE ID = " . $this->id;
		$res = $BD->query($sql);
		if ( $BD->numRows == 0 )
		{
			$this->id = -1;
			return null;
        }
        else {
            $BD->next($res);
            $row = $BD->result;
            $this->init($row->ID, $row->NAME, $row->IMPORTANCE, $row->DEADLINE, $row->DOMAIN, $row->COMMENT);
        }
    }
    
    function init($name,$importance,$deadline,$domain,$comment){
        // $this->id=$id;
        $this->name=$name;
        $this->importance=$importance;
        $this->deadline=$deadline;
        $this->domain=$domain;
        $this->comment=$comment;
    }

    function save($BD){
		$sql="";
		if($this->id==-1)
		{
			$sql.='INSERT INTO PROJECT(NAME,IMPORTANCE,DEADLINE,DOMAIN,COMMENT) VALUES
            ("'.$this->name.'",
            "'.$this->importance.'",
            "'.$this->deadline.'",
            "'.$this->domain.'",
            "'.$this->comment.'");';
		}
		else
		{
			$sql.="UPDATE PROJECT SET "
				. "NAME=".$this->name.", "
				. "IMPORTANCE=".$this->importance.", "
				. "DEADLINE=".$this->deadline.", "
				. "DOMAIN=".$this->domain.", "
				. "COMMENT='".$this->comment."' WHERE ID = " . $this->id;
		}
		$BD->query($sql);
		if ($BD->insertId != 0)
			$this->id = $BD->insertId;
		return $sql;
    }

}