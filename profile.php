<?php
	ob_start();
	include 'authenticate.php';
	include 'dbc.php';
?>
<html>
	<head>
	<link type="text/css" rel="stylesheet" href="css/custom.css">
	</head>
	<body>
	<?php
		// a profile is set
		if (isset($_GET['friend'])){
			// current user
			$user = $_COOKIE['username'];
			// if trying to view own public profile, redirect to home profile
			if ($user == $_GET['friend']){
				header("Location: home.php", 302);
			}
			
			//connect, query and close the database
			$dbc = mysqli_connect('localhost', $dbc_user, $dbc_pw, 'journalclone')
			or die('Error connecting to MySQL server.');
			
			// add and delete buttons
			$friendname = $_GET['friend'];

			// and and delete buttons
			echo "<div id='addndelete'>";
			echo "<form class='addndeletefriend' action='profile.php?friend=$friendname' method='POST'><input class='friendbuttons' type='submit' value='+ friend' name='addfriend'></form>";
			echo "<form class='addndeletefriend' action='profile.php?friend=$friendname' method='POST'><input class='friendbuttons' type='submit' value='- friend' name='deletefriend'></form>";
			echo "</div>";
			
			// profile title
			echo "<div id='profilename'>$friendname</div><br>";
			
			echo "<div class='profilepage'>";
			
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
			
			// START RENDERING FRIEND'S PROFILE
			
			// get friend's posts
			$data = mysqli_query($dbc, "SELECT * FROM entries WHERE username='$friendname'")
			or die('Failed to get past posts from database.');
			// render all blog posts of friend
			echo "<div id='profilehistory'>";
			while ($row = mysqli_fetch_array($data)){
				echo "<div class='postncomments'>"; // div containing 1 post and its comments
					echo "<div class='pastpost'>"; // div for the post
						echo "<div class='pastpostdate'>".$row['date']."</div>".
						"<div class='pastposttitle'>".$row['title']."</div>".
						"<br>".$row['entry'];
						$postID = $row['postID'];
					echo "</div><br>";
					// comments area
					echo "<div class='commentsunroll'>comments</div>";
					echo "<div class='allcomments'>"; // identify this div later *******************
					$postcomments = mysqli_query($dbc, "SELECT commentID, comment, commenter, date FROM comments WHERE postID=$postID");
					while ($c_row = mysqli_fetch_array($postcomments)){
						echo "<div class='onecomment'>";
						echo "<div class='comment'>".$c_row['comment']."</div>";
						echo "<div class='commentdetail'>".$c_row['commenter']." at ".$c_row['date']."</div>";
						if ($c_row['commenter'] == $user) {
							echo "<div class='editingcomment'></div>";
							echo "<div class='modbuttons'>";
							echo  "<form class='deleteform' method='POST' action='profile.php?&friend=$friendname'>";
							$commentID = $c_row['commentID'];
							echo "<input class='deletecommentID' type='hidden' name='deletecomment' value='$commentID'><input class='deletebutton' type=submit value='delete'>";
							echo "</form>";
							echo "<button type='button' class='edit'>edit</button>";
							echo "</div>"; // for .editingcomment where the comment box and mod buttons will go
						}
						// ********** use consistent string convention
						echo "</div>"; // for 'onecomment';
					}
					echo "<form action='profile.php?&friend=$friendname' method='POST'><label>comment</label><br>" .
					"<textarea rows='8' cols='100' name='comment'></textarea><input type='hidden' name='postID' value='$postID'><input type='hidden' name='friend' value='$friendname'>".
					"<br><input class='submitcomment' type='submit' value='submit'></form>";
					echo "</div>"; // for allcomments
				echo "</div>"; // for .postncomments
			}
			echo "</div>"; // for #profilehistory
			
			// delete a comment
			if (isset($_POST['deletecomment']) && !isset($_POST['editcommentID'])){
				$deletecomment = $_POST['deletecomment'];
				$deletecommentquery = "DELETE FROM comments WHERE commentID= '$deletecomment'";
				mysqli_query($dbc, $deletecommentquery)
				or die ('Error deleting comment');
				header("Location: profile.php?&friend=$friendname", 302);
			}
			
			
			// add a comment
			if (isset($_POST['comment'])){
				$datetime = new DateTime();
				$date = $datetime->format('y-m-d h:i:s');
				$comment = mysqli_real_escape_string($dbc, $_POST['comment']);
				$postID = $_POST['postID'];
				$commentquery = "INSERT INTO comments (postID, comment, owner, commenter, date)" .
				"VALUES ('$postID', '$comment', '$friendname', '$user', '$date')";
				//unset datetime?***********
				mysqli_query($dbc, $commentquery)
				or die('Error adding comment');
				header("Location: profile.php?&friend=$friendname", 302);
			}
			// get friend's friend list
			echo "<div id='profilefriends'> $friendname's friends<br>";
			$profilefriends = array(); // **problems??
			$friend2_data = mysqli_query($dbc, "SELECT friend2 FROM friends WHERE friend1='$friendname'")
			or die('Failed to get past posts from database.');
			while ($friends_row = mysqli_fetch_array($friend2_data)){
				$friend = $friends_row['friend2'];
				echo "<form method='GET' action='profile.php'><input class='friendbuttons' type='submit' name='friend' value='$friend'></form>";
				$profilefriends['$friend'] = 1;
			}
	
			$friend1_data = mysqli_query($dbc, "SELECT friend1 FROM friends WHERE friend2='$friendname'")
			or die('Failed to get past posts from database.');
			while ($friends_row = mysqli_fetch_array($friend1_data)){
				$friend = $friends_row['friend1'];
				echo "<form method='GET' action='profile.php'><input class='friendbuttons' type='submit' name='friend' value='$friend'></form>";
				$profilefriends['$friend'] = 1;
			}
			echo "</div>";
			
			//edit a comment
			if (isset($_POST['editcommentID'])){
				if (!isset($_POST['deletecomment'])){
					$editcommentID = $_POST['editcommentID'];
					echo "$editcommentID";
					$comment = mysqli_real_escape_string($dbc, $_POST['cmt']);
					$editquery = "UPDATE comments SET comment='$comment' WHERE commentID = '$editcommentID'";
					mysqli_query($dbc, $editquery)
					or die ('Error editing comment.');
					header("Location: profile.php?&friend=$friendname", 302);
				} else {
					$deletecommentID = $_POST['editcommentID'];
					$deletecommentquery = "DELETE FROM comments WHERE commentID= '$deletecommentID'";
					mysqli_query($dbc, $deletecommentquery)
					or die ('Error deleting comment');
					header("Location: profile.php?&friend=$friendname", 302);
				}
				
			
			}
		echo "</div>"; //for .profilepage
		} else {
		// redirect to log in page
			header("Location: index.php", 302);
		}

	?>
	<script src="js/jquery-2.0.3.min.js"></script>
	<script src="js/application.js"></script>
	<script>
	var friendname = "<?php echo $friendname; ?>";
	$(document).ready(function(){
	
		// CLICKING EDIT AND CANCELLING EDIT
		$('.edit').click(function(){
			var cmt = $(this).parent().siblings('.comment').text();
			$(this).parent().siblings('.comment').hide();
			if ($(this).parent().siblings('.editingcomment').find('.ecomment').length){
				//$(this).parent().siblings('.editingcomment').show();
				console.log("here");
				$(this).parent().siblings('.editingcomment').find('.editform').show();
			} else {
				var editcommentID = $(this).siblings('.deleteform').find('.deletecommentID').val();
				$(this).parent().siblings('.editingcomment').append("<form class='editform' action='profile.php?&friend=" + friendname + "' method='POST'><input type='hidden' name='editcommentID' value='" + editcommentID + "'><textarea rows='5' cols='100' class='ecomment' name='cmt' value=''></textarea><br><input class='submitedit' name='editaction' type='submit' value='ok'><button type='button' class='cancel'>cancel</button><input class='deletebutton' name='deletecomment' type='submit' value='delete'></form>");
				$(this).parent().siblings('.editingcomment').find('.ecomment').val(cmt);
			}
			$(this).parent().siblings('.commentdetail').hide();
			$(this).parent().hide();
			
			$('.cancel').click(function(){
				$(this).parent().parent().siblings('.modbuttons').show();
				$(this).parent().hide();
				$(this).parent().parent().siblings('.commentdetail').show();
				$(this).parent().parent().siblings('.comment').show();
			});
			
		});
	});
	</script>
	</body>
</html>