<?php

    if ($_SERVER["REMOTE_ADDR"] == "127.0.0.1") {
        $mysqlHost = "localhost";
        $mysqlBD = "Research_Platform";
        $mysqlUser = "root";
        $mysqlPassword = "mercilam";
        }  
    else {
        $mysqlHost = "localhost";
        $mysqlBD = "Research_Platform";
        $mysqlUser = "root";
        $mysqlPassword = "mercilam";
    }

    include_once('class.Mysql.php');
    
    @session_start();
    $h=$this->container->getParameter('database_host');
    $db=$this->container->getParameter('database_name');
    $u=$this->container->getParameter('database_user');
    $p=$this->container->getParameter('database_password');
    $BD = new MySQL($h,$db,$u,$p);
?>