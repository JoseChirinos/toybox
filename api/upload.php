<?php
	session_start();
	require "model/conexion.php";

	header('Content-type:bitmap; charset=utf-8');
	/*
	$fpdo = new FluentPDO(getConexion());
	$result = $fpdo->from('user')->execute();
	$res = $result->fetchAll(PDO::FETCH_OBJ);
	$newRes = array();
	foreach ($res as $r) {
		echo $r->name;
	}
	*/
	if(isset($_POST['encoded_string'])){
		$encoded_string = $_POST['encoded_string'];
		$image_name = $_POST['image_name'];

		$decoded_string = base64_decode($encoded_string);

		$path = 'upload/'.$image_name;

		$file = fopen($path, 'wb');

		$is_written = fwrite($file,$decoded_string);

		fclose($file);

		if($is_written > 0){
			echo 'se subio con exito';
		}
		else{
			echo 'hubo un error';
		}
	}
 ?>