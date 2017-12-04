<?php
	$hoy = getdate();
	$texto = join('.',$hoy);
	/*
	$new_dir = md5($texto).substr($name,-5);
	*/
	echo $texto;
 ?>