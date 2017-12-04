<?php
/*
	//$hoy = getdate();
	//$texto = join('.',$hoy);
	//$new_dir = md5($texto).substr($name,-5);
	
	echo $texto;
*/
	// eliminar un archivo en php
	/*unlink('upload/'.'1.png');*/
	require 'vendor/autoload.php';
	require "model/conexion.php";
	$fpdo = new FluentPDO(getConexion());
	$idd = 1;
	$result = $fpdo->from('product')->where('id',$idd)->execute();
	$res = $result->fetchObject();
	if($res){
		print_r($res->picture_url);
	}
 ?>