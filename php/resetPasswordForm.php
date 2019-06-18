<?php 
    session_start(); 

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

    if (isset($_GET['key'], $_GET['email'])) {
        $id = $_GET['id'];
        $reset_key = $_GET['key'];
        $email = $_GET['email'];
        
        // Query that retrieves the row with matching email and reset_key
        if ($stmt_verify = $con->prepare("SELECT * FROM password_reset WHERE email = ? AND reset_key = ?")) {
            $stmt_verify->bind_param('ss', $email, $reset_key);
            $stmt_verify->execute();
            $stmt_verify->store_result();
            
            // Check if email and reset_key matches an eligible account for password reset
            if ($stmt_verify->num_rows <= 0) {
                // User NOT verified for password reset
                $_SESSION['forgotPassword_err'] = "The link is invalid. Either you did not copy the correct link or you have already used the key before.";
                header("Location: forgotPassword.php");
                exit();
            } 
            $stmt_verify->close();
        } else {
            echo "Query failed";
        }
    }

    $con->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Reset Password</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.0/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script>
</head>

<body style="background-color: grey;">

<div class="container">

  <!-- Modal -->
    <div id="myModalHorizontal" tabindex="-1" role="dialog" 
     aria-labelledby="myModalLabel" aria-hidden="true" style="padding-top: 100px;">
        <div class="modal-dialog">
            <div class="modal-content">
                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel">
                        Reset Password for <p style="color: blue;"><?php echo $email; ?></p>
                    </h4>
                </div>
                
                <!-- Modal Body -->
                <div class="modal-body">
                    <form class="form-horizontal" role="form" action="resetPassword.php?id=<?php echo $id; ?>&key=<?php echo $reset_key; ?>&email=<?php echo $email; ?>" method="POST"> 
                        <div class="form-group">
                            <label class="col-sm-4 control-label">New Password: </label>
                            <div class="col-sm-8">
                                <input type="password" class="form-control" placeholder="New password" name="newPass" maxlength="120" required/>
                            </div>

                            <div style="margin: 10px;"></div>

                            <label class="col-sm-4 control-label">Confirm Password: </label>
                            <div class="col-sm-8">
                                <input type="password" class="form-control" placeholder="Confirm password" name="newPassConfirm" maxlength="120" required/>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-xs-1"></div>
                            <button type="submit" class="btn btn-success col-xs-3">Submit</button>
                        </div>

                        <?php if (isset($_SESSION['resetPass_err'])): ?>
                            <div class="row" style="margin-top: 10px;">
                                <div class="col-xs-1"></div>
                                <p style="color: blue;"><?php echo $_SESSION['resetPass_err']; ?></p>
                            </div> 
                        <?php unset($_SESSION['resetPass_err']); endif; ?>
                    </form>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
