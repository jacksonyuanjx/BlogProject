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

    if ($stmt = $con->prepare("INSERT INTO comments (commenter_name, post_id, date, comment_body) VALUES (?,?,?,?)")) {
        date_default_timezone_set('Canada/Pacific');  // php Canada/Pacific timezone is closest to Vancouver time
        // Storing in 24-hr time
        $date = date('Y-m-d H:i:s', time());
        $stmt->bind_param('siss', $_POST['name'], $_GET['post_id'], $date, $_POST['comment_body']);
        $stmt->execute();
    } else {
        echo "Failed to execute SQL stmt";
    }

    
    $con->close();
?>


