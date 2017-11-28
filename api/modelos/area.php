<?php

$app->get('/area/all',function() use($app) {
	try {
		$conex = getConexion();

		$result = $conex->prepare("SELECT * FROM area WHERE status = 1");
		
		$result->execute();
		$res = $result->fetchAll(PDO::FETCH_OBJ);
		$conex = null;
		$app->response->headers->set('Content-type','application/json');
		$app->response->headers->set('Access-Control-Allow-Origin','*');
		$app->response->status(200);
		$app->response->body(json_encode($res));
	}catch(PDOException $e) {
		echo 'Error: '.$e->getMessage();
	}
});
$app->get('/area/all/inverse',function() use($app) {
	try {
		$conex = getConexion();

		$result = $conex->prepare("SELECT * FROM area WHERE status = 1 ORDER BY id DESC");
		
		$result->execute();
		$res = $result->fetchAll(PDO::FETCH_OBJ);
		$conex = null;
		$app->response->headers->set('Content-type','application/json');
		$app->response->headers->set('Access-Control-Allow-Origin','*');
		$app->response->status(200);
		$app->response->body(json_encode($res));
	}catch(PDOException $e) {
		echo 'Error: '.$e->getMessage();
	}
});
$app->get('/area/all/down',function() use($app) {
	try {
		$conex = getConexion();

		$result = $conex->prepare("SELECT * FROM area WHERE status = 0");
		
		$result->execute();
		$res = $result->fetchAll(PDO::FETCH_OBJ);
		$conex = null;
		$app->response->headers->set('Content-type','application/json');
		$app->response->headers->set('Access-Control-Allow-Origin','*');
		$app->response->status(200);
		$app->response->body(json_encode($res));
	}catch(PDOException $e) {
		echo 'Error: '.$e->getMessage();
	}
});
$app->get('/area/:id',function($id) use($app) {
	try {
		$conex = getConexion();

		$result = $conex->prepare("SELECT * FROM area WHERE id='$id' AND status = 1;");

		$result->execute();
		$res = $result->fetchObject();
		if($res==""){
			$msg = "El elemento que busca fue eliminado o quizas no existe.";
			$ok = false;
		}
		else{
			$msg = "Encontrado con éxito";
			$ok = true;
		}
		$conex = null;

		$app->response->headers->set('Content-type','application/json');
		$app->response->headers->set('Access-Control-Allow-Origin','*');
		$app->response->status(200);
		$app->response->body(json_encode(array('msg' => $msg,'response'=>$res,'status'=>$ok)));
	}catch(PDOException $e) {
		echo 'Error: '.$e->getMessage();
	}
});

$app->post("/area",function() use($app) {
	$objDatos = json_decode(file_get_contents("php://input"));

	$name = $objDatos->name;
	$detail = $objDatos->detail;

	try {
		$conex = getConexion();

		$result = $conex->prepare("SELECT id FROM area WHERE name = '$name'");
		$result->execute();
		$res = $result->rowCount();

		if($res == 0){
			$result = $conex->prepare("INSERT INTO area VALUES (NULL, '$name','$detail','1');");
			$result->execute();
			$res = $conex->lastInsertId(); /*obtenemos ID*/
			$ok = true; // insertado con exito
			$msg = "Area insertada con éxito";
		}
		else{
			$res = 0;
			$ok = false; // ya existe
			$msg = "El area ya existe";
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

$app->put("/area/:id",function($id) use($app) {
	$jsonmessage = \Slim\Slim::getInstance()->request();
  	$objDatos = json_decode($jsonmessage->getBody());

	$name = $objDatos->name;
	$detail = $objDatos->detail;

	try {
		$conex = getConexion();
		$result = $conex->prepare("SELECT id FROM area WHERE name = '$name' AND id != '$id'");
		$result->execute();
		$res = $result->rowCount();

		if($res == 0){
			$result = $conex->prepare("UPDATE area SET name='$name', detail='$detail' WHERE id='$id';");
			$result->execute();
			$ok = true; // insertado con exito
			$msg = "Modificado con éxito";
		}
		else{
			$ok = false; // ya existe
			$msg = "La materia ya existe";
		}
		$conex = null;

		$app->response->headers->set('Content-type','application/json');
		$app->response->status(200);
		$app->response->body(json_encode(array("status"=>$ok,"msg"=>$msg)));

	}catch(PDOException $e) {
		echo "Error: ".$e->getMessage();
	}
});

$app->put("/area/:id/up",function($id) use($app) {
	$jsonmessage = \Slim\Slim::getInstance()->request();
  	$objDatos = json_decode($jsonmessage->getBody());

	$status = 1;

	try {
		$conex = getConexion();
		$result = $conex->prepare("UPDATE area SET status=$status WHERE id='$id';");

		$result->execute();
		$conex = null;

		$app->response->headers->set('Content-type','application/json');
		$app->response->status(200);
		$app->response->body(json_encode(array('status'=>true)));

	}catch(PDOException $e) {
		echo "Error: ".$e->getMessage();
	}
});

$app->put("/area/:id/down",function($id) use($app) {
	$jsonmessage = \Slim\Slim::getInstance()->request();
  	$objDatos = json_decode($jsonmessage->getBody());

	$status = 0;

	try {
		$conex = getConexion();
		$result = $conex->prepare("UPDATE area SET status=$status WHERE id='$id';");

		$result->execute();
		$conex = null;

		$app->response->headers->set('Content-type','application/json');
		$app->response->status(200);
		$app->response->body(json_encode(array('status'=>true)));

	}catch(PDOException $e) {
		echo "Error: ".$e->getMessage();
	}
});

$app->delete('/area/:id',function($id) use($app) {
	try {
		$conex = getConexion();

		$result = $conex->prepare("DELETE FROM area WHERE id='$id';");
		$result->execute();
		$result = $conex->prepare("DELETE FROM materia_area WHERE id_area ='$id';");
		$result->execute();
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