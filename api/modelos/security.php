<?php 
	function encriptar($string)
	{
		$key='042c76ee72648fd8c1841340da5b6da2';
		$td = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
		$iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_DEV_URANDOM );
		mcrypt_generic_init($td, $key, $iv);
		$encrypted_data_bin = mcrypt_generic($td, $string);
		mcrypt_generic_deinit($td);
		mcrypt_module_close($td);
		$encrypted_data_hex = bin2hex($iv).bin2hex($encrypted_data_bin);
		return $encrypted_data_hex;
	}

	function desencriptar($encrypted_data_hex)
	{
		$key='042c76ee72648fd8c1841340da5b6da2';
		$td = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
		$iv_size_hex = mcrypt_enc_get_iv_size($td)*2;
		$iv = pack("H*", substr($encrypted_data_hex, 0, $iv_size_hex));
		$encrypted_data_bin = pack("H*", substr($encrypted_data_hex, $iv_size_hex));
		mcrypt_generic_init($td, $key, $iv);
		$decrypted = mdecrypt_generic($td, $encrypted_data_bin);
		mcrypt_generic_deinit($td);
		mcrypt_module_close($td);
		return $decrypted;
	}
	function hidePassword($data){
		$salt = '#/$02.06$/#_#/$25.10$/#';
		$data = md5($salt.$data);
		$newdata = sha1($salt.$data);
		return $newdata;
	}
	function getCode(){
		/* #145fGz-gtXy */
		return trim(desencriptar('82a2c56aac42a4666b7bc74ca90537c6b0dc168a1623eb4550e5bb542474a5ad'));
	}
?>