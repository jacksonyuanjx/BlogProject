<?php
    // Script that deletes a post from yourPosts.php ONLY if the currently logged-in user is the author of the post
    session_start();

    // Check if author of post is the currently logged-in user
    if (isset($_SESSION['loggedin'], $_SESSION['id'])) {
        if ($_SESSION['id'] != $_COOKIE['creator_id']) {
            // Current user is NOT the author of this post
            setcookie('creator_id', "", time()-3600);
            header("Location: login.php");
            exit();
        }
    } else {
        // Not logged in so definitely redirect to login.php
        header("Location: login.php");
        exit();
    }
    
    $_SESSION['postToDelete'] = $_COOKIE['post_id'];
    $_SESSION['imgToDelete'] = $_COOKIE['img_path'];
    setcookie('img_path', "", time()-3600);     // Delete the 2 cookies
    setcookie('post_id', "", time()-3600);

    if (isset($_SESSION['postToDelete']) && isset($_SESSION['imgToDelete'])) {
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

        // Query that deletes the post stored in the 'post_id' cookie
        if ($stmt_delete = $con->prepare("DELETE FROM posts WHERE post_id = ?")) {
            $stmt_delete->bind_param('i', $_SESSION['postToDelete']);
            $stmt_delete->execute();
            $stmt_delete->close();

            // Delete the post's corresponding comments
            if ($stmt_deleteComments = $con->prepare("DELETE FROM comments WHERE post_id = ?")) {
                $stmt_deleteComments->bind_param('i', $_SESSION['postToDelete']);
                $stmt_deleteComments->execute();
                $stmt_deleteComments->close();
            } else {
                echo "Failed to delete post's corresponding comments";
            }

            // Delete the post's corresponding uploaded image if it's not default.jpeg
            if ($_SESSION['imgToDelete'] != "../uploads/default.jpeg") {
                if (!unlink($_SESSION['imgToDelete'])) {
                    // Deletion failed
                    echo "Failed to delete image";
                }
            }

            // Redirect back to yourPosts.php
            header("Location: yourPosts.php");
            exit();

        } else {
            echo "Failed to delete post";
        }

        // Unset all newly declared session vars
        unset($_SESSION['postToDelete']);
        unset($_SESSION['imgToDelete']);
        
    }
    
?>