<?php
	include 'dbc.php';
	include 'authenticate.php'; // prompt log-in of user
	
	//connect, query and close the database
	$dbc = mysqli_connect('localhost', $dbc_user, $dbc_pw, 'journalclone')
	or die('Error connecting to MySQL server.');
	// query to get the matching username and password from table
	$entered_username = $_SERVER['PHP_AUTH_USER'];
	$entered_password = $_SERVER['PHP_AUTH_PW'];
	$matchquery = "SELECT username, password FROM users WHERE username = '$entered_username' AND password = '$entered_password'";
	$matched_row_data = mysqli_query($dbc, $matchquery); // returns mysqli_result object
	// if found then allow the log in
	if (mysqli_num_rows($matched_row_data) == 1){ // no duplicate users or user not found
		$row = mysqli_fetch_array($matched_row_data); // converts mysqli_result to array
		$username = $row['username'];
		$password = $row['password'];
	} else {
		header('HTTP/1.1 401 Unauthorized');
		header('WWW-Authenticate: Basic realm="hoshi');
		exit('haha, access denied.');
	}
	echo "you are logged in as $username<br>";
?>
<html>
<head>
<link type="text/css" rel="stylesheet" href="css/custom.css">
</head>
<body>
<a href="#" id="entry">ADD ENTRY</a> <a href="#" id="posthistory">HISTORY</a>
<form action="home.php" method="POST" id="postentry">
	<label>Title</label><br>
	<input type="text" name="title"><br>
	<label>Entry</label><br>
	<textarea rows="8" cols="80" name="entry" ></textarea><br>
	<input type="submit" value="Publish" id="submit"/>
</form>


<?php
	if (array_key_exists("title", $_POST)){ // an entry has been submitted already -- don't worry abt if 'entry' exists, b/c 'title' existing is sufficient
		$title = $_POST['title'];                                                                                        // since form was submitted
		$entry = $_POST['entry'];
		$datetime = new DateTime();
		$date = $datetime->format('y-m-d h:i:s');
		//connect, query and close the database
		$dbc = mysqli_connect('localhost', $dbc_user, $dbc_pw, 'journalclone')
		or die('Error connecting to MySQL server.');
		
		$username = $_SERVER['PHP_AUTH_USER'];
		$password = $_SERVER['PHP_AUTH_PW'];
		
		$query = "INSERT INTO entries (userid, username, password, title, entry, date) ".
		"VALUES (100, '$username', '$password', '$title', '$entry', '$date')";
		mysqli_query($dbc, $query)
		or die('Error querying database.');
		echo "<br><p id='recorded'>Your entry has been recorded.</p>";
	}
	//delete post before rendering
	if (array_key_exists("delete", $_POST)){
		$deletedate = $_POST['delete'];
		mysqli_query($dbc, "DELETE FROM entries WHERE date='$deletedate'")
		or die('Failed to delete post.');
		echo "<p id='deletenotice'>The entry has been deleted.</p>";
	}
	// query for the posts in history
	$data = mysqli_query($dbc, "SELECT * FROM entries")
	or die('Failed to get past posts from database.');
	
	// display past posts in #history panel
	echo "<div id='history'>";
	while ($row = mysqli_fetch_array($data)){
		echo "<div class='pastpost'>";
		echo $row['date']."<br>".$row['title']."<br>".$row['entry'];
		$entrydate = $row['date'];
		echo "<br><form method='POST' action='home.php'><input class='delete' type=hidden name='delete' value='$entrydate'/><input type='submit' value='delete'/></form><br><br>";
		echo "</div>";
	}
	echo "</div>";
	
	mysqli_close($dbc);
	unset($date);
?>
<script src="js/jquery-2.0.3.min.js"></script>
<script src="js/application.js"></script>
</body>
</html>