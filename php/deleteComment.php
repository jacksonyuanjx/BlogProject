<?php
    // Script that deletes a comment ONLY if the currently logged-in user is the author of the post
    // ^^Above condition is verified here below as well as in post.php by not displaying the delete button
    // NOTE CHECK IF USER IS CORRECT AUTHOR

    session_start();

    if (isset($_SESSION['loggedin'], $_SESSION['id'])) {
        if ($_SESSION['id'] != $_SESSION['creator_id']) {
            header("Location: login.php");
            exit();
        }
    } else {
        // Not logged in so definitely redirect to login.php
        header("Location: login.php");
        exit();
    }
    $_SESSION['commentToDelete'] = $_COOKIE['comment_id'];
    setcookie('comment_id', "", time()-3600);   // Deleting the cookie by: setting the same cookie with NO value and a time in the past (expired)

    if (isset($_SESSION['commentToDelete'])) {
        
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

        // Query that deletes the comment stored in the 'comment_id' cookie
        if ($stmt_delete = $con->prepare("DELETE FROM comments WHERE comment_id = ?")) {
            $stmt_delete->bind_param('i', $_SESSION['commentToDelete']);
            $stmt_delete->execute();
            $stmt_delete->close();

            // Decrement num_comments for corresponding post
            if ($stmt_incrComments = $con->prepare("UPDATE posts SET num_comments = num_comments - 1 WHERE post_id = ?")) {
                $stmt_incrComments->bind_param('i', $_GET['post_id']);
                $stmt_incrComments->execute();
                $stmt_incrComments->close();
            } else {
                echo "Could not update num_comments";
            }

            // Redirect depending on whether navigated from blog.php or yourPosts.php
            if (isset($_GET['publicPost']) && $_GET['publicPost'] == 1) {
                // navigated from blog.php
                header("Location: post.php?post_id={$_GET['post_id']}&publicPost=1#anchorComment");
                exit();
            } else {
                header("Location: post.php?post_id={$_GET['post_id']}#anchorComment");
                exit();
            }

        } else {
            echo "Failed to delete comment";
        }

        // Unset all newly declared session vars
        unset($_SESSION['commentToDelete']);
        unset($_SESSION['creator_id']);

    }

?>
