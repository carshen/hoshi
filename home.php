<html>
<head>
<link type="text/css" rel="stylesheet" href="css/custom.css">
</head>
<body>

<?php
	ob_start();
	include 'dbc.php';
	include 'authenticate.php'; // prompt log-in of user
	
	// query to get the matching username and password from table
	$user = $_COOKIE['username'];
	echo "<div id='homemenu'>";
	echo "<div id='liinfo'><div id='limessage'>you are logged in as $user</div>";
	
	//connect, query and close the database
	$dbc = mysqli_connect('localhost', $dbc_user, $dbc_pw, 'journalclone')
	or die('Error connecting to MySQL server.');
?>

<div id='logout'><a href="logout.php">LOG OUT</a></div></div>
<div id="friendspanel"><div class='friendlist'>friends
<?php
	$friend2_data = mysqli_query($dbc, "SELECT friend2 FROM friends WHERE friend1='$user'")
	or die('Failed to get past posts from database.');
	while ($friends_row = mysqli_fetch_array($friend2_data)){
		$friend = $friends_row['friend2'];
		echo "<form method='GET' action='profile.php'><input class='friendbuttons' type='submit' name='friend' value='$friend'></form>";
	}
	
	$friend1_data = mysqli_query($dbc, "SELECT friend1 FROM friends WHERE friend2='$user'")
	or die('Failed to get past posts from database.');
	while ($friends_row = mysqli_fetch_array($friend1_data)){
		$friend = $friends_row['friend1'];
		echo "<form method='GET' action='profile.php'><input class='friendbuttons' type='submit' name='friend' value='$friend'></form>";
	}

?>
</div>
<div id='friendsearch'>

<form method='POST' action='home.php'>
<label>find more friends</label><br>
<input type=text value='search by username' name='desiredname'>
<input type='submit' value='go' class='submitbutton'>
</form>
<?php
	if (isset($_POST['desiredname'])){
		$desiredname = $_POST['desiredname'];
		$friendsearchquery = "SELECT username FROM users WHERE username = '$desiredname'";
		$search_data = mysqli_query($dbc, $friendsearchquery)
		or die ('Error finding friends.');
		
		if ($foundfriend = mysqli_fetch_array($search_data)){
			echo "<a href=profile.php?&friend=".$foundfriend['username'].">".$foundfriend['username']."</a>";
		}
		else { echo "nobody of that username is on hoshi";}
	}
?>
</div>
</div>

<a id='dashboardlink' class='homelink' href="#">DASHBOARD</a><a href="#" id="entry" class='homelink'>ADD ENTRY</a><a href="#" id="posthistory" class='homelink'>HISTORY</a> 
<div id='dashboardpanel'>
<?php
	$dashboard_query = "SELECT title, entry, username, date FROM entries, friends WHERE date >= DATE_SUB(CURDATE(), INTERVAL 3 DAY)".
	"AND ((friend1='$user' AND friend2=username) OR (friend2='$user' AND friend1=username)) ORDER BY date DESC";
	$dashboard_data = mysqli_query($dbc, $dashboard_query)
	or die('Error selecting dashboard entries.');
	
	while ($dashboard_row = mysqli_fetch_array($dashboard_data)){
		echo "<div class='dash_entry'>";
		echo "<div class='pastposttitlehome'>"."<a class='homeposttitle' href='profile.php?friend=".$dashboard_row['username']."'>".
		$dashboard_row['title']." &rarr;</a></div><br>".
		$dashboard_row['entry']."<br>".
		"<div class='pastpostdate'>&mdash; ".
		"<a href='profile.php?friend=".$dashboard_row['username']."'>".$dashboard_row['username']."</a> at ".
		$dashboard_row['date']."</div><br>";
		echo "</div>"; // for dash_entry
	}
?>
</div>

<form action="home.php" method="POST" id="postentry">
	<label>title</label><br>
	<input type="text" name="title"><br>
	<label>entry</label><br>
	<textarea rows="8" cols="80" name="entry" ></textarea><br>
	<input class='submitbutton' type="submit" value="publish" id="submit" name="submit"/>
</form>
<?php

	// add a post
	if (isset($_POST['title'])){ 
		$title = mysqli_real_escape_string($dbc, $_POST['title']); 
		$entry = mysqli_real_escape_string($dbc, $_POST['entry']);
		$datetime = new DateTime();
		$date = $datetime->format('y-m-d h:i:s');

		$query = "INSERT INTO entries (username, title, entry, date) ".
		"VALUES ('$user', '$title', '$entry', '$date')";
		mysqli_query($dbc, $query)
		or die('Error querying database.');
		echo "<br><p id='recorded'>Your entry has been recorded.</p>";
	}
	
	//delete a post
	if (isset($_POST['deletepost'])){
		$deleted_postID = $_POST['deletepost'];
		mysqli_query($dbc, "DELETE FROM entries WHERE postID='$deleted_postID'")
		or die('Failed to delete post.');
		echo "<p id='deletenotice'>The entry has been deleted.</p>";
	}
	// DISPLAY ALL POSTS
	// query for the posts in history
	$data = mysqli_query($dbc, "SELECT * FROM entries WHERE username='$user'")
	or die('Failed to get past posts from database.');
	
	// display past posts in #history panel
	echo "<div id='history'>";
	while ($row = mysqli_fetch_array($data)){
		echo "<div class='pastposthome'>"."<div class='pastpostdate'>".$row['date']."</div>".
		"<div class='pastposttitle'>".$row['title']."</div>".
		$row['entry'];
		$postID = $row['postID'];
		echo "<form class='deleteformhome' method='POST' action='home.php'>";
		echo "<input class='delete' type=hidden name='deletepost' value='$postID'/>";
		echo "<input type='submit' class='deletebutton' value='delete'/>";
		echo "</form></div>";
		$postID = $row['postID'];
		// print all the comments of that post
		echo "<div class='commentsunroll'>show comments</div><br>";
		echo "<div class='allcomments'>";
		$postcomments = mysqli_query($dbc, "SELECT commentID, comment, commenter, date FROM comments WHERE postID='$postID'");
		while ($c_row = mysqli_fetch_array($postcomments)){
			echo "<div class='onecommenthome'>";
			echo "<div class='editingcomment'></div>";
			echo "<div class='comment'>".$c_row['comment']."</div>"."<br><div class='commentdetail'>".$c_row['commenter']." ".$c_row['date']."</div>";
			// delete button
			echo  "<form class='deleteformhome' method='POST' action='home.php'>";
			$commentID = $c_row['commentID'];
			// edit button
			if ($c_row['commenter'] == $user) {
				echo "<button class='edithome' type='button'>edit</button>";
			}
			echo "<input type='hidden' name='deletecomment' value='$commentID' class='deletecommentID deletecommenthome'><input class='deletebutton deletebuttonhome' type=submit value='delete'>";
			echo "</form>";
			// ********** use consistent string convention
			echo "</div>"; // for .onecommenthome
		}
		// post comments
		echo "<form action='home.php' method='POST'><br><label>leave a comment</label><br>" .
		"<textarea rows='8' cols='50' name='comment'></textarea><input type='hidden' name='commenting' value='$postID'>".
		"<br><input class='submitbutton' type='submit' value='submit'></form>";
		echo "</div>";
	}
	echo "</div>";
	// END DISPLAY POSTS
	
	// add a comment
	if (isset($_POST['comment'])){
		$datetime = new DateTime();
		$date = $datetime->format('y-m-d h:i:s');
		$comment = mysqli_real_escape_string($dbc, $_POST['comment']);
		$postID = $_POST['commenting'];
		$commentquery = "INSERT INTO comments (postID, comment, owner, commenter, date)" .
		"VALUES ('$postID', '$comment', '$user', '$user', '$date')";
		//unset datetime?***********
		mysqli_query($dbc, $commentquery)
		or die('Error adding comment');
		header("Location: home.php", 302);
	}
	
	// delete a comment
	if (isset($_POST['deletecomment']) &&!isset($_POST['editcommentID'])){
		$deletecomment = $_POST['deletecomment'];
		$deletecommentquery = "DELETE FROM comments WHERE commentID= '$deletecomment'";
		mysqli_query($dbc, $deletecommentquery)
		or die ('Error deleting comment');
		header("Location: home.php", 302);
	}

	
	//edit a comment
	if (isset($_POST['editcommentID'])){
		echo "set";
		if (!isset($_POST['deletecommentaction'])){
			echo "updating";
			$editcommentID = $_POST['editcommentID'];
			$comment = mysqli_real_escape_string($dbc, $_POST['cmt']);
			$editquery = "UPDATE comments SET comment='$comment' WHERE commentID = '$editcommentID'";
			mysqli_query($dbc, $editquery)
			or die ('Error editing comment.');
			header("Location: home.php", 302);
		}
		else {
			echo "deleting";
			$deletecommentID = $_POST['editcommentID'];
			$deletecommentquery = "DELETE FROM comments WHERE commentID = '$deletecommentID'";
			mysqli_query($dbc, $deletecommentquery)
			or die ('Error editing comment.');
			header("Location: home.php", 302);
		}
	}
	
	mysqli_close($dbc);
	unset($datetime); // necessary? *****************
?>
</div> <!-- for homemenu -->

<script src="js/jquery-2.0.3.min.js"></script>
<script src="js/application.js"></script>
<script>
$(document).ready(function(){
		// CLICKING EDIT AND CANCELLING EDIT
	$('.edithome').click(function(){
		var cmt = $(this).parent().siblings(".comment").text();
		$(this).parent().siblings(".comment").hide();
		if ($(this).parent().parent().siblings('.editingcomment').find('.ecomment').length){
			$(this).parent().parent().find('.editingcomment').show();
		} else {
			var editcommentID = $(this).parent().parent().find('.deleteformhome').find('.deletecommentID').val();
			$(this).parent().siblings('.editingcomment').append("<form action='home.php' method='POST'><input type='hidden' name='editcommentID' value='" + editcommentID + "'><textarea rows='10' cols='100' class='ecomment' name='cmt' value=''></textarea><br><input class='edithome' type='submit' value='ok'><button type='button' class='cancel'>cancel</button><input type='submit' name='deletecommentaction' class='deletebutton' value='delete'/></form>");
			$(this).parent().siblings('.editingcomment').find('.ecomment').val(cmt);
		}
		
		$(this).hide();
		$(this).parent().siblings('.commentdetail').hide();
		$(this).parent().parent().find('.deleteformhome').hide();
		$('.cancel').click(function(){
			$(this).parent().parent().siblings('.comment').show();
			$(this).parent().parent().siblings('.commentdetail').show();
			$(this).parent().hide();
			//$(this).siblings('.editingcomment').hide();
			$(this).hide();
			//$(this).parent().parent().find('.deleteformhome').show();
			// $(this).parent().css("background", "yellow"); -- .editingcomment
			$(this).parent().parent().parent().find('.deleteformhome').show();
			$(this).parent().parent().parent().find('.edithome').show();
		});
		
	});
	$('.commentsunroll').click(function(){
		$(this).next().next().toggle(250);
	});
});

</script>
</div> <!-- for home panel-->
</body>
</html>