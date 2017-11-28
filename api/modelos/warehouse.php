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
$app->get('/warehouse/:lim/:offset',function($lim,$offset) use($app) {
	global $fpdo;
	try {
		$result = $fpdo->from('warehouse')->limit($lim)->offset($offset)->orderBy('id DESC')->execute();
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
$app->get('/warehouse/:id',function($id) use($app) {
	global $fpdo;
	$idd = desencriptar($id);
	try {
		$result = $fpdo->from('warehouse')->where('id',$idd)->execute();
		$result->execute();
		$res = $result->fetchObject();
		if($res==""){
			$msg = "El elemento que busca fue eliminado o quizas no existe.";
			$ok = false;
		}
		else{
			$res->id = encriptar($res->id);
			$msg = "Encontrado con éxito";
			$ok = true;
		}
		$conex = null;

		$app->response->headers->set('Content-type','application/json');
		$app->response->headers->set('Access-Control-Allow-Origin','*');
		$app->response->status(200);
		$app->response->body(json_encode(array('msg'=>$msg,'response'=>$res,'status'=> $ok)));
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

	try {
		$result = $fpdo->from('warehouse')->where('name',$name)->execute();
		$res = $result->rowCount();	

		if($res == 0 && $vip == getCode()){
			$values = array( 'id'=>'null',
						'name'=>$name,
						'address'=>$address,
						'gps'=>$gps,
						'max_capacity'=>$max_capacity,
						'min_capacity'=>$min_capacity,
						'date_init'=>new FluentLiteral('NOW()'));
			$result = $fpdo->insertInto('warehouse',$values)->execute();
			$res = encriptar($result); /*obtenemos ID*/
			$ok = true; // insertado con exito
			$msg = "Almacen insertado con éxito";
		}
		else{
			if($vip != getCode()){
				$res = 0;
				$ok = false; // ya existe
				$msg = "Usted no tiene permisos para registrar";
			}else{
				$res = 0;
				$ok = false; // ya existe
				$msg = "El Almacen ya existe";
			}
		}
		
		$conex = null;
		$app->response->headers->set("Content-type","application/json");
		$app->response->headers->set('Access-Control-Allow-Origin','*');
		$app->response->status(200);
		$app->response->body(json_encode(array("id"=>$res,"status"=>$ok,"msg"=>$msg)));
	}catch(PDOException $e) {
		echo "Error: ".$e->getMessage();
	}
});
/* edit */
$app->put("/warehouse/edit",function() use($app) {
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
			$result = $fpdo->from('user')->where('name = ? AND id != ?',$name,$idd)->execute();
			$res = $result->rowCount();
			if($res == 0){
				$set = array(
							'name'=>$name,
							'address'=>$address,
							'gps'=>$gps,
							'max_capacity'=>$max_capacity,
							'min_capacity'=>$min_capacity);

				$result = $fpdo->update('warehouse',$set,$idd)->execute();
				$ok = true; // insertado con exito
				$msg = "Modificado con éxito";
			}
			else{
				$ok = false; // ya existe
				$msg = "No se pudo actualizar porque el nombre de almacen ya existe";
			}
		}else{
			$msg = "Usted no tiene permisos para registrar";
			$status = false;
		}

		$app->response->headers->set('Content-type','application/json');
		$app->response->status(200);
		$app->response->body(json_encode(array("status"=>$ok,"msg"=>$msg)));
	}catch(PDOException $e) {
		echo "Error: ".$e->getMessage();
	}	
});

/* delete */
$app->delete('/warehouse/delete',function() use($app) {
	global $fpdo;
	try {
		$result = $fpdo->deleteFrom('warehouse', $id)->execute();
		$conex = null;

		$app->response->headers->set('Content-type','application/json');
		$app->response->headers->set('Access-Control-Allow-Origin','*');
		$app->response->status(200);
		$app->response->body(json_encode(array('status'=>true)));
	}catch(PDOException $e) {
		echo 'Error: '.$e->getMessage();
	}
});

?>