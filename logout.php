<?php
	if (isset($_COOKIE['username'])){
		setcookie('username', '', time()-100);
	}
	header("Location: index.php");

?>