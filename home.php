<?php
	ob_start();
	include 'dbc.php';
	include 'authenticate.php'; // prompt log-in of user
	
	// query to get the matching username and password from table
	$user = $_COOKIE['username'];
	echo "<div id='liinfo'><div id='limessage'>you are logged in as $user</div>";
	
	//connect, query and close the database
	$dbc = mysqli_connect('localhost', $dbc_user, $dbc_pw, 'journalclone')
	or die('Error connecting to MySQL server.');
?>
<html>
<head>
<link type="text/css" rel="stylesheet" href="css/custom.css">
</head>
<body>
<a href="logout.php" id="logout">LOG OUT</a>
</div>
<a href="#" id="entry">ADD ENTRY</a> <a href="#" id="posthistory">HISTORY</a> 
<form action="home.php" method="POST" id="postentry">
	<label>Title</label><br>
	<input type="text" name="title"><br>
	<label>Entry</label><br>
	<textarea rows="8" cols="80" name="entry" ></textarea><br>
	<input type="submit" value="Publish" id="submit" name="submit"/>
</form>
<?php

	// add a post
	if (isset($_POST['title'])){ 
		$title = $_POST['title']; 
		$entry = $_POST['entry'];
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
		echo "<div class='pastpost'>".$row['date']."<br>".$row['title']."<br>".$row['entry'];
		$postID = $row['postID'];
		echo "<br><form method='POST' action='home.php'>";
		echo "<input class='delete' type=hidden name='deletepost' value='$postID'/>";
		echo "<input type='submit' value='delete post'/>";
		echo "</form><br></div>";
		$postID = $row['postID'];
		// print all the comments of that post
		echo "<div id='postcommentpanel'>";
		$postcomments = mysqli_query($dbc, "SELECT commentID, comment, commenter, date FROM comments WHERE postID='$postID'");
		while ($c_row = mysqli_fetch_array($postcomments)){
			echo "<div class='onecomment'>";
			echo "<div class='editingcomment'></div>";
			echo "<div class='comment'>".$c_row['comment']."</div>"."<br>".$c_row['commenter']." ".$c_row['date']."<br>";
			// delete button
			echo  "<form class='deleteform' method='POST' action='home.php'>";
			$commentID = $c_row['commentID'];
			echo "<input type='hidden' name='deletecomment' value='$commentID' class='deletecommentID'><input type=submit value='delete comment'>";
			echo "</form>";
			// edit button
			if ($c_row['commenter'] == $user) {
				echo "<div class='edit'>edit</div>";
			}
			// ********** use consistent string convention
			echo "</div>"; // for .onecomment
		}
		echo "</div>";
		// post comments
		echo "<form action='home.php' method='POST'><label>comment</label><br>" .
		"<textarea rows='8' cols='50' name='comment'></textarea><input type='hidden' name='commenting' value='$postID'>".
		"<br><input type='submit' value='comment'></form>";
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
	if (isset($_POST['deletecomment'])){
		$deletecomment = $_POST['deletecomment'];
		$deletecommentquery = "DELETE FROM comments WHERE commentID= '$deletecomment'";
		mysqli_query($dbc, $deletecommentquery)
		or die ('Error deleting comment');
		header("Location: home.php", 302);
	}

	
	//edit a comment
	if (isset($_POST['editcommentID'])){
		$editcommentID = $_POST['editcommentID'];
		echo "$editcommentID";
		$comment = mysqli_real_escape_string($dbc, $_POST['cmt']);
		$editquery = "UPDATE comments SET comment='$comment' WHERE commentID = '$editcommentID'";
		mysqli_query($dbc, $editquery)
		or die ('Error editing comment.');
		header("Location: home.php", 302);
	}
?>

<br>
<div id="friendspanel">friends
	<ul>
<?php
	$friend2_data = mysqli_query($dbc, "SELECT friend2 FROM friends WHERE friend1='$user'")
	or die('Failed to get past posts from database.');
	while ($friends_row = mysqli_fetch_array($friend2_data)){
		$friend = $friends_row['friend2'];
		echo "<form method='GET' action='profile.php'><input type='submit' name='friend' value='$friend'></form>";
	}
	
	$friend1_data = mysqli_query($dbc, "SELECT friend1 FROM friends WHERE friend2='$user'")
	or die('Failed to get past posts from database.');
	while ($friends_row = mysqli_fetch_array($friend1_data)){
		$friend = $friends_row['friend1'];
		echo "<form method='GET' action='profile.php'><input type='submit' name='friend' value='$friend'></form>";
	}
	mysqli_close($dbc);
	unset($datetime); // necessary? *****************
?>
	</ul>
</div>
<script src="js/jquery-2.0.3.min.js"></script>
<script src="js/application.js"></script>
<script>
$(document).ready(function(){
		// CLICKING EDIT AND CANCELLING EDIT
	$('.edit').click(function(){
		var cmt = $(this).siblings(".comment").text();
		$(this).siblings(".comment").hide();
		if ($(this).siblings('.editingcomment').find('.ecomment').length){
			$(this).siblings('.editingcomment').show();
		} else {
			var editcommentID = $(this).siblings('.deleteform').find('.deletecommentID').val();
			$(this).siblings('.editingcomment').append("<form action='home.php' method='POST'><input type='hidden' name='editcommentID' value='" + editcommentID + "'><input class='ecomment' name='cmt' value='' type='text'><input type='submit' value='edit'></form>");
			$(this).siblings('.editingcomment').find('.ecomment').val(cmt);
		}
		
		if ($(this).siblings('.cancel').length){
			$(this).siblings('.cancel').show();
		} else {
			$(this).parent().append("<div class='cancel'>cancel</div>");
		}
		$(this).hide();
		$('.cancel').click(function(){
			$(this).siblings('.comment').show();
			$(this).siblings('.editingcomment').hide();
			$(this).hide();
			$(this).siblings('.edit').show();
		});
		
	});
});

</script>
</body>
</html>