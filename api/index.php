<?php
	session_start();
	require 'vendor/autoload.php';
	\Slim\Slim::registerAutoloader();

	$app = new \Slim\Slim();

	/*Common*/
	require "modelos/conexion.php";
	require "modelos/security.php";
	$fpdo = new FluentPDO(getConexion());

	/* Models */
	require "modelos/user.php";
	require "modelos/warehouse.php";
	/*
	require "modelos/materia.php";
	require "modelos/area.php";
	require "modelos/materia_area.php";
	require "modelos/carrera.php";
	require "modelos/alumno.php";
	require "modelos/pregunta.php";
	*/

	$app->run();
