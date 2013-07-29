<?php
	include 'dbc.php';
	include 'authenticate.php'; // prompt log-in of user
	
	// query to get the matching username and password from table
	$li_username = $_COOKIE['username'];
	echo "you are logged in as $li_username<br>";

?>
<html>
<head>
<link type="text/css" rel="stylesheet" href="css/custom.css">
</head>
<body>
<a href="#" id="entry">ADD ENTRY</a> <a href="#" id="posthistory">HISTORY</a> 
<a href="logout.php">LOG OUT</a>
<form action="home.php" method="POST" id="postentry">
	<label>Title</label><br>
	<input type="text" name="title"><br>
	<label>Entry</label><br>
	<textarea rows="8" cols="80" name="entry" ></textarea><br>
	<input type="submit" value="Publish" id="submit" name="submit"/>
</form>


<?php
	//connect, query and close the database
	$dbc = mysqli_connect('localhost', $dbc_user, $dbc_pw, 'journalclone')
	or die('Error connecting to MySQL server.');
		
	if (array_key_exists("title", $_POST)){ // an entry has been submitted already -- don't worry abt if 'entry' exists, b/c 'title' existing is sufficient
		$title = $_POST['title'];                                                                                        // since form was submitted
		$entry = $_POST['entry'];
		$datetime = new DateTime();
		$date = $datetime->format('y-m-d h:i:s');

		$query = "INSERT INTO entries (userid, username, title, entry, date) ".
		"VALUES (100, '$li_username', '$title', '$entry', '$date')";
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
	$data = mysqli_query($dbc, "SELECT * FROM entries WHERE username='$li_username'")
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