<?php session_start(); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Forgot Password</title>
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
                        Forgot Password?
                    </h4>
                </div>
                
                <!-- Modal Body -->
                <div class="modal-body">
                    <form class="form-horizontal" role="form" action="sendPasswordResetEmail.php" method="POST"> 
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Email:</label>
                            <div class="col-sm-9">
                                <input type="email" class="form-control" placeholder="Email" name="email" maxlength="120" required/>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-xs-1"></div>
                            <button type="submit" class="btn btn-success col-xs-3">Submit</button>
                        </div>

                        <?php if (isset($_SESSION['forgotPassword_err'])): ?>
                            <div class="row" style="margin-top: 10px;">
                                <div class="col-xs-1"></div>
                                <p style="color: blue;"><?php echo $_SESSION['forgotPassword_err']; ?></p>
                            </div> 
                        <?php unset($_SESSION['forgotPassword_err']); endif; ?>
                    </form>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
