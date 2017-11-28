<?php
/* get */
$app->get('/user/all',function() use($app) {
	global $fpdo;
	try {
		$result = $fpdo->from('user')->execute();
		$res = $result->fetchAll(PDO::FETCH_OBJ);
		$newRes = array();
		foreach ($res as $r) {
			$r->id = encriptar($r->id);
			array_push($newRes,$r);
		}

		$conex = null;
		$app->response->headers->set('Content-type','application/json');
		$app->response->headers->set('Access-Control-Allow-Origin','*');
		$app->response->status(200);
		$app->response->body(json_encode($newRes));
	}catch(PDOException $e) {
		echo 'Error: '.$e->getMessage();
	}
});
$app->get('/user/:id',function($id) use($app) {
	global $fpdo;
	$idd = desencriptar($id);
	try {

		$result = $fpdo->from('user')->where('id',$idd)->execute();
		if($result->rowCount()!=0){
			$res = $result->fetchObject();
			$res->id = encriptar($res->id);
			$status = true;
			$msg = "Encontrado con éxito";
		}
		else{
			$res = array();
			$status = false;
			$msg = "No se encontro ningun resultado";
		}		

		$app->response->headers->set('Content-type','application/json');
		$app->response->headers->set('Access-Control-Allow-Origin','*');
		$app->response->status(200);
		$app->response->body(json_encode(array('response'=>$res,"msg"=>$msg,'status'=>$status)));
	}catch(PDOException $e) {
		echo 'Error: '.$e->getMessage();
	}
});

/* new */
$app->post("/user/new",function() use($app) {
	global $fpdo;
	$objDatos = json_decode(file_get_contents("php://input"));
	$name = $objDatos->name;
	$last_name = $objDatos->last_name;
	$email = $objDatos->email;
	$cellphone = $objDatos->cellphone;
	$password = $objDatos->password;
	$picture_url = $objDatos->picture_url;
	$type_user = $objDatos->type_user;
	$vip = $objDatos->vip;
	$id = 0;
	try {

		$result = $fpdo->from('user')->where('email',$email)->execute();
		$res = $result->rowCount();		
		if($res == 0 && $vip == getCode()){
			$password = hidePassword($password);
			$values = array( 'id'=>'null',
						'name'=>$name,
						'last_name'=>$last_name,
						'email'=>$email,
						'cellphone'=>$cellphone,
						'password'=>$password,
						'picture_url'=>$picture_url,
						'type_user'=>$type_user,
						'date_init'=>new FluentLiteral('NOW()'));
			$result = $fpdo->insertInto('user',$values)->execute();
			$id = encriptar($result);
			$msg = "Usted se ha registrado con éxito";
			$status = true;
		}
		else{
			if($vip != getCode()){
				$msg = "Usted no tiene permisos para registrar";
				$status = false;
			}
			else{
				$msg = "El correo ya esta registrado";
				$status = false;
			}
		}

		$app->response->headers->set("Content-type","application/json");
		$app->response->headers->set('Access-Control-Allow-Origin','*');
		$app->response->status(200);
		$app->response->body(json_encode(array('id'=>$id,'msg'=>$msg,'status'=>$status)));

	}catch(PDOException $e) {
		echo "Error: ".$e->getMessage();
	}
});

/* edit */
$app->post("/user/edit",function() use($app) {
	global $fpdo;
	$jsonmessage = \Slim\Slim::getInstance()->request();
  	$objDatos = json_decode($jsonmessage->getBody());
	$idd = desencriptar($objDatos->id); 
	$name = $objDatos->name;
	$last_name = $objDatos->last_name;
	$email = $objDatos->email;
	$cellphone = $objDatos->cellphone;
	$picture_url = $objDatos->picture_url;
	$type_user = $objDatos->type_user;
	$vip = $objDatos->vip;
	$id = $objDatos->id;

	try {
		if($vip == getCode()){
			$result = $fpdo->from('user')->where('email = ? AND id != ?',$email,$idd)->execute();
			$res = $result->rowCount();
			if($res == 0 ){
				$set = array(	'name'=>$name,
							'last_name'=>$last_name,
							'email'=>$email,
							'cellphone'=>$cellphone,
							'picture_url'=>$picture_url,
							'type_user'=>$type_user,
							'date_init'=>new FluentLiteral('NOW()'));
				$result = $fpdo->update('user',$set,$idd)->execute();
				$id = encriptar($result);
				$msg = "Modificado con éxito";
				$status = true;
			}
			else{
				$msg = "El correo ya esta registrado";
				$status = false;
			}
		}else{
			$msg = "Usted no tiene permisos para registrar";
			$status = false;
		}
		$app->response->headers->set("Content-type","application/json");
		$app->response->headers->set('Access-Control-Allow-Origin','*');
		$app->response->status(200);
		$app->response->body(json_encode(array('id'=>$id,'msg'=>$msg,'status'=>$status)));
	}catch(PDOException $e) {
		echo "Error: ".$e->getMessage();
	}
})->conditions(array('id'=>'[0-9]{1,11}'));

/* delete */
$app->post('/user/delete',function() use($app) {
	global $fpdo;
	$jsonmessage = \Slim\Slim::getInstance()->request();
  	$objDatos = json_decode($jsonmessage->getBody());

  	$vip = $objDatos->vip;
	$idd = desencriptar($objDatos->id);
	try {
		if($vip == getCode()){
			$result = $fpdo->deleteFrom('user', $idd)->execute();
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
})->conditions(array('id'=>'[0-9]{1,11}'));

/* modulos plus */

/* modificar el password */
$app->post("/user/reset/password",function() use($app) {
	global $fpdo;
	$jsonmessage = \Slim\Slim::getInstance()->request();
  	$objDatos = json_decode($jsonmessage->getBody());

  	$newpassword = $objDatos->passwordnuevo;
  	$idd = desencriptar($objDatos->id);
  	$password = hidePassword($objDatos->password);
  	$passwordnuevo = hidePassword($objDatos->passwordnuevo);
  	$vip = $objDatos->vip;
  	$id = 0;

  	$result = $fpdo->from('user')->where('id=? AND password=?',$idd,$password)->execute();
	$res = $result->rowCount();
	try {
		if($res == 1  && $vip == getCode()){
			$set = array('password'=>$passwordnuevo);
			$result = $fpdo->update('user',$set,$idd)->execute();
			$id = encriptar($result);
			$msg = "Modificado con éxito";
			$status = true;
			$objDatos->password = $newpassword;
		}
		else{
			if($vip != getCode()){
				$msg = "Usted no tiene permisos para registrar";
				$status = false;
			}
			else{
				$msg = "La contraseña es incorrecta";
				$status = false;
			}
		}

		$app->response->headers->set('Content-type','application/json');
		$app->response->status(200);
		$app->response->body(json_encode(array('response'=>$objDatos,'msg'=>$msg,'status'=>$status)));

	}catch(PDOException $e) {
		echo "Error: ".$e->getMessage();
	}
})->conditions(array('id'=>'[0-9]{1,11}'));

/* login */
$app->post("/user/login",function() use($app) {
	global $fpdo;
	$objDatos = json_decode(file_get_contents("php://input"));
	$email = $objDatos->email;
	$password = hidePassword($objDatos->password);
	$vip = $objDatos->vip;

	try {
		if($vip == getCode()){
			$result = $fpdo->from('user')->where('email',$email)->execute();
			$res = $result->rowCount();
			if($res == 0){
				$res = array();
				$msg = "El correo introducido no esta registrado";
				$status = false;
			}
			else{
				$objeto = $result->fetchObject();
				if($password == $objeto->password){
					$res = $objeto;
					$msg = "Correcto";
					$status = true;
				}
				else{
					$res = array();
					$msg = "Contraseña Incorrecta";
					$status = false;
				}
			}
		}
		else{
			$res = array();
			$msg = "Usted no tiene permisos" ;
			$status = false;
		}

		$app->response->headers->set("Content-type","application/json");
		$app->response->headers->set('Access-Control-Allow-Origin','*');
		$app->response->status(200);
		$app->response->body(json_encode(array("response"=>$res,"msg"=>$msg,"status"=>$status)));
	}catch(PDOException $e) {
		echo "Error: ".$e->getMessage();
	}
});

?>