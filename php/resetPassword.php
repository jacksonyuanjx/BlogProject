<?php
    session_start();

    // Check if data was submitted, isset() will check if data exists
    if (!isset($_POST['newPass'], $_POST['newPassConfirm']) && $_POST['newPass'] != "" && $_POST['newPassConfirm'] != "") {
        // Form is incomplete
        $_SESSION['resetPass_err'] = "Please complete the form!";
        header("Location: resetPasswordForm.php");
        exit();
    }

    // Character length check
    if (strlen($_POST['newPass']) > 20 || strlen($_POST['newPass']) < 5) {
        // password must be btwn 5 to 20 characters
        $_SESSION['resetPass_err'] = "Password must be between 5-20 characters.";
        header("Location: resetPasswordForm.php");
        exit();
    }

    // Check if newPass and newPassConfirm are identical
    if ($_POST['newPass'] !== $_POST['newPassConfirm']) {
        $_SESSION['resetPass_err'] = "Passwords must be identical!";
        header("Location: resetPasswordForm.php");
        exit();
    }

    $DATABASE_HOST = 'localhost';
    $DATABASE_USER = 'root';
    $DATABASE_PASS = '';
    $DATABASE_NAME = 'blog_db';

    // Attempt to connect to database
    $con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
    if ( mysqli_connect_errno() ) {
        // If there is an error with the connection, stop the script and display the error.
        die ('Failed to connect to MySQL: ' . mysqli_connect_error());
    }

    // Query that resets the password for the corresponding account
    if ($stmt_resetPass = $con->prepare("UPDATE accounts SET password = ? WHERE id = ? AND email = ?")) {
        $updatedPass = password_hash($_POST['newPass'], PASSWORD_DEFAULT);
        $stmt_resetPass->bind_param('sis', $updatedPass, $_GET['id'], $_GET['email']);
        $stmt_resetPass->execute();
        $stmt_resetPass->close();

        // Query that deletes the reset record from password_reset table
        if ($stmt_deleteReset = $con->prepare("DELETE FROM password_reset WHERE acc_id = ? AND email = ? AND reset_key = ?")) {
            $stmt_deleteReset->bind_param('iss', $_GET['id'], $_GET['email'], $_GET['key']);
            $stmt_deleteReset->execute();
            $stmt_deleteReset->close();
        } else {
            $_SESSION['incorrect_login_err'] = "Failed to delete reset record. Contact developer!";
            header("Location: login.php");
            exit();
        }

        // Redirect to login page, using 'incorrect_login_err' session var to display msg but this isn't an err
        $_SESSION['incorrect_login_err'] = "Password has successfully been updated!";
        header("Location: login.php");
        exit();
    } else {
        $_SESSION['forgotPassword_err'] = "The link is invalid. Either you did not copy the correct link or you have already used the key before.";
        header("Location: forgotPassword.php");
        exit();
    }
    
    $con->close();

?>