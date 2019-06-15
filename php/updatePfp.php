<?php
    session_start();

    if (!isset($_SESSION['loggedin'], $_SESSION['id'])) {
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

    $_SESSION['currPfpPath'] = $_COOKIE['currPfpPath'];
    setcookie('currPfpPath', "", time()-3600);


    if (isset($_FILES['pfpToUpload']['error'])) {
        if ($_FILES['pfpToUpload']['error'] != 4) {
            // A file has been uploaded

            $target_dir = "../uploads/";
            $local_file_name = pathinfo($_FILES["pfpToUpload"]["name"], PATHINFO_FILENAME);
            $extension_lower = strtolower(pathinfo($_FILES["pfpToUpload"]["name"], PATHINFO_EXTENSION));
            $target_file = $target_dir . $local_file_name . "." . $extension_lower;

            // Check if image file is a actual image or fake image
            $check = getimagesize($_FILES["pfpToUpload"]["tmp_name"]);
            if($check == false) {
                $_SESSION['pfp_err'] = "File is not an image";
                header("Location: profile.php");
                exit();
            }

            // Check file size
            define('MB', 1048576); // in bytes, 1048576 bytes = 1 megabyte
            if ($_FILES["pfpToUpload"]["size"] > 5*MB) {
                // use session error incompelte_err
                echo "Sorry, your file is too large.";
                $_SESSION['pfp_err'] = "Image size must be < 5MB";
                header("Location: profile.php");
                exit();
            }

            // Allow certain file formats
            if($extension_lower != "jpg" && $extension_lower != "png" && $extension_lower != "jpeg") {
                $_SESSION['pfp_err'] = "Only jpg, jpeg & png files are allowed";
                // $_SESSION['pfp_err'] = $target_file;
                header("Location: profile.php");
                exit();
            }

            // Checking for duplicates, if found duplicate then rename by incrementing an int following the filename
            $increment = "";
            $local_file_name = str_replace(" ", "", $local_file_name);      // Remove all spaces in name before checking, will store with new name that has no spaces
            while (file_exists($target_dir . $local_file_name . $increment . '.' . $extension_lower)) {
                $increment++;
            }
            $target_file = $target_dir . $local_file_name . $increment . "." . $extension_lower;

            // Attempt to upload image file
            if (!move_uploaded_file($_FILES["pfpToUpload"]["tmp_name"], $target_file)) {   
                $_SESSION['pfp_err'] = "There was a problem uploading the img";
                // $_SESSION['pfp_err'] = $target_file;
                header("Location: profile.php");
                exit();
            } else {
                // Image upload was successful
                // Delete the old profile pic to save space on database and prevent unused images
                if (isset($_SESSION['currPfpPath'])) {
                    if ($_SESSION['currPfpPath'] != "../uploads/default_user.png") {
                        if (!unlink($_SESSION['currPfpPath'])) {
                            // Deletion failed
                            $_SESSION['pfp_err'] = "Failed to delete previous profile pic. Please contact blog developer";
                            header("Location: profile.php");
                            exit();
                        }
                    }
                }
            }

        } else {
            // NO file has been uploaded
            $target_file = "";
            $_SESSION['pfp_err'] = "Please upload an image!";
            header("Location: profile.php");
            exit();
        }

        // At this point, image has been successfully uploaded so store file path into database
        // Query that updates the currently logged-in user's profile picture path
        if ($stmt = $con->prepare("UPDATE accounts SET pfp_path = ? WHERE id = ?")) {
            $stmt->bind_param("si", $target_file, $_SESSION['id']);
            $stmt->execute();
            $stmt->close();
            
            header("Location: profile.php");
        }
    }

    // Unset all newly declared session vars
    unset($_SESSION['currPfpPath']);

    $con->close();
?>