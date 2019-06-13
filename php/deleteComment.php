<?php
    // Script that deletes a comment ONLY if the currently logged-in user is the author of the post

    session_start();
    // echo $_GET['post_id'] . " " . $_GET['comment_id'] " ";
    $_SESSION['commentToDelete'] = $_COOKIE['comment_id'];
    if (isset($_SESSION['commentToDelete'])) {
        echo $_SESSION['commentToDelete'];
        
        $DATABASE_HOST = 'localhost';
        $DATABASE_USER = 'root';
        $DATABASE_PASS = '';
        $DATABASE_NAME = 'blog_db';


        // Try and connect using the info above.
        $con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
        if ( mysqli_connect_errno() ) {
            // If there is an error with the connection, stop the script and display the error.
            die ('Failed to connect to MySQL: ' . mysqli_connect_error());
        }

        if ($stmt_delete = $con->prepare("DELETE FROM comments WHERE comment_id = ?")) {
            $stmt_delete->bind_param('i', $_SESSION['commentToDelete']);
            $stmt_delete->execute();
            if (isset($_GET['publicPost']) && $_GET['publicPost'] == 1) {
                header("Location: post.php?post_id={$_GET['post_id']}&publicPost=1");
            } else {
                header("Location: post.php?post_id={$_GET['post_id']}");
            }
        } else {
            echo "failed";
        }
    }
?>
