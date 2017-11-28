<?php

function getConexion(){
	try{
		$usuario = "root";
		$pwd = "";
		$conex = new PDO("mysql:host=localhost;dbname=toybox_db;charset=utf8",$usuario,$pwd,array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
		$conex->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

	}catch(PDOException $e){
		echo "Error: ".$e->getMessage();
	}
	return $conex;
}

?>