<?php
	if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW'])){
		header('HTTP/1.1 401 Unauthorized');
		header('WWW-Authenticate: Basic realm="hoshi"');
		exit("Log in failed");
	}	
?>