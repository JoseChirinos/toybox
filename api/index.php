<?php
	session_start();
	require 'vendor/autoload.php';
	\Slim\Slim::registerAutoloader();

	$app = new \Slim\Slim();

	/*Common*/
	require "model/conexion.php";
	require "model/security.php";
	$fpdo = new FluentPDO(getConexion());

	/* Models */
	require "model/user.php";
	require "model/warehouse.php";
	require "model/product.php";
	/*
	require "modelos/materia.php";
	require "modelos/area.php";
	require "modelos/materia_area.php";
	require "modelos/carrera.php";
	require "modelos/alumno.php";
	require "modelos/pregunta.php";
	*/

	$app->run();
