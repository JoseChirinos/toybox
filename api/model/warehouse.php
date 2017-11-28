<?php

$app->get('/warehouse/all',function() use($app) {
	global $fpdo;
	try {
		
		$result = $fpdo->from('warehouse')->execute();
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
$app->get('/warehouse/all/inverse',function() use($app) {
	global $fpdo;
	try {
		$conex = getConexion();

		$result = $fpdo->from('warehouse')->orderBy('id DESC')->execute();
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
$app->get('/warehouse/:iduser/:limit/:offset',function($iduser,$limit,$offset) use($app) {
	global $fpdo;
	$idd = desencriptar($iduser);
	try {
		$conex = getConexion();
		$sql = 'CALL allWarehouse(?,?,?)';
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
$app->get('/warehouse/:id',function($id) use($app) {
	global $fpdo;
	$idd = desencriptar($id);
	try {
		$result = $fpdo->from('warehouse')->where('id',$idd)->execute();
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
$app->post("/warehouse/new",function() use($app) {
	global $fpdo;
	$objDatos = json_decode(file_get_contents("php://input"));

	$name = $objDatos->name;
	$address = $objDatos->address;
	$gps = $objDatos->gps;
	$max_capacity = $objDatos->max_capacity;
	$min_capacity = $objDatos->min_capacity;
	$vip = $objDatos->vip;
	$userId = $objDatos->userid;

	try {
		$result = $fpdo->from('warehouse')->where('name',$name)->execute();
		$res = $result->rowCount();	

		if($res == 0 && $vip == getCode()){
			$conex = getConexion();
			$sql = 'CALL insertWarehouse(?,?,?,?,?,?)';
			$query = $conex->prepare($sql);
			$query->execute(
				array(
					desencriptar($userId),
					$name,
					$address,
					$gps,
					$max_capacity,
					$min_capacity
				)
			);
			$res = encriptar($query->fetchObject()->idInsertado);/*obtenemos ID*/
			$status = true; // insertado con exito
			$msg = "Almacen insertado con éxito";
		}
		else{
			if($vip != getCode()){
				$res = 0;
				$status = false; // ya existe
				$msg = "Usted no tiene permisos para registrar";
			}else{
				$res = 0;
				$status = false; // ya existe
				$msg = "El Almacen ya existe";
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
$app->post("/warehouse/edit",function() use($app) {
	global $fpdo;
	$jsonmessage = \Slim\Slim::getInstance()->request();
  	$objDatos = json_decode($jsonmessage->getBody());

  	$idd = desencriptar($objDatos->id);
	$name = $objDatos->name;
	$address = $objDatos->address;
	$gps = $objDatos->gps;
	$max_capacity = $objDatos->max_capacity;
	$min_capacity = $objDatos->min_capacity;
	$vip = $objDatos->vip;
	$res = 0;

	try {
		if($vip == getCode()){
			$result = $fpdo->from('warehouse')->where('name = ? AND id != ?',$name,$idd)->execute();
			$res = $result->rowCount();
			if($res == 0){
				$set = array(
							'name'=>$name,
							'address'=>$address,
							'gps'=>$gps,
							'max_capacity'=>$max_capacity,
							'min_capacity'=>$min_capacity);

				$result = $fpdo->update('warehouse',$set,$idd)->execute();
				$status = true; // insertado con exito
				$msg = "Modificado con éxito";
			}
			else{
				$status = false; // ya existe
				$msg = "No se pudo actualizar porque el nombre de almacen ya existe";
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
$app->post('/warehouse/delete',function() use($app) {
	global $fpdo;
	$jsonmessage = \Slim\Slim::getInstance()->request();
  	$objDatos = json_decode($jsonmessage->getBody());

  	$vip = $objDatos->vip;
	$idd = desencriptar($objDatos->id);
	try {
		if($vip == getCode()){
			$result = $fpdo->deleteFrom('product')->where('id_warehouse',$idd)->execute();
			$result = $fpdo->deleteFrom('warehouse_branch')->where('id_warehouse',$idd)->execute();
			$result = $fpdo->deleteFrom('warehouse')->where('id',$idd)->execute();
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