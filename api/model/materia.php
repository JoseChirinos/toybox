<?php

$app->get('/materia/all',function() use($app) {
	try {
		$conex = getConexion();

		$result = $conex->prepare("SELECT * FROM materia WHERE status = 1");
		
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
$app->get('/materia/all/inverse',function() use($app) {
	try {
		$conex = getConexion();

		$result = $conex->prepare("SELECT * FROM materia WHERE status = 1 ORDER BY id DESC ");
		
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
$app->get('/materia/all/down',function() use($app) {
	try {
		$conex = getConexion();

		$result = $conex->prepare("SELECT * FROM materia WHERE status = 0");
		
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
$app->get('/materia/:id',function($id) use($app) {
	try {
		$conex = getConexion();

		$result = $conex->prepare("SELECT * FROM materia WHERE id='$id' AND status = 1;");

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
		$app->response->body(json_encode(array('msg'=>$msg,'response'=>$res,'status'=> $ok)));
	}catch(PDOException $e) {
		echo 'Error: '.$e->getMessage();
	}
})->conditions(array('id'=>'[0-9]{1,11}'));

$app->post("/materia",function() use($app) {
	$objDatos = json_decode(file_get_contents("php://input"));

	$name = $objDatos->name;
	$detail = $objDatos->detail;

	try {
		$conex = getConexion();

		$result = $conex->prepare("SELECT id FROM materia WHERE name = '$name'");
		$result->execute();
		$res = $result->rowCount();

		if($res == 0){
			$result = $conex->prepare("INSERT INTO materia VALUES (NULL, '$name','$detail','1');");
			$result->execute();
			$res = $conex->lastInsertId(); /*obtenemos ID*/
			$ok = true; // insertado con exito
			$msg = "Materia guardada con éxito";
		}
		else{
			$res = 0;
			$ok = false; // ya existe
			$msg = "La materia ya existe";
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

$app->put("/materia/:id",function($id) use($app) {
	$jsonmessage = \Slim\Slim::getInstance()->request();
  	$objDatos = json_decode($jsonmessage->getBody());

	$name = $objDatos->name;
	$detail = $objDatos->detail;

	try {
		$conex = getConexion();

		$result = $conex->prepare("SELECT id FROM materia WHERE name = '$name' AND id != '$id'");
		$result->execute();
		$res = $result->rowCount();

		if($res == 0){
			$result = $conex->prepare("UPDATE materia SET name='$name', detail='$detail' WHERE id='$id';");
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

$app->put("/materia/:id/up",function($id) use($app) {
	$jsonmessage = \Slim\Slim::getInstance()->request();
  	$objDatos = json_decode($jsonmessage->getBody());

	$status = 1;

	try {
		$conex = getConexion();
		$result = $conex->prepare("UPDATE materia SET status=$status WHERE id='$id';");

		$result->execute();
		$conex = null;

		$app->response->headers->set('Content-type','application/json');
		$app->response->status(200);
		$app->response->body(json_encode(array('status'=>true)));

	}catch(PDOException $e) {
		echo "Error: ".$e->getMessage();
	}
});

$app->put("/materia/:id/down",function($id) use($app) {
	$jsonmessage = \Slim\Slim::getInstance()->request();
  	$objDatos = json_decode($jsonmessage->getBody());

	$status = 0;

	try {
		$conex = getConexion();
		$result = $conex->prepare("UPDATE materia SET status=$status WHERE id='$id';");		
		$result->execute();
		$conex = null;

		$app->response->headers->set('Content-type','application/json');
		$app->response->status(200);
		$app->response->body(json_encode(array('status'=>true)));

	}catch(PDOException $e) {
		echo "Error: ".$e->getMessage();
	}
});

$app->delete('/materia/:id',function($id) use($app) {
	try {
		$conex = getConexion();

		$result = $conex->prepare("DELETE FROM materia WHERE id='$id';");
		$result->execute();

		$conex = null;

		$app->response->headers->set('Content-type','application/json');
		$app->response->headers->set('Access-Control-Allow-Origin','*');
		$app->response->status(200);
		$app->response->body(json_encode(array('statuss'=>true)));
	}catch(PDOException $e) {
		echo 'Error: '.$e->getMessage();
	}
});

?>