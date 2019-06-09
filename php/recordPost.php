<?php
    session_start();
    // If user not logged in, redirect to login page
    if (!isset($_SESSION['loggedin'])) {
        header("Location: login.php");
        exit();
    }

    $DB_HOST = 'localhost';
    $DB_USER = 'root';
    $DB_PASS = '';
    $DB_NAME = 'blog_db';

    // Attempt to connect
    $con = mysqli_connect($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
    if (mysqli_connect_errno()) {
        die ('Could not connect to MySQL Server: ' . mysqli_connect_errno());
    }

    // Check if data was submitted, isset() will check if data exists
    if (!isset($_POST['title'], $_POST['post_body'])) {
        // form is incomplete
        $_SESSION['registration_err'] = "Please complete all fields!";
        header("Location: registerForm.php");
        exit();
    }

    if ($stmt = $con->prepare('INSERT INTO posts (creator_id, date, title, post_body) VALUES (?,?,?,?)')) {
        date_default_timezone_set('Canada/Mountain');  // php Canada/Mountain timezone is closest to Vancouver time
        $date = date('Y-m-d h:m:s', time());
        $stmt->bind_param('isss', $_SESSION['id'], $date, $_POST['title'], $_POST['post_body']);    // assigning creator_id as the id of the currently logged-in user
        $stmt->execute();
        header("Location: post.php");
        exit();
    } else {
        echo 'Failed to store post into database';
    }
    $stmt->close();
    $con->close();

    // Use this to handle previous/next post situation instead of next/prev IDs
    // https://stackoverflow.com/questions/1446821/how-to-get-next-previous-record-in-mysql

?>




