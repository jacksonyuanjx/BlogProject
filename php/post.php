<?php 
  session_start(); 
  
  if (isset($_GET['publicPost'])) {
      // Case where navigated to this page from the public blog.php page, will enter this case whether logged-in or not
      // Case of manipulating URL to have 'publicPost' to view private posts is handled after $private var is set, checking if post is private or not
      $_SESSION['post_id'] = $_GET['post_id'];
  } elseif (!isset($_SESSION['post_id'], $_SESSION['loggedin'])) {
      // Case where either no post_id assigned or user is not logged-in
      header('Location: login.php');
      exit();
  }

  // NOTE: might need to change condition for private posts
  // This page works by using the current session variable 'post_id' to render the corresponding post.
  // If the session has been destroyed or expired, this php script will force user the login and after
  // logging in, the user will be redirected to the home page instead of this post page.

  // There is a current post_id, so query the post's details from database
  $DATABASE_HOST = 'localhost';
	$DATABASE_USER = 'root';
	$DATABASE_PASS = '';
	$DATABASE_NAME = 'blog_db';

	$con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
	if ( mysqli_connect_errno() ) {
		// If there is an error with the connection, stop the script and display the error.
		die ('Failed to connect to MySQL: ' . mysqli_connect_error());
  }
  
  // Guard that retrieves URL parameter (if it exists) and reassigns the current session var 'post_id' - represents the current post
  // This guard accounts for users pressing the 'previous post' or 'next post' buttons
  if (isset($_GET['post_id'])) {
    // if ($_GET['post_id'] != NULL) {
      $_SESSION['post_id'] = $_GET['post_id'];
    // }
  }

  if ($stmt_postDetails = $con->prepare('SELECT creator_id, creator_name, date, title, post_body, private FROM posts WHERE post_id = ?')) {
      // Binding parameter
      $stmt_postDetails->bind_param('i', $_SESSION['post_id']);
      $stmt_postDetails->execute();
      // $stmt_postDetails->store_result();  // Storing result to assign to variables for displaying in html
      
      // Assigning query results into variables
      $stmt_postDetails->bind_result($creator_id, $creator_name, $date, $title, $post_body, $private);   
      $stmt_postDetails->fetch();
      $stmt_postDetails->close();
      // echo date_create_from_format('Y-m-d H:i:s', $date);
      // echo $date;
  } else {
      echo "stmt_postDetails failed";
  }

  // If no one is logged in and post is private, force log-in
  if (!isset($_SESSION['loggedin']) && $private == 1) {
      header("Location: login.php");
      exit();
  }

  $dateObj = strtotime($date);
  if (isset($_SESSION['id'], $_SESSION['loggedin'])) {
      // Query selects the prev non-private post by post_id, ASSUMPTION: post_id increments accordingly with datetimes
      // Comparing post_id in stmt condition b/c int comparisons are faster than string comparisons
      if ($stmt_prevPost = $con->prepare("SELECT post_id, title FROM posts WHERE post_id < {$_SESSION['post_id']} AND (creator_id = {$_SESSION['id']} OR private = 0) ORDER BY post_id DESC LIMIT 1")) {
          // $stmt_prevPost->execute();
          // // $stmt_prevPost->store_result();    // unnecessary? don't need b/c only looking for one result/row not a bunch of them?
          // $stmt_prevPost->bind_result($prev_id, $prev_title);
          // $stmt_prevPost->fetch();
      } else {
          $_SESSION['failed to prepare prevPost'];
          echo "stmt_prevPost failed";
      }
      // $stmt_prevPost->close();

      // Query selects the next non-private post by post_id
      if ($stmt_nextPost = $con->prepare("SELECT post_id, title FROM posts WHERE post_id > {$_SESSION['post_id']} AND (creator_id = {$_SESSION['id']} OR private = 0) ORDER BY post_id ASC LIMIT 1")) {
          // $stmt_nextPost->execute();
          // $stmt_nextPost->bind_result($next_id, $next_title);
          // $stmt_nextPost->fetch();
      } else {
          $_SESSION['failed to prepare nextPost'];
          echo "stmt_nextPost failed";
      }
      // $stmt_nextPost->close();

      // Query that retrieves the 3 most recent posts (by date) to display max 3 in the 'Latest Posts' section
      if ($stmt_latest = $con->prepare("SELECT post_id, creator_name, date, title, private, num_comments FROM posts WHERE creator_id = {$_SESSION['id']} OR private = 0 ORDER BY date DESC LIMIT 0,3")) {
      } else {
          echo "failed query1";
      }
  } else {
      // Query selects the prev non-private post by post_id
      if ($stmt_prevPost = $con->prepare("SELECT post_id, title FROM posts WHERE post_id < {$_SESSION['post_id']} AND private = 0 ORDER BY post_id DESC LIMIT 1")) {
      } else {
          $_SESSION['failed to prepare prevPost'];
          echo "stmt_prevPost failed";
      }

      // Query selects the next non-private post by post_id
      if ($stmt_nextPost = $con->prepare("SELECT post_id, title FROM posts WHERE post_id > {$_SESSION['post_id']} AND private = 0 ORDER BY post_id ASC LIMIT 1")) {
      } else {
          $_SESSION['failed to prepare nextPost'];
          echo "stmt_nextPost failed";
      }

      // Query that retrieves the 3 most recent PUBLIC posts (by post_id) to display max 3 in the 'Latest Posts' section
      if ($stmt_latest = $con->prepare("SELECT post_id, creator_name, date, title, private, num_comments FROM posts WHERE private = 0 ORDER BY date DESC LIMIT 0,3")) {
      } else {
          echo "failed query1";
      }
  }

  // Executing the set of post-related stmts
  if ($stmt_prevPost != false || $stmt_prevPost != NULL) {
      $stmt_prevPost->execute();
      // $stmt_prevPost->store_result();    // unnecessary? don't need b/c only looking for one result/row not a bunch of them?
      $stmt_prevPost->bind_result($prev_id, $prev_title);
      $stmt_prevPost->fetch();
      $stmt_prevPost->close();    // Make sure to close this stmt before executing the next
  }

  if ($stmt_nextPost != false || $stmt_nextPost != NULL) {
      $stmt_nextPost->execute();
      $stmt_nextPost->bind_result($next_id, $next_title);
      $stmt_nextPost->fetch();
      $stmt_nextPost->close();
  }

  if ($stmt_latest != false || $stmt_latest != NULL) {
      $stmt_latest->execute();
      $latest = $stmt_latest->get_result();
      if ($latest->num_rows > 0) {
          while ($row = mysqli_fetch_array($latest)) {
              $results_latest[] = $row;
          }
      }
      $stmt_latest->close();
  }

  // Query that gets all the comments
  if ($stmt_comments = $con->prepare("SELECT comment_id, commenter_name, date, comment_body FROM comments WHERE post_id = ? ORDER BY date DESC")) {
      $stmt_comments->bind_param('i', $_GET['post_id']);
      $stmt_comments->execute();
      $comments = $stmt_comments->get_result();
      if ($comments->num_rows > 0) {
          while ($row = mysqli_fetch_array($comments)) {
              $res_comments[] = $row;   // Storing all comments in an array
          }
      }
      $stmt_comments->close();
  } else { 
      echo "Failed to query comments";
  }
  $_SESSION['post_id'] = $_GET['post_id'];


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
              <li class="nav-item"><a href="blog.php" class="nav-link ">Blog</a></li>
              <!-- <li class="nav-item"><a href="post.php" class="nav-link ">Post</a></li> -->
              <?php if (isset($_SESSION['loggedin']) && isset($_SESSION['name'])): ?>
                <li class="nav-item"><a href="yourPosts.php" class="nav-link ">Your Posts</a></li>
                <li class="nav-item"><a href="newPost.php" class="nav-link ">New Post</a></li>
                <li class="nav-item"><a href="profile.php" class="nav-link"><i class="fas fa-user-circle"></i>&nbsp<?php echo $_SESSION['name']?></a></li>
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
    <div class="container">
      <div class="row">
        <!-- Current Post -->
        <main class="post blog-post col-lg-8"> 
          <div class="container">
            <div class="post-single">
              <div class="post-thumbnail"><img src="../img/blog-post-3.jpeg" alt="..." class="img-fluid"></div>
              <div class="post-details">
                <!-- <div class="post-meta d-flex justify-content-between">
                  <div class="category"><a href="#">Business</a><a href="#">Financial</a></div>
                </div> -->
                <h1>
                  <?php echo $title; ?>
                  <!-- <a href="#"><i class="fa fa-bookmark-o"></i></a> -->
                </h1>
                <div class="post-footer d-flex align-items-center flex-column flex-sm-row"><a href="#" class="author d-flex align-items-center flex-wrap">
                    <div class="avatar"><img src="../img/user.svg" alt="..." class="img-fluid"></div>
                    <div class="title"><span><?php echo $creator_name; ?></span></div></a>
                  <div class="d-flex align-items-center flex-wrap">       
                    <div class="date"><i class="icon-clock"></i><?php echo date_format(date_create($date), "h:i A | d-M Y"); ?></div>
                    <!-- <div class="views"><i class="icon-eye"></i> 500</div> -->
                    <div class="comments "><i class="icon-comment"></i><?php echo $comments->num_rows; ?></div>
                    <div class="title meta-last">
                      <?php if ($private == 1) {
                        ?><i class="fas fa-lock"></i> Private<?php 
                      } else {
                        ?><i class="fas fa-unlock"></i> Public<?php
                      } ?>
                    </div>
                  </div>
                </div>
                <div class="post-body">
                  <?php echo $post_body; ?>
                  <!-- <p class="lead">Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
                  <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
                  <p> <img src="../img/featured-pic-3.jpeg" alt="..." class="img-fluid"></p>
                  <h3>Lorem Ipsum Dolor</h3>
                  <p>div Lorem ipsum dolor sit amet, consectetur adipisicing elit. Assumenda temporibus iusto voluptates deleniti similique rerum ducimus sint ex odio saepe. Sapiente quae pariatur ratione quis perspiciatis deleniti accusantium</p>
                  <blockquote class="blockquote">
                    <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip.</p>
                    <footer class="blockquote-footer">Someone famous in
                      <cite title="Source Title">Source Title</cite>
                    </footer>
                  </blockquote>
                  <p>quasi nam. Libero dicta eum recusandae, commodi, ad, autem at ea iusto numquam veritatis, officiis. Accusantium optio minus, voluptatem? Quia reprehenderit, veniam quibusdam provident, fugit iusto ullam voluptas neque soluta adipisci ad.</p> -->
                </div>
                <!-- <div class="post-tags"><a href="#" class="tag">#Business</a><a href="#" class="tag">#Tricks</a><a href="#" class="tag">#Financial</a><a href="#" class="tag">#Economy</a></div> -->
                <div class="posts-nav d-flex justify-content-between align-items-stretch flex-column flex-md-row">
                    <!-- PREV POST -->
                    <?php if ($prev_title != NULL): ?>
                      <a href="post.php?post_id=<?php echo $prev_id ?>&publicPost=1" class="prev-post text-left d-flex align-items-center">
                      <div class="icon prev"><i class="fa fa-angle-left"></i></div>
                      <div class="text"><strong class="text-primary">Previous Post </strong>
                        <h6><?php echo $prev_title; ?></h6>
                      </div></a>
                    <?php else: ?>
                      <!-- No more posts  -->
                      <div class="text"></div>
                    <?php endif; ?>

                    <!-- NEXT POST -->
                    <?php if ($next_title != NULL): ?>
                      <a href="post.php?post_id=<?php echo $next_id ?>&publicPost=1" class="next-post text-right d-flex align-items-center justify-content-end">
                      <div class="text"><strong class="text-primary">Next Post </strong>
                        <h6><?php echo $next_title; ?></h6>
                      </div>
                      <div class="icon next"><i class="fa fa-angle-right"></i></div></a>
                    <?php else: ?>
                      <!-- No more posts -->
                      <div class="text"></div>
                    <?php endif; ?>
                </div>


                <!-- JS that sends cookie storing comment_id to delete for access in deleteComment.php -->
                <script type="text/Javascript">
                  $(document).ready(function() {
                    console.log("ready");
                    $(document).on("click", ".open-DeleteCommentModal", function () {
                        var comment_id = $(this).data('id');
                        $(".modal-footer #comment_id").val( comment_id );
                        console.log("clicked on: " + comment_id);
                        document.cookie = "comment_id = " + comment_id;
                        
                    });
                  });
                </script>

                <!-- Display comments -->
                <header style="margin-top: 2em;">
                    <a name="anchorAfterDeleteComment" style="width=0px; height=0px;"></a>
                    <h3 class="h6">Post Comments<span class="no-of-comments"> &nbsp;&nbsp;(<?php echo $comments->num_rows; ?>)</span></h3>
                </header>
                
                <?php if (isset($res_comments)): ?>
                  <div class="post-comments" style="overflow: auto; height: 375px;">  <!-- Height before scrollbar appears roughly allows 3 comments -->
                  <!-- <header> <h3 class="h6">Post Comments<span class="no-of-comments">(3)</span></h3> </header> -->
                  <?php for ($i = 0; $i < sizeof($res_comments); $i++) { ?>
                    <div class="comment">
                      <div class="comment-header d-flex justify-content-between">
                        <div class="user d-flex align-items-center">
                          <div class="image"><img src="../img/user.svg" alt="..." class="img-fluid rounded-circle"></div>
                          <div class="title">
                              <strong>
                              <?php echo $res_comments[$i]['commenter_name']; ?>

                                
                              </strong>
                            <span class="date">
                              <?php echo date_format(date_create($res_comments[$i]['date']), "h:i A | d-M Y "); ?>
                            </span>
                          </div>
                        </div>
                      </div>
                      <div class="comment-body">
                        <p><?php echo $res_comments[$i]['comment_body']; ?></p>
                        <?php if (isset($_SESSION['loggedin']) && $creator_id == $_SESSION['id'] && $creator_name == $_SESSION['name']): ?>
                          <span>
                            <a class="open-DeleteCommentModal" data-id="<?php echo $res_comments[$i]['comment_id']; ?>" href="#myModal" data-toggle="modal" data-target="#myModal" style="border:none;"><i class="far fa-trash-alt"></i></a>
                          </span>
                        <?php endif; ?>  
                      </div>
                    </div> <?php
                  } ?>
                  </div>
                <?php endif; ?>

                <div id="myModal" class="modal fade">
                  <div class="modal-dialog">
                    <!-- Modal content-->
                    <div class="modal-content">
                      <div class="modal-header">
                          <button type="button" class="close" data-dismiss="modal">&times;</button>
                          <h4 class="modal-title">Delete this comment?</h4>
                      </div>
                      <div class="modal-body">
                        <p style="text-transform: none;">You cannot retrieve a deleted comment. Are you sure you would like to proceed?</p>
                      </div>
                      <div class="modal-footer">
                        <a name="comment_id" id="comment_id" href="deleteComment.php?post_id=<?php echo $_GET['post_id']; if (isset($_GET['publicPost']) && $_GET['publicPost'] == 1): ?>&publicPost=1<?php endif; ?>" class="deleteBtn">Delete</a>
                        <a class="closeBtn" data-dismiss="modal">Close</a>
                      </div>
                    </div>
                  </div>
                </div>
                

                <!-- Add a comment -->
                <div class="add-comment">
                  <header>
                    <h3 class="h6">Leave a reply</h3>
                  </header>
                  <form action="recordComment.php?post_id=<?php echo $_GET['post_id']; if (isset($_GET['publicPost']) && $_GET['publicPost'] == 1): ?>&publicPost=1<?php endif; ?>" class="commenting-form" method="POST">
                    <div class="row">
                      <div class="form-group col-md-6">
                        <input type="text" name="name" id="username" placeholder="Name" class="form-control" maxlength="50" required>
                      </div>
                      <!-- <div class="form-group col-md-6">
                        <input type="email" name="username" id="useremail" placeholder="Email Address (will not be published)" class="form-control">
                      </div> -->
                      <div class="form-group col-md-12">
                        <textarea name="comment_body" id="usercomment" placeholder="Type your comment" class="form-control" maxlength="255" required></textarea>
                      </div>
                      <div class="form-group col-md-12">
                        <button type="submit" class="btn btn-secondary">Submit Comment</button>
                        <?php if (isset($_SESSION['incomplete_comment_err'])): ?><
                          <p style="display: inline; color: blue;"><?php echo $_SESSION['incomplete_comment_err']; ?></p>
                        <?php unset($_SESSION['incomplete_comment_err']); endif; ?>
                      </div>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </main>
        <aside class="col-lg-4">
          <!-- Widget [Search Bar Widget]-->
          <div class="widget search">
            <header>
              <h3 class="h6">Search the blog</h3>
            </header>
            <form action="#" class="search-form">
              <div class="form-group">
                <input type="search" placeholder="What are you looking for?">
                <button type="submit" class="submit"><i class="icon-search"></i></button>
              </div>
            </form>
          </div>
          <!-- Widget [Latest Posts Widget]        -->
          <div class="widget latest-posts">
            <header>
              <h3 class="h6">Latest Posts</h3>
            </header>
            <div class="blog-posts">
              <?php if (isset($results_latest)) {
                for ($i = 0; $i < sizeof($results_latest); $i++) { ?>
                  <a href="post.php?post_id=<?php echo $results_latest[$i]['post_id']; if ($results_latest[$i]['private'] == 0): ?>&publicPost=1<?php endif; ?>">
                    <div class="item d-flex align-items-center">
                      <div class="image"><img src="../img/mountains.jpg" alt="..." class="img-fluid"></div>
                      <div class="title"><strong><?php echo $results_latest[$i]['title']; ?></strong>
                        <div class="d-flex align-items-center">
                          <div class="views"><i class="fas fa-user-circle"></i><?php echo $results_latest[$i]['creator_name']; ?></div>
                          <div class="views"><?php echo date_format(date_create($results_latest[$i]['date']), "d-M | Y"); ?></div>
                          <div class="comments"><i class="icon-comment"></i><?php echo $results_latest[$i]['num_comments']; ?></div>
                        </div>
                      </div>
                    </div>
                  </a><?php
                }
              } ?>
            </div>
          </div>
        </aside>
      </div>
    </div>
    <!-- Page Footer-->
    <footer class="main-footer">
      <div class="container">
        <div class="row">
          <div class="col-md-4">
            <div class="logo">
              <h6 class="text-white">Bootstrap Blog</h6>
            </div>
            <div class="contact-details">
              <p>53 Broadway, Broklyn, NY 11249</p>
              <p>Phone: (020) 123 456 789</p>
              <p>Email: <a href="mailto:info@company.com">Info@Company.com</a></p>
              <ul class="social-menu">
                <li class="list-inline-item"><a href="#"><i class="fa fa-facebook"></i></a></li>
                <li class="list-inline-item"><a href="#"><i class="fa fa-twitter"></i></a></li>
                <li class="list-inline-item"><a href="#"><i class="fa fa-instagram"></i></a></li>
                <li class="list-inline-item"><a href="#"><i class="fa fa-behance"></i></a></li>
                <li class="list-inline-item"><a href="#"><i class="fa fa-pinterest"></i></a></li>
              </ul>
            </div>
          </div>
          <div class="col-md-4">
            <div class="menus d-flex">
              <ul class="list-unstyled">
                <li> <a href="#">My Account</a></li>
                <li> <a href="#">Add Listing</a></li>
                <li> <a href="#">Pricing</a></li>
                <li> <a href="#">Privacy &amp; Policy</a></li>
              </ul>
              <ul class="list-unstyled">
                <li> <a href="#">Our Partners</a></li>
                <li> <a href="#">FAQ</a></li>
                <li> <a href="#">How It Works</a></li>
                <li> <a href="#">Contact</a></li>
              </ul>
            </div>
          </div>
          <div class="col-md-4">
            <div class="latest-posts"><a href="#">
                <div class="post d-flex align-items-center">
                  <div class="image"><img src="../img/mountains.jpg" alt="..." class="img-fluid"></div>
                  <div class="title"><strong>Hotels for all budgets</strong><span class="date last-meta">October 26, 2016</span></div>
                </div></a><a href="#">
                <div class="post d-flex align-items-center">
                  <div class="image"><img src="../img/mountains.jpg" alt="..." class="img-fluid"></div>
                  <div class="title"><strong>Great street atrs in London</strong><span class="date last-meta">October 26, 2016</span></div>
                </div></a><a href="#">
                <div class="post d-flex align-items-center">
                  <div class="image"><img src="../img/mountains.jpg" alt="..." class="img-fluid"></div>
                  <div class="title"><strong>Best coffee shops in Sydney</strong><span class="date last-meta">October 26, 2016</span></div>
                </div></a></div>
          </div>
        </div>
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
    <!-- Javascript files-->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.3/umd/popper.min.js"> </script>
    <script src="../vendor/bootstrap/js/bootstrap.min.js"></script>
    <script src="../vendor/jquery.cookie/jquery.cookie.js"> </script>
    <script src="../vendor/@fancyapps/fancybox/jquery.fancybox.min.js"></script>
    <script src="../js/front.js"></script>
  </body>
</html>