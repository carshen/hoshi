<html>
<head>
<link type="text/css" rel="stylesheet" href="custom.css">
</head>
<body>
<a href="#" id="entry">ADD ENTRY</a> <a href="#" id="posthistory">HISTORY</a>
<form action="journal.php" method="POST" id="postentry">
	<label>Title</label><br>
	<input type="text" name="title"><br>
	<label>Entry</label><br>
	<textarea rows="8" cols="80" name="entry" ></textarea><br>
	<input type="submit" value="Publish" id="submit"/>
</form>

<?php
	include 'dbc.php';
	$title = $_POST['title'];
	$entry = $_POST['entry'];
	$datetime = new DateTime();
	$date = $datetime->format('y-m-d h:i:s');
	//connect, query and close the database
	$dbc = mysqli_connect('localhost', $dbc_user, $dbc_pw, 'journalclone')
	or die('Error connecting to MySQL server.');
	
	$query = "INSERT INTO entries (title, entry, date) ".
	"VALUES ('$title', '$entry', '$date')";
	mysqli_query($dbc, $query)
	or die('Error querying database.');
	echo "<br><p id='recorded'>Your entry has been recorded.</p>";
	//delete before rendering
	if (array_key_exists("delete", $_POST)){
		$deletedate = $_POST['delete'];
		mysqli_query($dbc, "DELETE FROM entries WHERE date='$deletedate'")
		or die('Failed to delete post.');
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
		echo "<br><form method='POST' action='index.php'><input class='delete' type=hidden name='delete' value='$entrydate'/><input type='submit' value='delete'/></form><br><br>";
		echo "</div>";
	}
	echo "</div>";
	
	mysqli_close($dbc);
	unset($date);
?>
<script src="jquery-2.0.3.min.js"></script>
<script src="application.js"></script>
</body>
</html>