<?php

$app->get('/product/all',function() use($app) {
	global $fpdo;
	try {
		
		$result = $fpdo->from('product')->execute();
		$res = $result->fetchAll(PDO::FETCH_OBJ);
		$newRes = array();
		foreach ($res as $r) {
			$r->id = encriptar($r->id);
			array_push($newRes,$r);
		}
		$app->response->headers->set('Content-type','application/json');
		$app->response->headers->set('Access-Control-Allow-Origin','*');
		$app->response->status(200);
		$app->response->body(json_encode($newRes));
	}catch(PDOException $e) {
		echo 'Error: '.$e->getMessage();
	}
});
$app->get('/product/all/inverse',function() use($app) {
	global $fpdo;
	try {
		$conex = getConexion();

		$result = $fpdo->from('product')->orderBy('id DESC')->execute();
		$res = $result->fetchAll(PDO::FETCH_OBJ);
		$newRes = array();
		foreach ($res as $r) {
			$r->id = encriptar($r->id);
			array_push($newRes,$r);
		}
		$app->response->headers->set('Content-type','application/json');
		$app->response->headers->set('Access-Control-Allow-Origin','*');
		$app->response->status(200);
		$app->response->body(json_encode($newRes));
	}catch(PDOException $e) {
		echo 'Error: '.$e->getMessage();
	}
});
$app->get('/product/:id_warehouse/:limit/:offset',function($idwarehouse,$limit,$offset) use($app) {
	global $fpdo;
	$idd = desencriptar($idwarehouse);
	try {
		$conex = getConexion();
		$sql = 'CALL allProduct(?,?,?)';
		$query = $conex->prepare($sql);
		$query->execute(
			array(
				$idd,
				$limit,
				$offset
			)
		);
		
		$res = $query->fetchAll(PDO::FETCH_OBJ);
		$newRes = array();
		foreach ($res as $r) {
			$r->id = encriptar($r->id);
			array_push($newRes,$r);
		}
		$app->response->headers->set('Content-type','application/json');
		$app->response->headers->set('Access-Control-Allow-Origin','*');
		$app->response->status(200);
		$app->response->body(json_encode($newRes));
	}catch(PDOException $e) {
		echo 'Error: '.$e->getMessage();
	}
});
$app->get('/product/:id',function($id) use($app) {
	global $fpdo;
	$idd = desencriptar($id);
	try {
		$result = $fpdo->from('product')->where('id',$idd)->execute();
		$result->execute();
		$res = $result->fetchObject();
		if($res==""){
			$msg = "El elemento que busca fue eliminado o quizas no existe.";
			$status = false;
		}
		else{
			$res->id = encriptar($res->id);
			$msg = "Encontrado con éxito";
			$status = true;
		}
		$conex = null;

		$app->response->headers->set('Content-type','application/json');
		$app->response->headers->set('Access-Control-Allow-Origin','*');
		$app->response->status(200);
		$app->response->body(json_encode(array('response'=>$res,'msg'=>$msg,'status'=> $status)));
	}catch(PDOException $e) {
		echo 'Error: '.$e->getMessage();
	}
});
/* new */
$app->post("/product/new",function() use($app) {
	global $fpdo;
	$objDatos = json_decode(file_get_contents("php://input"));

	$image = $objDatos->image;
	$name = $objDatos->name;
	$quantity = $objDatos->quantity;
	$price = $objDatos->price;
	$barcode = $objDatos->barcode;
	$vip = $objDatos->vip;
	$userId = $objDatos->id_user;
	$warehouseId = $objDatos->id_warehouse;

	$picture_url = "default.jpg";

	try {
		$result = $fpdo->from('product')->where('name',$name)->execute();
		$res = $result->rowCount();	

		if($res == 0 && $vip == getCode()){

			/* upload image */
			if(isset($image)){
				$decoded_image = base64_decode($image);
				$path = "upload/";
				$hoy = getdate();
				$texto = join('.',$hoy);
				$picture_url = md5($texto.$name).'.jpg';
				$path = $path.$picture_url;
				$file = fopen($path, 'wb');
				$is_written = fwrite($file,$decoded_image);
				fclose($file);
				if($is_written > 0){
					echo 'se subio con exito';
				}
				else{
					echo 'hubo un error';
				}
			}
			$conex = getConexion();
			$sql = 'CALL insertProduct(?,?,?,?,?,?,?)';
			$query = $conex->prepare($sql);
			$query->execute(
				array(
					$name,
					$quantity,
					$price,
					$picture_url,
					$barcode,
					desencriptar($userId),
					desencriptar($warehouseId)
				)
			);
			$res = encriptar($query->fetchObject()->idInsertado);//obtenemos ID
			$status = true; // insertado con exito
			$msg = "Producto insertado con éxito";
		}
		else{
			if($vip != getCode()){
				$res = 0;
				$status = false; // ya existe
				$msg = "Usted no tiene permisos para registrar";
			}else{
				$res = 0;
				$status = false; // ya existe
				$msg = "El Producto ya existe";
			}
		}
		$conex = null;
		$app->response->headers->set("Content-type","application/json");
		$app->response->headers->set('Access-Control-Allow-Origin','*');
		$app->response->status(200);
		$app->response->body(json_encode(array("id"=>$res,"msg"=>$msg,"status"=>$status)));

	}catch(PDOException $e) {
		echo "Error: ".$e->getMessage();
	}
});
/* edit */
$app->post("/product/edit",function() use($app) {
	global $fpdo;
	$jsonmessage = \Slim\Slim::getInstance()->request();
  	$objDatos = json_decode($jsonmessage->getBody());

  	$idd = desencriptar($objDatos->id);
	$name = $objDatos->name;
	$quantity = $objDatos->quantity;
	$picture_url = $objDatos->picture_url;
	$barcode = $objDatos->barcode;
	$vip = $objDatos->vip;
	$res = 0;

	try {
		if($vip == getCode()){
			$result = $fpdo->from('product')->where('name = ? AND id != ?',$name,$idd)->execute();
			$res = $result->rowCount();
			if($res == 0){
				$set = array(
							'name'=>$name,
							'quantity'=>$quantity,
							'picture_url'=>$picture_url,
							'barcode'=>$barcode);

				$result = $fpdo->update('product',$set,$idd)->execute();
				$status = true; // insertado con exito
				$msg = "Modificado con éxito";
			}
			else{
				$status = false; // ya existe
				$msg = "No se pudo actualizar porque el nombre de producto ya existe";
			}
		}else{
			$msg = "Usted no tiene permisos para registrar";
			$status = false;
		}

		$app->response->headers->set('Content-type','application/json');
		$app->response->status(200);
		$app->response->body(json_encode(array("msg"=>$msg,"status"=>$status)));
	}catch(PDOException $e) {
		echo "Error: ".$e->getMessage();
	}	
});

/* delete */
$app->post('/product/delete',function() use($app) {
	global $fpdo;
	$jsonmessage = \Slim\Slim::getInstance()->request();
  	$objDatos = json_decode($jsonmessage->getBody());

  	$vip = $objDatos->vip;
	$idd = desencriptar($objDatos->id);
	try {
		if($vip == getCode()){
			$result = $fpdo->deleteFrom('product')->where('id',$idd)->execute();
			$msg = "Eliminado";
			$status = true;
		}
		else{
			$msg = "Usted no tiene permisos para eliminar";
			$status = false;
		}

		$app->response->headers->set('Content-type','application/json');
		$app->response->headers->set('Access-Control-Allow-Origin','*');
		$app->response->status(200);
		$app->response->body(json_encode(array('msg'=>$msg,'$status'=>$status)));
	}catch(PDOException $e) {
		echo 'Error: '.$e->getMessage();
	}
});

?>