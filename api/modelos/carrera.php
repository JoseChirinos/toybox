<?php

$app->get('/carrera/all',function() use($app) {
	try {
		$conex = getConexion();

		$result = $conex->prepare("SELECT * FROM carrera");
		
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
$app->get('/carrera/all/inverse',function() use($app) {
	try {
		$conex = getConexion();

		$result = $conex->prepare("SELECT * FROM carrera ORDER BY id DESC");
		
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
$app->get('/carrera/all/init',function() use($app) {
	try {
		$conex = getConexion();

		$result = $conex->prepare("SELECT c.id, c.name, c.detail, c.id_area, a.name as name_area FROM carrera as c INNER JOIN area as a ON c.id_area = a.id  ORDER BY c.id DESC");
		$result->execute();
		$res = $result->fetchAll(PDO::FETCH_OBJ);

		$area_query = $conex->prepare("SELECT id,name FROM area");
		$area_query->execute();
		$areas = $area_query->fetchAll(PDO::FETCH_OBJ);

		$conex = null;
		$app->response->headers->set('Content-type','application/json');
		$app->response->headers->set('Access-Control-Allow-Origin','*');
		$app->response->status(200);
		$app->response->body(json_encode(array("carreras"=>$res,"areas"=>$areas)));
	}catch(PDOException $e) {
		echo 'Error: '.$e->getMessage();
	}
});
$app->get('/carrera/:id',function($id) use($app) {
	try {
		$conex = getConexion();

		$result = $conex->prepare("SELECT * FROM carrera WHERE id='$id';");

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

$app->post("/carrera",function() use($app) {
	$objDatos = json_decode(file_get_contents("php://input"));

	$name = $objDatos->name;
	$detail = $objDatos->detail;
	$id_area = $objDatos->id_area;

	try {
		$conex = getConexion();

		$result = $conex->prepare("SELECT id FROM carrera WHERE name = '$name'");
		$result->execute();
		$res = $result->rowCount();

		if($res == 0){
			$result = $conex->prepare("INSERT INTO carrera VALUES (NULL, '$name','$detail','$id_area');");
			$result->execute();
			$res = $conex->lastInsertId(); /*obtenemos ID*/
			$ok = true; // insertado con exito
			$msg = "Carrera insertada con éxito";
		}
		else{
			$res = 0;
			$ok = false; // ya existe
			$msg = "La carrera ya existe";
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

$app->put("/carrera/:id",function($id) use($app) {
	$jsonmessage = \Slim\Slim::getInstance()->request();
  	$objDatos = json_decode($jsonmessage->getBody());

	$name = $objDatos->name;
	$detail = $objDatos->detail;
	$id_area = $objDatos->id_area;

	try {
		$conex = getConexion();
		$result = $conex->prepare("UPDATE carrera SET name='$name', detail='$detail', id_area ='$id_area' WHERE id='$id';");

		$result->execute();
		$conex = null;

		$app->response->headers->set('Content-type','application/json');
		$app->response->status(200);
		$app->response->body(json_encode(array('status'=>true)));

	}catch(PDOException $e) {
		echo "Error: ".$e->getMessage();
	}
});

$app->delete('/carrera/:id',function($id) use($app) {
	try {
		$conex = getConexion();

		$result = $conex->prepare("DELETE FROM carrera WHERE id='$id';");
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