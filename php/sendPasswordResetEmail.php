<?php
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;
    require 'C:\XAMPP\composer\vendor\autoload.php';
    require 'SMTPAuth.php';     // Must require SMTPAuth.php with class 'SMTPAuth' containing GMail Authentication

    session_start();

    // $_SESSION['forgotPassword_err'] = $_POST['email'];
    // header("Location: forgotPassword.php");
    // exit();
    if (isset($_POST['email']) && !empty($_POST['email'])) {
        $email = $_POST['email'];

        // Email Validation: checking if email is an actual email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            // Input email is not a valid email
            echo "test";
            $_SESSION['forgotPassword_err'] = "Please use the valid email for your account!";
            header("Location: forgotPassword.php");
            exit();
        } else {
            // Input email is valid
            
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

            if ($stmt = $con->prepare("SELECT id FROM accounts WHERE email = ?")) {
                $stmt->bind_param('s', $email);
                $stmt->execute();
                $stmt->store_result();
                if ($stmt->num_rows <= 0) {
                    $_SESSION['forgotPassword_err'] = "Email is not registered to an account!";
                    header("Location: forgotPassword.php");
                    exit();
                } else {
                    $key = md5($email);
                    $addKey = md5(uniqid(rand(),1));
                    $key = $key . $addKey;

                    $stmt->bind_result($id);
                    $stmt->fetch();
                    if ($stmt_storeKey = $con->prepare("INSERT INTO password_reset VALUES (?,?,?)")) {
                        $stmt_storeKey->bind_param('iss', $id, $email, $key);
                        $stmt_storeKey->execute();
                        $stmt_storeKey->close();
                    } else {
                        echo "stmt_storeKey failed";
                    }

                    $mail = new PHPMailer();    // Passing TRUE to constructor enables exceptions

                    // Using GMail free SMTP server :) 
                    try {
                        // Set the sender address
                        $mail->setFrom('leonixglow@gmail.com');
                        // Add a recipient address
                        $mail->addAddress($email);
                        // Set the email subject
                        $mail->Subject = "[NO-REPLY] Blog-PASSWORD-RESET [ACTION-REQUIRED]";
                        // Set the email body
                        $output ='<p>Please click on the following link to reset your password.</p>';
                        $output.='<p>-------------------------------------------------------------</p>';
                        $output.='<p><a href="localhost/BlogProject/php/resetPasswordForm.php?id='.$id.'&key='.$key.'&email='.$email.'&action=reset" target="_blank">
                        localhost/BlogProject/php/resetPasswordForm.php?key='.$key.'&email='.$email.'&action=reset</a></p>';		
                        $output.='<p>-------------------------------------------------------------</p>';
                        $output.='<p>Please be sure to copy the entire link into your browser.</p>';
                        $output.='<p>If you did not request this forgotten password email, no action is needed, your password will not be reset.</p>';   	
                        $output.='<p>Thanks,</p>';
                        $output.='<p>TOL Team</p>';
                        $body = $output; 

                        $mail->isHTML(true);
                        $mail->Body = $body;
                        // NOTE: include $mail->AltBody = .... 

                        $mail->isSMTP();
                        $mail->Host = 'smtp.gmail.com';
                        $mail->SMTPAuth = TRUE;
                        // $mail->SMTPDebug = SMTP::DEBUG_SERVER;
                        $mail->SMTPDebug = 2; //Alternative to above constant
                        $mail->SMTPSecure = 'tls';
                        $mail->Port = 587;
                        // $mail->Username = 'AKIA6IGLZKOVWACLM45L';
                        // $mail->Password = 'BFXXAnMRXutWK4A5N+2rGvv9pMl1yN01b2IQSFgW4mws';

                        // GMail user and pass stored in SMTPAuth.php which is not included on GitHub commit
                        $mail->Username = SMTPAuth::getUser();
                        $mail->Password = SMTPAuth::getPass();

                        // Send the email
                        if (!$mail->send()) {
                            echo $mail->ErrorInfo;
                        }

                        $_SESSION['forgotPassword_err'] = "A password reset link has been sent to your email";
                        header("Location: forgotPassword.php");
                        exit();
                   } catch (Exception $e) {
                       echo $e->errorMessage();
                   }

                    // $_SESSION['forgotPassword_err'] = $addKey;
                    // header("Location: forgotPassword.php");
                    // exit();
                }
                $stmt->close();
            }

            $con->close();
        }

    }

?>