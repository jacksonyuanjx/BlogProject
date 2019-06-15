<?php
    // We need to use sessions, so you should always start sessions using the below code
	session_start();
	
    // If the user is not logged in redirect to the login page
    if (!isset($_SESSION['loggedin'])) {
        header('Location: ../index.php');
        exit();
	}
	
    $DATABASE_HOST = 'localhost';
    $DATABASE_USER = 'root';
    $DATABASE_PASS = '';
	$DATABASE_NAME = 'blog_db';
	
	// Attempt to connect
    $con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
    if (mysqli_connect_errno()) {
        die ('Failed to connect to MySQL: ' . mysqli_connect_error());
	}
	
	// Don't have the password or email info stored in sessions so instead we can get the results from the database.
	if ($stmt = $con->prepare("SELECT password, email, pfp_path FROM accounts WHERE id = ?")) {
		// In this case we can use the account ID to get the account info
		$stmt->bind_param('s', $_SESSION['id']);
		$stmt->execute();
		$stmt->bind_result($password, $email, $pfp_path);
		$stmt->fetch();
		$stmt->close();

		if (!isset($pfp_path) || $pfp_path == "" || $pfp_path == NULL) {
			$pfp_path = "../uploads/default_user.png";
		}
	}
	
	$con->close();

?>


<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>BlogProject</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="all,follow">
    <!-- Bootstrap CSS-->
    <link rel="stylesheet" href="../vendor/bootstrap/css/bootstrap.min.css">
    <!-- Font Awesome CSS-->
    <link rel="stylesheet" href="../vendor/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css">
    <!-- Custom icon font-->
    <link rel="stylesheet" href="../css/fontastic.css">
    <!-- Google fonts - Open Sans-->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,700">
    <!-- Fancybox-->
    <link rel="stylesheet" href="../vendor/@fancyapps/fancybox/jquery.fancybox.min.css">
    <!-- theme stylesheet-->
    <link rel="stylesheet" href="../css/style.default.css" id="theme-stylesheet">
    <!-- Custom stylesheet - for your changes-->
    <link rel="stylesheet" href="../css/custom.css">
    <!-- Favicon-->
    <!-- <link rel="shortcut icon" href="../favicon.png"> -->
	<script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>

    <!-- Tweaks for older IEs--><!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script><![endif]-->
  </head>
  <body>
  <header class="header">
      <!-- Main Navbar-->
      <nav class="navbar navbar-expand-lg">
        <div class="search-area">
          <div class="search-area-inner d-flex align-items-center justify-content-center">
            <div class="close-btn"><i class="icon-close"></i></div>
            <div class="row d-flex justify-content-center">
              <div class="col-md-8">
                <form action="#">
                  <div class="form-group">
                    <input type="search" name="search" id="search" placeholder="What are you looking for?">
                    <button type="submit" class="submit"><i class="icon-search-1"></i></button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
        <div class="container">
          <!-- Navbar Brand -->
          <div class="navbar-header d-flex align-items-center justify-content-between">
            <!-- Navbar Brand --><a href="../index.php" class="navbar-brand">BlogProject</a>
            <!-- Toggle Button-->
            <button type="button" data-toggle="collapse" data-target="#navbarcollapse" aria-controls="navbarcollapse" aria-expanded="false" aria-label="Toggle navigation" class="navbar-toggler"><span></span><span></span><span></span></button>
          </div>
          <!-- Navbar Menu -->
          <div id="navbarcollapse" class="collapse navbar-collapse">
            <ul class="navbar-nav ml-auto">
              <li class="nav-item"><a href="../index.php" class="nav-link ">Home</a></li>
              <li class="nav-item"><a href="blog.php" class="nav-link active">Blog</a></li>
              <!-- <li class="nav-item"><a href="post.php" class="nav-link ">Post</a></li> -->
              <?php if (isset($_SESSION['loggedin']) && isset($_SESSION['name'])): ?>
                <li class="nav-item"><a href="yourPosts.php" class="nav-link ">Your Posts</a></li>
                <li class="nav-item"><a href="newPost.php" class="nav-link"><i class="far fa-plus-square"></i> New Post</a></li>
                <li class="nav-item"><a href="profile.php" class="nav-link">
                  <i class="fas fa-user-circle"></i>&nbsp;
                  <?php echo substr($_SESSION['name'], 0, 15); ?></a>
                </li>
                <li class="nav-item"><a href="logout.php" class="nav-link">Logout</a></li>
              <?php elseif(!isset($_SESSION['loggedin']) || !isset($_SESSION['name'])): ?>
                <li class="nav-item"><a href="login.php" class="nav-link ">Login</a></li>
              <?php endif; ?>
            </ul>
            <div class="navbar-text"><a href="php/#" class="search-btn"><i class="icon-search-1"></i></a></div>
            <!-- <ul class="langs navbar-text"><a href="#" class="active">EN</a><span>           </span><a href="#">ES</a></ul> -->
          </div>
        </div>
      </nav>
	</header>
	
	<!-- JS script that stores current image path in cookie (when update btn is clicked) 
		to be accessed in updatePfp.php -->
	<script type="text/Javascript">
		$(document).ready(function() {
			$(document).on("click", "#updatePfpBtn", function () {
				var currPfpPath = $(this).attr("data-currPfpPath");
				document.cookie = "currPfpPath = " + currPfpPath;
			});
		});
	</script>

    <div class="container">
		<div class="row">
			<div class="col-sm">
				<div class="profileModal" style="padding-top: 20px; padding-bottom: 150px;">
					<h2 style="font-size: 2em;">Profile</h2>
					<img class="mx-auto rounded-circle" src="<?php echo $pfp_path; ?>" alt="" style="padding: 10px; width: 200px; height: 200px;">
					<div>
						<form action="updatePfp.php" method="POST" enctype="multipart/form-data">
							<i class='fas fa-file-upload' style="font-size: 1.25em;"> Change Photo:</i> 
							<input type="file" name="pfpToUpload" id="pfpToUpload">
							<input id="updatePfpBtn" data-currPfpPath="<?php echo $pfp_path; ?>"  type="submit" value="Update" name="submit" 
									style="margin: 10px; border-radius: 15px; border-width: thin; background-color: #12b733;">
							<?php if (isset($_SESSION['pfp_err'])): ?>
								<p style="color: blue;"><?php echo $_SESSION['pfp_err']; ?></p>
							<?php unset($_SESSION['pfp_err']); endif; ?>
						</form>
					</div>
					<div style="padding-top: 30px;">
						<p>Your account details are below:</p>
						<p style="word-wrap: break-word;"><strong>Username: </strong>&nbsp;<?=$_SESSION['name']?></p>
						<p style="word-wrap: break-word;"><strong>Password: </strong>&nbsp;<?=$password?></p>
						<p style="word-wrap: break-word;"><strong>Email: </strong>&nbsp;<?=$email?></p>
					</div>
				</div>
			</div>
		</div>
	</div>
	
    <!-- Page Footer-->
    <footer class="main-footer">
      <div class="container">
          <!-- Can include redirects to developer's page -->
      </div>
      <div class="copyrights">
        <div class="container">
          <div class="row">
            <div class="col-md-6">
              <p>&copy; 2017. All rights reserved. Your great site.</p>
            </div>
            <div class="col-md-6 text-right">
              <p>Template By <a href="https://bootstraptemple.com" class="text-white">Bootstrap Temple</a>
                <!-- Please do not remove the backlink to Bootstrap Temple unless you purchase an attribution-free license @ Bootstrap Temple or support us at http://bootstrapious.com/donate. It is part of the license conditions. Thanks for understanding :)                         -->
              </p>
            </div>
          </div>
        </div>
      </div>
    </footer>
    <!-- Javascript files -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.3/umd/popper.min.js"> </script>
    <script src="../vendor/bootstrap/js/bootstrap.min.js"></script>
    <script src="../vendor/jquery.cookie/jquery.cookie.js"> </script>
    <script src="../vendor/@fancyapps/fancybox/jquery.fancybox.min.js"></script>
    <script src="../js/front.js"></script>
  </body>
</html>

