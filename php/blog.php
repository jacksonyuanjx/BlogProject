<?php 
    session_start(); 
    // This page does not require user log-in b/c it should display all public posts and 
    // is always viewable

    $DATABASE_HOST = 'localhost';
    $DATABASE_USER = 'root';
    $DATABASE_PASS = '';
    $DATABASE_NAME = 'blog_db';

	  $con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
	  if ( mysqli_connect_errno() ) {
		// If there is an error with the connection, stop the script and display the error.
		    die ('Failed to connect to MySQL: ' . mysqli_connect_error());
    }

    // If page number is not set, then set it to the first page
    if (!isset($_GET['page'])) {
        $_GET['page'] = 1;
    }

    // Query that retrieves the total number of posts
    if ($stmt_numPosts = $con->prepare('SELECT COUNT(*) AS total FROM posts WHERE private = 0')) {
        $stmt_numPosts->execute();
        $res = $stmt_numPosts->get_result();
        $row = $res->fetch_object();

        // Assigning variables for pagination
        $totalNumPosts = $row->total;
        $totalNumPages = ceil($totalNumPosts / 4);  // Displaying 4 posts per page
    }
    $stmt_numPosts->close();

    // Query that retrieves the 4 most recent posts by currently logged-in user in order from most recent --> least recent, depending on current page
    $limit_offset = ($_GET['page'] - 1) * 4;
    $limit_count = ($totalNumPosts - $limit_offset) < 4 ? $totalNumPosts - $limit_offset : 4;
    if ($stmt_recentPosts = $con->prepare("SELECT post_id, creator_name, date, title, post_body, private FROM posts WHERE private = 0 ORDER BY date DESC LIMIT " . $limit_offset . "," . $limit_count)) {
        $stmt_recentPosts->execute();
        $result = $stmt_recentPosts->get_result();
        if ($result->num_rows > 0) {
            while ($row = mysqli_fetch_array($result)) {
                $results[] = $row;
                // echo $row['post_id'] . "<br/>;
            }
            // echo $results[0]['post_id'];
        } else {
          echo "it's 0";
        }
    } else {
        echo "failed query";
    }
    $stmt_recentPosts->close();
    
    // Query that retrieves the 3 most recent PUBLIC posts to display max 3 in the 'Latest Posts" section
    if ($stmt_latest = $con->prepare('SELECT post_id, creator_name, date, title, private FROM posts WHERE private = 0 ORDER BY date DESC LIMIT 0,3')) {
        $stmt_latest->execute();
        $result_latest = $stmt_latest->get_result();
        if ($result_latest->num_rows > 0) {
            while ($row = mysqli_fetch_array($result_latest)) {
                $results_latest[] = $row;
            }
        }
    } else {
        echo "failed query 2";
    }
    $stmt_latest->close();

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
            <!-- Navbar Brand --><a href="index.php" class="navbar-brand">BlogProject</a>
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
                <li class="nav-item"><a href="newPost.php" class="nav-link">New Post</a></li>
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
        <!-- Posts (4 per page) -->
        <main class="posts-listing col-lg-8"> 
          <div class="container">
            <div class="row">
              <!-- post -->
              <?php if (isset($results)) {
                for ($i = 0; $i < $limit_count; $i++) { ?>
                  <div class="post col-xl-6">
                    <div class="post-thumbnail"><a href="post.php?post_id=<?php echo $results[$i]['post_id']; ?>&publicPost=1"><img src="../img/mountains.jpg" alt="..." class="img-fluid"></a></div>
                    <div class="post-details">
                      <div class="post-meta d-flex justify-content-between">
                        <div class="date meta-last"><?php echo date_format(date_create($results[$i]['date']), "d-M | Y"); ?></div>
                        <!-- <div class="category"><a href="#">Business</a></div> -->
                        <!-- UPDATE THIS LINK BELOW -->
                      </div><a href="post.php?post_id=<?php echo $results[$i]['post_id']; ?>&publicPost=1">
                        <h3 class="h4"><?php echo $results[$i]['title']; ?></h3></a>
                      <!-- Displaying the first 100 characters of post, essentially a summary -->
                      <p class="text-muted"><?php echo substr($results[$i]['post_body'], 0, 100) . "..."; ?></p>  
                      <footer class="post-footer d-flex align-items-center"><a href="post.php?post_id=<?php echo $results[$i]['post_id']; ?>&publicPost=1" class="author d-flex align-items-center flex-wrap">
                          <div class="avatar"><img src="../img/avatar-3.jpg" alt="..." class="img-fluid"></div>
                          <div class="title"><span><?php echo $results[$i]['creator_name']; ?></span></div></a>
                        <div class="title">
                          <?php if ($results[$i]['private'] == 1) {
                            ?><i class="fas fa-lock"></i> Private<?php 
                          } else {
                            ?><i class="fas fa-unlock"></i> Public<?php
                          } ?>
                        </div>
                        <div class="comments meta-last"><i class="icon-comment"></i>12</div>
                      </footer>
                    </div>
                  </div>
                <?php
                }
              } else {
                // Case where there are 0 posts
                ?><h3 class="h3 text-center">You have no posts so far!</h3><?php
              } ?>
              <!-- post             -->
              <!-- <div class="post col-xl-6">
                <div class="post-thumbnail"><a href="post.html"><img src="../img/blog-post-2.jpg" alt="..." class="img-fluid"></a></div>
                <div class="post-details">
                  <div class="post-meta d-flex justify-content-between">
                    <div class="date meta-last">20 May | 2016</div>
                    <div class="category"><a href="#">Business</a></div>
                  </div><a href="post.html">
                    <h3 class="h4">Alberto Savoia Can Teach You About Interior</h3></a>
                  <p class="text-muted">Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore.</p>
                  <div class="post-footer d-flex align-items-center"><a href="#" class="author d-flex align-items-center flex-wrap">
                      <div class="avatar"><img src="../img/avatar-2.jpg" alt="..." class="img-fluid"></div>
                      <div class="title"><span>John Doe</span></div></a>
                    <div class="date"><i class="icon-clock"></i> 2 months ago</div>
                    <div class="comments meta-last"><i class="icon-comment"></i>12</div>
                  </div>
                </div>
              </div> -->
            </div>
            <!-- Pagination -->
            <nav aria-label="Page navigation example">
              <ul class="pagination pagination-template d-flex justify-content-center">
                <!-- php script below handles pagination displays, etc -->
                <?php if ($limit_offset != 0) {
                  // Guaranteed at this point that there is a previous page so can do $_GET['page'] -1
                  ?><li class="page-item"><a href="blog.php?page=<?php echo $_GET['page']-1; ?>" class="page-link"> <i class="fa fa-angle-left"></i></a></li><?php
                } ?>
                <?php if ($totalNumPages != 0) {
                  for ($i = 1; $i <= $totalNumPages; $i++) {
                    if ($i == 1 || $i == $totalNumPages || ($i >= $_GET['page'] - 2 && $i <= $_GET['page'] + 2)) {
                      if ($i == $_GET['page']) {
                        ?><li class="page-item"><a href="blog.php?page=<?php echo $i; ?>" class="page-link active"><?php echo $i ?></a></li><?php
                      } else {
                        ?><li class="page-item"><a href="blog.php?page=<?php echo $i; ?>" class="page-link"><?php echo $i ?></a></li><?php
                      } 
                    }
                  }
                } else {
                  // Case where there are 0 posts
                  ?><li class="page-item"><a href="blog.php?page=<?php echo $i; ?>" class="page-link active">1</a></li><?php
                } ?>
                <?php if ($_GET['page'] != $totalNumPages) {
                  ?><li class="page-item"><a href="blog.php?page=<?php echo $_GET['page']+1; ?>" class="page-link"> <i class="fa fa-angle-right"></i></a></li><?php
                } ?>
              </ul>
            </nav>
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
                          <div class="comments"><i class="icon-comment"></i>12</div>
                        </div>
                      </div>
                    </div>
                  </a><?php
                }
              } ?>
                <!-- <a href="#">
                  <div class="item d-flex align-items-center">
                    <div class="image"><img src="img/small-thumbnail-2.jpg" alt="..." class="img-fluid"></div>
                    <div class="title"><strong>Alberto Savoia Can Teach You About</strong>
                      <div class="d-flex align-items-center">
                        <div class="views"><i class="icon-eye"></i> 500</div>
                        <div class="comments"><i class="icon-comment"></i>12</div>
                      </div>
                    </div>
                  </div>
                </a>
                <a href="#">
                  <div class="item d-flex align-items-center">
                    <div class="image"><img src="img/small-thumbnail-3.jpg" alt="..." class="img-fluid"></div>
                    <div class="title"><strong>Alberto Savoia Can Teach You About</strong>
                      <div class="d-flex align-items-center">
                        <div class="views"><i class="icon-eye"></i> 500</div>
                        <div class="comments"><i class="icon-comment"></i>12</div>
                      </div>
                    </div>
                  </div>
                </a> -->
            </div>
          </div>
          <!-- Widget [Categories Widget]-->
          <!-- <div class="widget categories">
            <header>
              <h3 class="h6">Categories</h3>
            </header>
            <div class="item d-flex justify-content-between"><a href="#">Growth</a><span>12</span></div>
            <div class="item d-flex justify-content-between"><a href="#">Local</a><span>25</span></div>
            <div class="item d-flex justify-content-between"><a href="#">Sales</a><span>8</span></div>
            <div class="item d-flex justify-content-between"><a href="#">Tips</a><span>17</span></div>
            <div class="item d-flex justify-content-between"><a href="#">Local</a><span>25</span></div>
          </div> -->
          <!-- Widget [Tags Cloud Widget]-->
          <!-- <div class="widget tags">       
            <header>
              <h3 class="h6">Tags</h3>
            </header>
            <ul class="list-inline">
              <li class="list-inline-item"><a href="#" class="tag">#Business</a></li>
              <li class="list-inline-item"><a href="#" class="tag">#Technology</a></li>
              <li class="list-inline-item"><a href="#" class="tag">#Fashion</a></li>
              <li class="list-inline-item"><a href="#" class="tag">#Sports</a></li>
              <li class="list-inline-item"><a href="#" class="tag">#Economy</a></li>
            </ul>
          </div> -->
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
                  <div class="image"><img src="img/small-thumbnail-1.jpg" alt="..." class="img-fluid"></div>
                  <div class="title"><strong>Hotels for all budgets</strong><span class="date last-meta">October 26, 2016</span></div>
                </div></a><a href="#">
                <div class="post d-flex align-items-center">
                  <div class="image"><img src="img/small-thumbnail-2.jpg" alt="..." class="img-fluid"></div>
                  <div class="title"><strong>Great street atrs in London</strong><span class="date last-meta">October 26, 2016</span></div>
                </div></a><a href="#">
                <div class="post d-flex align-items-center">
                  <div class="image"><img src="img/small-thumbnail-3.jpg" alt="..." class="img-fluid"></div>
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
    <script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.3/umd/popper.min.js"> </script>
    <script src="../vendor/bootstrap/js/bootstrap.min.js"></script>
    <script src="../vendor/jquery.cookie/jquery.cookie.js"> </script>
    <script src="../vendor/@fancyapps/fancybox/jquery.fancybox.min.js"></script>
    <script src="../js/front.js"></script>
  </body>
</html>