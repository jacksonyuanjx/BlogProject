<?php
    // recordPost.php will set session var 'post_id' so that when redirected to post.php, 
    // we don't need the guard to check if the 'post_id' URL param is set as the queries will 
    // always be using $_SESSION['post_id'] and not the URL param

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
    if (!isset($_POST['title'], $_POST['post_body']) || $_POST['title'] == "" || $_POST['post_body'] == "") {
        // form is incomplete
        $_SESSION['incomplete_post_err'] = "Please complete all fields!";
        header("Location: newPost.php");
        exit();
    }

    $target_dir = "../uploads/";
    $local_file_name = basename($_FILES["imgToUpload"]["name"]);
    $target_file = $target_dir . $local_file_name;
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
    // Check if image file is a actual image or fake image
    $check = getimagesize($_FILES["imgToUpload"]["tmp_name"]);
    if($check !== false) {
        echo "File is an image - " . $check["mime"] . ".";
        $uploadOk = 1;
    } else {
        echo "File is not an image.";
        $uploadOk = 0;
    }

    // Check file size
    $maxImgSize = 20971520;     // in bytes, 20971520 bytes = ~ 20 megabytes
    if ($_FILES["fileToUpload"]["size"] > $maxImgSize) {
        // use session error incompelte_err
        echo "Sorry, your file is too large.";
        $uploadOk = 0;
        $_SESSION['incomplete_post_err'] = "Image size must be < 20MB";
        header("Location: newPost.php");
        exit();
    }

    // Allow certain file formats
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
        echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        $uploadOk = 0;
        // $_SESSION['incomplete_post_err'] = "Only jpg, jpeg & png files are allowed";
        $_SESSION['incomplete_post_err'] = $target_file;
        header("Location: newPost.php");
        exit();
    }

    // Attempt to upload image file
    if (!move_uploaded_file($_FILES["imgToUpload"]["tmp_name"], $target_file)) {   
        $_SESSION['incomplete_post_err'] = "There was a problem uploading the img";
        header("Location: newPost.php");
        exit();
    } 
    // else {
    //     echo "Sorry, there was an error uploading your file.";
    // }



    if ($stmt = $con->prepare('INSERT INTO posts (creator_id, creator_name, date, title, post_body, private, img_name) VALUES (?,?,?,?,?,?,?)')) {
        date_default_timezone_set('Canada/Pacific');  // php Canada/Pacific timezone is closest to Vancouver time
        // Storing in 24-hr time
        $date = date('Y-m-d H:i:s', time());
        $private = 1;
        if (!isset($_POST['private'])) {   // Must include this guard b/c bind_param won't take int and 'private' column on db is either 0 or 1
            $private = 0;
        }
        $stmt->bind_param('issssis', $_SESSION['id'], $_SESSION['name'], $date, $_POST['title'], $_POST['post_body'], $private, $local_file_name);    // assigning creator_id as the id of the currently logged-in user, if $_POST['private'] == 1 then it's set
        $stmt->execute();
        $_SESSION['post_id'] = mysqli_insert_id($con);  // assigns to the most recent post_id
        // echo $_SESSION['post_id'];
        header("Location: post.php?post_id={$_SESSION['post_id']}");
        exit();
    } 
    else {
        $_SESSION['error'] = "Failed to store post into db";
        echo 'Failed to store post into database';
    }
    $stmt->close();
    $con->close();

    // Use this to handle previous/next post situation instead of next/prev IDs
    // https://stackoverflow.com/questions/1446821/how-to-get-next-previous-record-in-mysql

?>




