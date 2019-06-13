
<?php 
    session_start();
    // If user not logged in, redirect to login page
    if (!isset($_SESSION['loggedin'])) {
        header('Location: login.php');
        exit();
    }
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <title>New Post</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/newPost.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.0/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script>
</head>

<body>

<div class="container">

  <!-- Modal -->
    <div id="myModalHorizontal" tabindex="-1" role="dialog" 
     aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel">
                        Create a new post
                    </h4>
                </div>
                
                <!-- Modal Body -->
                <div class="modal-body">
                    
                    <form class="form-horizontal" role="form" action="recordPost.php" method="POST" enctype="multipart/form-data"> 
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Title</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" placeholder="Title" name="title" maxlength="120" required/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Post</label>
                            <div class="col-sm-10">
                                <textarea class="form-control" name="post_body" cols="30" rows="3" required></textarea>
                            </div>
                        </div>
                        <!-- <div class="form-group">
                            <div class="col-sm-offset-2 col-sm-10">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox"/> Remember me
                                </label>
                            </div>
                            </div>
                        </div> -->
                        <div class="row">
                            <div class="col-xs-1"></div>
                            <button type="submit" class="btn btn-success col-xs-3">Submit</button>
                            <!-- <div class="col-md-4"></div> -->
                            <div class="checkbox col-xs-7">
                                <label>
                                    <input type="checkbox" name="private" value="1"/>Private Post
                                </label>
                            </div>
                            <div>
                                <br/><br/>
                                <i class='fas fa-file-upload' style="margin-left: 7.5px;"> Image:</i> 
                                <input type="file" name="imgToUpload" id="imgToUpload" style="margin-left: 7.5px;" >
                                <?php if (isset($_SESSION['incomplete_post_err'])): ?>
                                    <p style="color: blue; margin: 10px;"><?php echo $_SESSION['incomplete_post_err']?></p>
                                <?php unset($_SESSION['incomplete_post_err']); endif; ?>
                            </div>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

</body>
</html>
