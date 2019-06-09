<?php  
    session_start();

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
    if (!isset($_POST['username'], $_POST['password'], $_POST['email'], $_POST['authKey'])) {
        // form is incomplete
        $_SESSION['registration_err'] = "Please complete the registration form!";
        header("Location: registerForm.php");
        exit();
    }

    // Invalid characters validation
    if (preg_match('/[A-Za-z0-9]+/', $_POST['username']) == 0) {
        // input username has invalid characters
        $_SESSION['registration_err'] = "Please use a valid username!";
        header("Location: registerForm.php");
        exit();
    }

    // Character length check
    if (strlen($_POST['password']) > 20 || strlen($_POST['password']) < 5) {
        // password must be btwn 5 to 20 characters
        $_SESSION['registration_err'] = "Password must be between 5-20 characters.";
        header("Location: registerForm.php");
        exit();
    }

    // Email Validation: checking if email is an actual email
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        // input email is not a valid email
        $_SESSION['registration_err'] = "Please use a valid email!";
        header("Location: registerForm.php");
        exit();
    }


    if ($stmt_Username = $con->prepare('SELECT id, password FROM accounts WHERE username = ?')) {
        // Binding 'username' parameter
        $stmt_Username->bind_param('s', $_POST['username']);
        $stmt_Username->execute();
        // Store result so can check if account exists in database
        $stmt_Username->store_result();

        if ($stmt_Username->num_rows > 0) {
            // Username already exists
            $_SESSION['registration_err'] = "Username already exists!";
            header("Location: registerForm.php");
            exit(); 
        } else {
            // First check if authKey is valid
            if (!password_verify($_POST['authKey'], '$2y$10$PhMWKkSI9TCKmWEjDKXOkOTxrN6MUeTO7w3796fuwdC60QjeoV1x.')) {
                // authKey is incorrect
                $_SESSION['registration_err'] = "Please contact the owner for a valid authKey";
                header("Location: registerForm.php");
                exit();
            }

            // Username does not exist & authKey is valid, create new account
            if ($stmt_newUser = $con->prepare('INSERT INTO accounts (username, password, email) VALUES (?, ?, ?)')) {
                $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $stmt_newUser->bind_param('sss', $_POST['username'], $password, $_POST['email']);
                $stmt_newUser->execute();
                // Store current username in a session var
                $_SESSION['loggedin'] = TRUE;
                $_SESSION['name'] = $_POST['username'];
                $_SESSION['id'] = $id;
                header("Location: ../index.php");
                exit();
                // echo 'you succesfuly registered, you can now login!'
            } else {
                // could not register, err with SQL stmt
            }
            $stmt_newUser->close();
        }
    } else {
        // err w/ SQL stmt, could not get users
    }
    $stmt_Username->close();
    $con->close();
    // SQL stmts close automatically at end of script?

?>



    