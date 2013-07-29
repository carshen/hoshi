<html>
	<head>
	</head>
	<body>
	<?php
		include 'authenticate.php';
		include 'dbc.php';
		
		if (isset($_GET['friend'])){
			$user = $_COOKIE['username'];
			//connect, query and close the database
			$dbc = mysqli_connect('localhost', $dbc_user, $dbc_pw, 'journalclone')
			or die('Error connecting to MySQL server.');
			$friendname = $_GET['friend'];
			echo "<h2>$friendname</h2><br>";

			// get friend's posts
			$data = mysqli_query($dbc, "SELECT * FROM entries WHERE username='$friendname'")
			or die('Failed to get past posts from database.');
			echo "<div id='history'>";
			while ($row = mysqli_fetch_array($data)){
				echo "<div class='pastpost'>";
				echo $row['date']."<br>".$row['title']."<br>".$row['entry'];
				$postID = $row['postID'];
				echo "</div><br>";
				echo "<a href='#'>comments</a>";
				echo "<div>"; // identify this div later *******************
				$postcomments = mysqli_query($dbc, "SELECT comment, commenter, date FROM comments WHERE postID=$postID");
				while ($c_row = mysqli_fetch_array($postcomments)){
					echo $c_row['comment']."<br>".$c_row['commenter']." ".$c_row['date']."<br>";
					// ********** use consistent string convention
				}
				echo "</div>";
				echo "<form action='profile.php? method='GET'><label>comment</label>" .
				"<input type='text' name='comment'><input type='hidden' name='postID' value='$postID'><input type='hidden' name='friend' value='$friendname'>".
				"<input type='submit'></form>";
			}
			echo "</div>";
			if (isset($_GET['comment'])){
				$datetime = new DateTime();
				$date = $datetime->format('y-m-d h:i:s');
				$comment = $_GET['comment'];
				$postID = $_GET['postID'];
				$commentquery = "INSERT INTO comments (postID, comment, owner, commenter, date)" .
				"VALUES ('$postID', '$comment', '$user', '$friendname', '$date')";
				//unset datetime?***********
				mysqli_query($dbc, $commentquery)
				or die('Error adding comment');
			}
			// get friend's friend list
			$profilefriends = array(); // **problems??
			$friend2_data = mysqli_query($dbc, "SELECT friend2 FROM friends WHERE friend1='$friendname'")
			or die('Failed to get past posts from database.');
			while ($friends_row = mysqli_fetch_array($friend2_data)){
				$friend = $friends_row['friend2'];
				echo "<form method='GET' action='profile.php'><input type='submit' name='friend' value='$friend'></form>";
				$profilefriends['$friend'] = 1;
				
			}
	
			$friend1_data = mysqli_query($dbc, "SELECT friend1 FROM friends WHERE friend2='$friendname'")
			or die('Failed to get past posts from database.');
			while ($friends_row = mysqli_fetch_array($friend1_data)){
				$friend = $friends_row['friend1'];
				echo "<form method='GET' action='profile.php'><input type='submit' name='friend' value='$friend'></form>";
				$profilefriends['$friend'] = 1;
			}

			echo "<form action='profile.php?friend=$friendname' method='POST'><input type='submit' value='add friend' name='addfriend'></form>";
			echo "<form action='profile.php?friend=$friendname' method='POST'><input type='submit' value='delete friend' name='deletefriend'></form>";
			// add and delete friends
			if (isset($_POST['addfriend'])){
				$datetime = new DateTime();
				$date = $datetime->format('y-m-d h:i:s');
				
				$addquery = "INSERT INTO friends (friend1, friend2, since) VALUES ('$user', '$friendname', '$date')";
				mysqli_query($dbc, $addquery)
				or die('Error adding friend.');
				//unset($datetime); // necessary?*********************
			}
			if (isset($_POST['deletefriend'])){
				$user = $_COOKIE['username'];
				$deletequery = "DELETE FROM friends WHERE friend1='$user' AND friend2='$friendname'";
				mysqli_query($dbc, $deletequery)
				or die ('Error deleting friend.');
			}
			
		} else {
			echo "<h3>Page not found.</h3>";
		}

	?>
	</body>
</html>