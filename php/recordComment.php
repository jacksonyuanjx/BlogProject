<?php
    // This script does not require user log-in b/c anyone is permitted to comment (subject to change)
    session_start();

    $DATABASE_HOST = 'localhost';
    $DATABASE_USER = 'root';
    $DATABASE_PASS = '';
    $DATABASE_NAME = 'blog_db';

    // Attempt to connect
    $con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
	  if ( mysqli_connect_errno() ) {
		// If there is an error with the connection, stop the script and display the error.
		    die ('Failed to connect to MySQL: ' . mysqli_connect_error());
    }

    // Ensure that all fields of comment-creation form are complete
    // Note: should not reach here as input tags have 'required' field set, feature of HTML 5
    if (!isset($_POST['name'], $_POST['comment_body']) || $_POST['name'] == "" || $_POST['comment_body'] == "") {
        $_SESSION['incomplete_comment_err'] = "Please complete out all fields!";
        header("Location: post.php?post_id={$_SESSION['post_id']}");
        exit();
    }

    // Query that inserts comment into `comments` table
    if ($stmt = $con->prepare("INSERT INTO `comments` (commenter_name, post_id, date, comment_body) VALUES (?,?,?,?)")) {
        date_default_timezone_set('Canada/Pacific');  // php Canada/Pacific timezone is closest to Vancouver time
        // Storing in 24-hr time
        $date = date('Y-m-d H:i:s', time());
        $stmt->bind_param('siss', $_POST['name'], $_GET['post_id'], $date, $_POST['comment_body']);
        $stmt->execute();

        // Increment num_comments for corresponding post
        if ($stmt_incrComments = $con->prepare("UPDATE posts SET num_comments = num_comments + 1 WHERE post_id = ?")) {
            $stmt_incrComments->bind_param('i', $_GET['post_id']);
            $stmt_incrComments->execute();
            $stmt_incrComments->close();
        } else {
            echo "could not update num_comments";
        }
        
        if (isset($_GET['publicPost'])) {
            header("Location: post.php?post_id={$_SESSION['post_id']}&publicPost=1");
        } else {
            header("Location: post.php?post_id={$_SESSION['post_id']}");
        }
        $stmt->close();
        exit();
        // $_SESSION['error'] = $_GET['post_id'];
    } else {
        $_SESSION['error'] = "error here!";
        echo "Failed to execute SQL stmt";
    }

    
    $con->close();
?>


