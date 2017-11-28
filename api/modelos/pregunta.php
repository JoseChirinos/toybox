<?php

$app->get('/pregunta/all',function() use($app) {
	try {
		$conex = getConexion();

		$result = $conex->prepare("SELECT * FROM pregunta WHERE status = 1");
		
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
$app->get('/pregunta/:materia/init',function($materia) use($app) {
	try {
		$conex = getConexion();

		$materia_query = $conex->prepare("SELECT name FROM materia WHERE id = '$materia'");
		$materia_query->execute();
		$name = $materia_query->fetchObject();

		$result = $conex->prepare("SELECT * FROM pregunta WHERE id_materia ='$materia'");

		$result->execute();
		$res = $result->fetchAll(PDO::FETCH_OBJ);

		$conex = null;

		$app->response->headers->set('Content-type','application/json');
		$app->response->headers->set('Access-Control-Allow-Origin','*');
		$app->response->status(200);
		$app->response->body(json_encode(array('response'=>$res,'materia'=>$name)));
	}catch(PDOException $e) {
		echo 'Error: '.$e->getMessage();
	}
})->conditions(array('id'=>'[0-9]{1,11}'));

$app->get('/pregunta/:id',function($id) use($app) {
	try {
		$conex = getConexion();

		$result = $conex->prepare("SELECT * FROM pregunta WHERE id='$id'");

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

$app->post("/pregunta",function() use($app) {
	$objDatos = json_decode(file_get_contents("php://input"));

	$question = $objDatos->question;
	$image = $objDatos->image;
	$option_a = $objDatos->option_a;
	$option_b = $objDatos->option_b;
	$option_c = $objDatos->option_c;
	$option_d = $objDatos->option_d;
	$image_a = $objDatos->image_a;
	$image_b = $objDatos->image_b;
	$image_c = $objDatos->image_c;
	$image_d = $objDatos->image_d;
	$answer = $objDatos->answer;
	$id_materia = $objDatos->id_materia;


	try {
		$conex = getConexion();

		$result = $conex->prepare("INSERT INTO pregunta VALUES (NULL, '$question','$image','$option_a','$image_a','$option_b','$image_b','$option_c','$image_c','$option_d','$image_d','$answer',NOW(),$id_materia,1);");
		$result->execute();
		$res = $conex->lastInsertId(); /*obtenemos ID*/
		$ok = true; // insertado con exito
		$msg = "Su pregunta ha sido guardada con éxito";
		
		$conex = null;
		$app->response->headers->set("Content-type","application/json");
		$app->response->headers->set('Access-Control-Allow-Origin','*');
		$app->response->status(200);
		$app->response->body(json_encode(array("id"=>$res,"status"=>$ok,"msg"=>$msg)));
	}catch(PDOException $e) {
		echo "Error: ".$e->getMessage();
	}
});

$app->put("/pregunta/:id",function($id) use($app) {
	$jsonmessage = \Slim\Slim::getInstance()->request();
  	$objDatos = json_decode($jsonmessage->getBody());

	$question = $objDatos->question;
	$image = $objDatos->image;
	$option_a = $objDatos->option_a;
	$option_b = $objDatos->option_b;
	$option_c = $objDatos->option_c;
	$option_d = $objDatos->option_d;
	$image_a = $objDatos->image_a;
	$image_b = $objDatos->image_b;
	$image_c = $objDatos->image_c;
	$image_d = $objDatos->image_d;
	$answer = $objDatos->answer;
	$id_materia = $objDatos->id_materia;

	try {
		$conex = getConexion();
		$result = $conex->prepare("UPDATE pregunta SET question='$question', image='$image', option_a='$option_a', image_a='$image_a', option_b='$option_b', image_b='$image_b', option_c='$option_c', image_c='$image_c', option_d='$option_d', image_d='$image_d', answer = '$answer' WHERE id='$id';");

		$result->execute();
		$conex = null;

		$app->response->headers->set('Content-type','application/json');
		$app->response->status(200);
		$app->response->body(json_encode(array('status'=>true)));

	}catch(PDOException $e) {
		echo "Error: ".$e->getMessage();
	}
});

$app->put("/pregunta/:id/up",function($id) use($app) {
	$jsonmessage = \Slim\Slim::getInstance()->request();
  	$objDatos = json_decode($jsonmessage->getBody());

	$status = 1;

	try {
		$conex = getConexion();
		$result = $conex->prepare("UPDATE pregunta SET status=$status WHERE id='$id';");

		$result->execute();
		$conex = null;

		$app->response->headers->set('Content-type','application/json');
		$app->response->status(200);
		$app->response->body(json_encode(array('status'=>true)));

	}catch(PDOException $e) {
		echo "Error: ".$e->getMessage();
	}
});

$app->put("/pregunta/:id/down",function($id) use($app) {
	$jsonmessage = \Slim\Slim::getInstance()->request();
  	$objDatos = json_decode($jsonmessage->getBody());

	$status = 0;

	try {
		$conex = getConexion();
		$result = $conex->prepare("UPDATE pregunta SET status=$status WHERE id='$id';");		
		$result->execute();
		$conex = null;

		$app->response->headers->set('Content-type','application/json');
		$app->response->status(200);
		$app->response->body(json_encode(array('status'=>true)));

	}catch(PDOException $e) {
		echo "Error: ".$e->getMessage();
	}
});

$app->delete('/pregunta/:id',function($id) use($app) {
	try {
		$conex = getConexion();

		$result = $conex->prepare("DELETE FROM pregunta WHERE id='$id';");
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