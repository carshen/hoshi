<html>
<head>
<link type="text/css" rel="stylesheet" href="custom.css">
</head>
<body>

<?php
	include 'dbc.php';

	//connect, query and close the database
	$dbc = mysqli_connect('localhost', $dbc_user, $dbc_pw, 'journalclone')
	or die('Error connecting to MySQL server.');
	
	if (isset($_POST['su-name']) && isset($_POST['su-pword'])){
		$su_username = $_POST['su-name'];
		$su_password = $_POST['su-pword'];


		$query = "INSERT INTO users (username, password) ".
		"VALUES ('$su_username', '$su_password')";
		mysqli_query($dbc, $query)
		or die('Error querying database.');
		echo "Successful sign up! Please login.";
		mysqli_close($dbc);
	}
?>
<h2>SIGN UP</h2>
<form action="index.php" method="POST">
	<label>Username</label><br>
	<input type="text" name="su-name"><br>
	<label>Password</label><br>
	<input type="text" name="su-pword"><br>
	<input type="submit" value="signup" name="signup">
</form>
<?php
	// query to get the matching username and password from table
	if (!isset($_COOKIE['username'])){
		if (isset($_POST['li-name']) && isset($_POST['li-pword'])){
			$li_username = $_POST['li-name'];
			$li_password = $_POST['li-pword'];

			$matchquery = "SELECT username, password FROM users WHERE username = '$li_username' AND password = '$li_password'";
			$matched_row_data = mysqli_query($dbc, $matchquery); // returns mysqli_result object
			// if found then allow the log in
			if (mysqli_num_rows($matched_row_data) == 1){ // no duplicate users or user not found
				$row = mysqli_fetch_array($matched_row_data); // converts mysqli_result to array
				setcookie('username', $row['username']);
				// ********** CHANGE LATER **************
				header("Location: home.php");
			} else {
				echo "User not found. Try logging in again or signing up.";
			}
		}
	}
	else {
		// ********** CHANGE LATER **************
		header("Location: home.php");
	}
?>
<h2>LOGIN</h2>
<form action="index.php" method="POST">
	<label>Username</label><br>
	<input type="text" name="li-name"><br>
	<label>Password</label><br>
	<input type="text" name="li-pword"><br>
	<input type="submit" value="login">
</form>

<script src="jquery-2.0.3.min.js"></script>
<script src="application.js"></script>
</body>
</html>