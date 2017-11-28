<?php
$app->get('/materia_area/all',function() use($app) {
	try {
		$conex = getConexion();

		$result = $conex->prepare("SELECT t1.id,t2.name,t1.percent,t1.number_questions, t1.id_materia, t1.id_area FROM materia_area as t1 INNER JOIN materia as t2 WHERE t1.id_materia = t2.id");
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
$app->get('/materia_area/:area/init',function($area) use($app) {
	$id = $area;
	try {
		$conex = getConexion();
		$area_query = $conex->prepare("SELECT name FROM area WHERE id = '$id'");
		
		$area_query->execute();
		$area = $area_query->fetchObject();

		$result = $conex->prepare("SELECT t1.id,t2.name,t1.percent,t1.number_questions, t1.id_materia, t1.id_area FROM materia_area as t1 INNER JOIN materia as t2 ON t1.id_materia = t2.id WHERE t1.id_area = '$id'");
		
		$result->execute();
		$res = $result->fetchAll(PDO::FETCH_OBJ);
		if($res==""){
			$msg = "Aun no hay materias.";
			$ok = false;
			$materias = array();
		}
		else{
			$materia_query = $conex->prepare("SELECT id,name FROM materia WHERE status = 1");		
			$materia_query->execute();
			$materias = $materia_query->fetchAll(PDO::FETCH_OBJ);
			$msg = "Encontrado con éxito";
			$ok = true;
		}
		$conex = null;

		$app->response->headers->set('Content-type','application/json');
		$app->response->headers->set('Access-Control-Allow-Origin','*');
		$app->response->status(200);
		$app->response->body(json_encode(array('msg' => $msg,'response'=>$res,'materias'=>$materias,'area'=>$area,'status'=>$ok)));
	}catch(PDOException $e) {
		echo 'Error: '.$e->getMessage();
	}
});

$app->post("/materia_area",function() use($app) {
	$objDatos = json_decode(file_get_contents("php://input"));

	$percent = $objDatos->percent;
	$number_questions = $objDatos->number_questions;
	$id_materia = $objDatos->id_materia;
	$id_area = $objDatos->id_area;

	try {
		$conex = getConexion();

		$result = $conex->prepare("INSERT INTO materia_area VALUES (NULL, '$percent','$number_questions','$id_materia','$id_area');");

		$result->execute();
		$res = $conex->lastInsertId(); /*obtenemos ID*/
		$conex = null;
		$app->response->headers->set("Content-type","application/json");
		$app->response->headers->set('Access-Control-Allow-Origin','*');
		$app->response->status(200);
		$app->response->body(json_encode(array("id"=>$res)));
	}catch(PDOException $e) {
		echo "Error: ".$e->getMessage();
	}
});

$app->put("/materia_area/:id",function($id) use($app) {
	$jsonmessage = \Slim\Slim::getInstance()->request();
  	$objDatos = json_decode($jsonmessage->getBody());

	$percent = $objDatos->percent;
	$number_questions = $objDatos->number_questions;

	try {
		$conex = getConexion();
		$result = $conex->prepare("UPDATE materia_area SET percent='$percent', number_questions='$number_questions' WHERE id='$id';");

		$result->execute();
		$conex = null;

		$app->response->headers->set('Content-type','application/json');
		$app->response->status(200);
		$app->response->body(json_encode(array('status'=>true)));

	}catch(PDOException $e) {
		echo "Error: ".$e->getMessage();
	}
});

$app->delete('/materia_area/:id',function($id) use($app) {

	try {
		$conex = getConexion();

		$result = $conex->prepare("DELETE FROM materia_area WHERE id='$id';");
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