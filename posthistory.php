<html>
<head>
<link type="text/css" rel="stylesheet" href="custom.css">
</head>
<body>
<a href="#" id="entry">ADD ENTRY</a> <a href="#" id="posthistory">HISTORY</a>
<form action="journal.php" method="POST">
	<label>Title</label><br>
	<input type="text" name="title"><br>
	<label>Entry</label><br>
	<textarea rows="8" cols="80" name="entry" ></textarea><br>
	<input type="submit" value="Publish" id="submit">
</form>

<?php

	//connect, query and close the database
	$dbc = mysqli_connect('localhost', 'root', '', 'journalclone')
	or die('Error connecting to MySQL server.');
	
	$data = mysqli_query($dbc, "SELECT * FROM entries")
	or die('failed to get past posts from database');
	
	echo "<div id='history'>";
	while ($row = mysqli_fetch_array($data)){
		echo $row['date']."<br>".$row['title']."<br>".$row['entry'];
		echo "<br><br>";
	}
	echo "</div>";
	
	mysqli_close($dbc);
	unset($date);
?>
<script src="jquery-2.0.3.min.js"></script>
<script src="application.js"></script>
</body>
</html>