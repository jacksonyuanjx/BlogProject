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

    if (isset($_FILES['imgToUpload']['error'])) {
        if ($_FILES['imgToUpload']['error'] != 4) {
            // A file has been uploaded

            $target_dir = "../uploads/";
            $local_file_name = pathinfo($_FILES["imgToUpload"]["name"], PATHINFO_FILENAME);
            $extension_lower = strtolower(pathinfo($_FILES["imgToUpload"]["name"], PATHINFO_EXTENSION));
            $target_file = $target_dir . $local_file_name . "." . $extension_lower;
            $uploadOk = 1;
            // $imageFileType = strtolower($extension);
            // Check if image file is a actual image or fake image
            $check = getimagesize($_FILES["imgToUpload"]["tmp_name"]);
            if($check == false) {
                $_SESSION['incomplete_post_err'] = "File is not an image";
                header("Location: newPost.php");
                exit();
                $uploadOk = 0;
            }

            // Check file size
            define('MB', 1048576);
            $maxImgSize = 1 * MB;     // in bytes, 20971520 bytes = ~ 20 megabytes
            if ($_FILES["imgToUpload"]["size"] > 5*MB) {
                // use session error incompelte_err
                echo "Sorry, your file is too large.";
                $uploadOk = 0;
                $_SESSION['incomplete_post_err'] = "Image size must be < 5MB";
                header("Location: newPost.php");
                exit();
            }

            // Allow certain file formats
            if($extension_lower != "jpg" && $extension_lower != "png" && $extension_lower != "jpeg") {
                $uploadOk = 0;
                $_SESSION['incomplete_post_err'] = "Only jpg, jpeg & png files are allowed";
                // $_SESSION['incomplete_post_err'] = $target_file;
                header("Location: newPost.php");
                exit();
            }

            // Attempt to upload image file
            $increment = "";
            $local_file_name = str_replace(" ", "", $local_file_name);      // Remove all spaces in name before checking, will store with new name that has no spaces
            while (file_exists($target_dir . $local_file_name . $increment . '.' . $extension_lower)) {
                $increment++;
            }
            $target_file = $target_dir . $local_file_name . $increment . "." . $extension_lower;

            // $target_file = str_replace(" ", "", $target_file);

            if (!move_uploaded_file($_FILES["imgToUpload"]["tmp_name"], $target_file)) {   
                $_SESSION['incomplete_post_err'] = "There was a problem uploading the img";
                // $_SESSION['incomplete_post_err'] = $target_file;
                header("Location: newPost.php");
                exit();
            } 

        } else {
            // NO file has been uploaded
            $local_file_name = "";
        }
    }



    if ($stmt = $con->prepare('INSERT INTO posts (creator_id, creator_name, date, title, post_body, private, img_path) VALUES (?,?,?,?,?,?,?)')) {
        date_default_timezone_set('Canada/Pacific');  // php Canada/Pacific timezone is closest to Vancouver time
        // Storing in 24-hr time
        $date = date('Y-m-d H:i:s', time());
        $private = 1;
        if (!isset($_POST['private'])) {   // Must include this guard b/c bind_param won't take int and 'private' column on db is either 0 or 1
            $private = 0;
        }
        $stmt->bind_param('issssis', $_SESSION['id'], $_SESSION['name'], $date, $_POST['title'], $_POST['post_body'], $private, $target_file);    // assigning creator_id as the id of the currently logged-in user, if $_POST['private'] == 1 then it's set
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




