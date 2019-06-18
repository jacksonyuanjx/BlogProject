<?php 
  session_start(); 
  
  $DATABASE_HOST = 'localhost';
  $DATABASE_USER = 'root';
  $DATABASE_PASS = '';
  $DATABASE_NAME = 'blog_db';

  $con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
  if ( mysqli_connect_errno() ) {
    // If there is an error with the connection, stop the script and display the error.
    die ('Failed to connect to MySQL: ' . mysqli_connect_error());
  }

  if (isset($_SESSION['loggedin'], $_SESSION['id'])) {

    // Query that retrieves the pfp_path of the currently logged-in user
    if ($stmt_pfp = $con->prepare("SELECT pfp_path FROM accounts WHERE id = ?")) {
      $stmt_pfp->bind_param('i', $_SESSION['id']); 
      $stmt_pfp->execute();
      $stmt_pfp->bind_result($pfp_path);
      $stmt_pfp->fetch();
      $stmt_pfp->close();

      $pfp_path = str_replace("../", "", $pfp_path);  // Remove "../" from "../uploads/..."
    }
  }

  // Assigns to default pfp if no pfp_path returned
  if (!isset($pfp_path) || $pfp_path == "" || $pfp_path == NULL) {
    $pfp_path = "img/default_user.png";
  }

  // LEFT JOIN Query that retrieves the 3 most recent posts and the corresponding author's pfp_path to display max 3 posts on home page
  if ($stmt_latest = $con->prepare("SELECT p.post_id, p.creator_name, p.date, p.title, p.post_body, p.private, p.num_comments, p.img_path, a.pfp_path FROM posts p LEFT JOIN accounts a ON p.creator_id = a.id WHERE p.private = 0 ORDER BY p.date DESC LIMIT 0,3")) {
      $stmt_latest->execute();
      $latest = $stmt_latest->get_result();
      if ($latest->num_rows > 0) {
          $i = 0;
          while ($row = mysqli_fetch_array($latest)) {
              $results_latest[] = $row;

              // If there's already an img assigned to the post, trim "../" from the path b/c index.php is in the root dir
              if (isset($results_latest[$i]['img_path']) || $results_latest[$i]['img_path'] != "" || $results_latest[$i]['img_path'] != NULL) {
                $results_latest[$i]['img_path'] = str_replace("../", "", $results_latest[$i]['img_path']);
              }
              
              // Assign default_user as pfp if no pfp_path returned, o.w. remove "../" from path since index.php is already in root dir
              if (!isset($results_latest[$i]['pfp_path']) || $results_latest[$i]['pfp_path'] == "" || $results_latest[$i]['pfp_path'] == NULL) {
                $results_latest[$i]['pfp_path'] = "img/default_user.png";
              } else {
                $results_latest[$i]['pfp_path'] = str_replace("../", "", $results_latest[$i]['pfp_path']);
              }
              $i++;
          }
      }
      $stmt_latest->close();
  } else {
      echo "failed query1";
  }

  $con->close();
?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Tol Project</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="all,follow">
    <!-- Bootstrap CSS-->
    <link rel="stylesheet" href="vendor/bootstrap/css/bootstrap.min.css">
    <!-- Font Awesome CSS-->
    <link rel="stylesheet" href="vendor/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css">
    <!-- Custom icon font-->
    <link rel="stylesheet" href="css/fontastic.css">
    <!-- Google fonts - Open Sans-->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,700">
    <!-- Fancybox-->
    <link rel="stylesheet" href="vendor/@fancyapps/fancybox/jquery.fancybox.min.css">
    <!-- theme stylesheet-->
    <link rel="stylesheet" href="css/style.default.css" id="theme-stylesheet">
    <!-- Custom stylesheet - for your changes-->
    <link rel="stylesheet" href="css/custom.css">
    <!-- Favicon-->
    <link rel="shortcut icon" href="../favicon.png">

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
            <!-- Navbar Brand --><a href="index.php" class="navbar-brand">PROJECT TOL</a>
            <!-- Toggle Button-->
            <button type="button" data-toggle="collapse" data-target="#navbarcollapse" aria-controls="navbarcollapse" aria-expanded="false" aria-label="Toggle navigation" class="navbar-toggler"><span></span><span></span><span></span></button>
          </div>
          <!-- Navbar Menu -->
          <div id="navbarcollapse" class="collapse navbar-collapse">
            <ul class="navbar-nav ml-auto">
              <li class="nav-item"><a href="index.php" class="nav-link active">Home</a></li>
              <li class="nav-item"><a href="php/blog.php" class="nav-link ">Blog</a></li>
              <!-- <li class="nav-item"><a href="php/post.php" class="nav-link ">Post</a></li> -->
              <?php if (isset($_SESSION['loggedin']) && isset($_SESSION['name'])): ?>
                <li class="nav-item"><a href="php/yourPosts.php" class="nav-link">Your Posts</a></li>
                <li class="nav-item"><a href="php/newPost.php" class="nav-link "><i class="far fa-plus-square"></i> New Post</a></li>
                <li class="nav-item"><a href="php/profile.php" class="nav-link">
                  <div class="avatar" style="display: inline-block; width:25px; height: 25px;"><img src="<?php echo $pfp_path; ?>" alt="..." class="avatar rounded-circle img-fluid"></div>&nbsp;
                  <?php echo substr($_SESSION['name'], 0, 15); ?></a>
                </li>
                <li class="nav-item"><a href="php/logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
              <?php elseif(!isset($_SESSION['loggedin']) || !isset($_SESSION['name'])): ?>
                <li class="nav-item"><a href="php/login.php" class="nav-link ">Login</a></li>
              <?php endif; ?>
            </ul>
            <div class="navbar-text"><a href="php/#" class="search-btn"><i class="icon-search-1"></i></a></div>
            <!-- <ul class="langs navbar-text"><a href="#" class="active">EN</a><span>           </span><a href="#">ES</a></ul> -->
          </div>
        </div>
      </nav>
    </header>

    <!-- Hero Section-->
    <section style="background: url(img/mountains.jpg); background-size: cover; background-position: center center" class="hero">
      <div class="container">
        <div class="row">
          <div class="col-lg-7">
            <h1>PROJECT TOL</h1>
            <a href=".intro" class="hero-link link-scroll">Discover More &nbsp;<i class="fas fa-arrow-down"></i></a>
          </div>
        </div>
        <!-- <a href=".intro" class="continue link-scroll"><i class="fas fa-arrow-down"></i> Scroll Down</a> -->
      </div>
    </section>

    <!-- Intro Section-->
    <section class="intro">
      <div class="container">
        <div class="row">
          <div class="col-lg-8">
            <h2 class="h3">PROJECT TOL (Tree of Life) -  A Place to Share and for Knowledge to Grow</h2>
            <p class="text-big">This blog was developed primarily for my younger sister for the purpose of allowing her to write daily journals. The blog is developed 
              using PHP 7, MySQL, HTML 5, and CSS 3 (Bootstrap). 
              <br/><br/>
              Currently, the blog enables users to write public/private postings (w/ images attached) as well as 
              <strong>log-in authentication, account creation, password reset via an emailed link following SMTP</strong> and many other cool features!
              <br/>
            </p>
          </div>
        </div>
      </div>
    </section>

    <section class="featured-posts no-padding-top">
      <div class="container">
        <header> 
          <h2>Latest from the blog</h2>
          <p class="text-big">Just the latest of the many posts on this blog</p>
        </header>

        <?php 
            for ($i = 0; $i < sizeof($results_latest); $i++) { ?>
                <!-- Post-->
                <div class="row d-flex align-items-stretch">
                  <?php if ($i % 2 != 0) { ?>
                    <div class="image col-lg-5">
                      <a href="php/post.php?post_id=<?php echo $results_latest[$i]['post_id']; ?>&publicPost=1">
                        <img src="<?php if (!isset($results_latest[$i]['img_path']) || $results_latest[$i]['img_path'] == "" || $results_latest[$i]['img_path'] == NULL) { $results_latest[$i]['img_path'] = "uploads/default.jpeg"; echo $results_latest[$i]['img_path']; } else { echo $results_latest[$i]['img_path']; } ?>" alt="..." class="img-fluid">
                      </a>
                    </div> <?php
                  } ?>
                  <div class="text col-lg-7">
                    <div class="text-inner d-flex align-items-center">
                      <div class="content">
                        <header class="post-header">
                          <!-- <div class="category"><a href="#">Business</a><a href="#">Technology</a></div><a href="post.html"> -->
                            <h2 class="h4">
                              <a href="php/post.php?post_id=<?php echo $results_latest[$i]['post_id']; ?>&publicPost=1">
                                <?php echo $results_latest[$i]['title']; ?>
                              </a>
                            </h2>
                        </header>
                        <p><?php if (strlen($results_latest[$i]['post_body']) < 100) { echo $results_latest[$i]['post_body']; } else { echo substr($results_latest[$i]['post_body'], 0, 100) . "..."; } ?></p>
                        <footer class="post-footer d-flex align-items-center"><a href="#" class="author d-flex align-items-center flex-wrap">
                            <div class="avatar">
                              <img src="<?php echo $results_latest[$i]['pfp_path']; ?>" alt="..." class="img-fluid">
                            </div>
                            <div class="title"><span><?php echo $results_latest[$i]['creator_name']; ?></span></div></a>
                          <div class="date"><i class="icon-clock"></i> <?php echo date_format(date_create($results_latest[$i]['date']), "d-M | Y"); ?></div>
                          <div class="comments"><i class="icon-comment"></i><?php echo $results_latest[$i]['num_comments']; ?></div>
                        </footer>
                      </div>
                    </div>
                  </div>
                  <?php if ($i % 2 == 0) { ?>
                    <div class="image col-lg-5">
                      <a href="php/post.php?post_id=<?php echo $results_latest[$i]['post_id']; ?>&publicPost=1">
                        <img src="<?php if (!isset($results_latest[$i]['img_path']) || $results_latest[$i]['img_path'] == "" || $results_latest[$i]['img_path'] == NULL) { $results_latest[$i]['img_path'] = "uploads/default.jpeg"; echo $results_latest[$i]['img_path']; } else { echo $results_latest[$i]['img_path']; } ?>" alt="..." class="img-fluid">
                      </a>
                    </div> <?php
                  } ?>
                </div> <?php
            }
        ?>
        
      </div>
    </section>

    <!-- Divider Section-->
    <!-- <section style="background: url(img/divider-bg.jpg); background-size: cover; background-position: center bottom" class="divider">
      <div class="container">
        <div class="row">
          <div class="col-md-7">
            <h2>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua</h2><a href="#" class="hero-link">View More</a>
          </div>
        </div>
      </div>
    </section> -->

    <!-- Latest Posts -->
    <!-- <section class="latest-posts"> 
      <div class="container">
        <header> 
          <h2>Latest from the blog</h2>
          <p class="text-big">Lorem ipsum dolor sit amet, consectetur adipisicing elit.</p>
        </header>
        <div class="row">
          <div class="post col-md-4">
            <div class="post-thumbnail"><a href="post.html"><img src="img/blog-1.jpg" alt="..." class="img-fluid"></a></div>
            <div class="post-details">
              <div class="post-meta d-flex justify-content-between">
                <div class="date">20 May | 2016</div>
                <div class="category"><a href="#">Business</a></div>
              </div><a href="post.html">
                <h3 class="h4">Ways to remember your important ideas</h3></a>
              <p class="text-muted">Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore.</p>
            </div>
          </div>
          <div class="post col-md-4">
            <div class="post-thumbnail"><a href="post.html"><img src="img/blog-2.jpg" alt="..." class="img-fluid"></a></div>
            <div class="post-details">
              <div class="post-meta d-flex justify-content-between">
                <div class="date">20 May | 2016</div>
                <div class="category"><a href="#">Technology</a></div>
              </div><a href="post.html">
                <h3 class="h4">Diversity in Engineering: Effect on Questions</h3></a>
              <p class="text-muted">Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore.</p>
            </div>
          </div>
          <div class="post col-md-4">
            <div class="post-thumbnail"><a href="post.html"><img src="img/blog-3.jpg" alt="..." class="img-fluid"></a></div>
            <div class="post-details">
              <div class="post-meta d-flex justify-content-between">
                <div class="date">20 May | 2016</div>
                <div class="category"><a href="#">Financial</a></div>
              </div><a href="post.html">
                <h3 class="h4">Alberto Savoia Can Teach You About Interior</h3></a>
              <p class="text-muted">Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore.</p>
            </div>
          </div>
        </div>
      </div>
    </section> -->

    <!-- Newsletter Section-->
    <!-- <section class="newsletter no-padding-top">    
      <div class="container">
        <div class="row">
          <div class="col-md-6">
            <h2>Subscribe to Newsletter</h2>
            <p class="text-big">Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
          </div>
          <div class="col-md-8">
            <div class="form-holder">
              <form action="#">
                <div class="form-group">
                  <input type="email" name="email" id="email" placeholder="Type your email address">
                  <button type="submit" class="submit">Subscribe</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </section> -->

    <!-- Gallery Section-->
    <!-- <section class="gallery no-padding">    
      <div class="row">
        <div class="mix col-lg-3 col-md-3 col-sm-6">
          <div class="item"><a href="img/gallery-1.jpg" data-fancybox="gallery" class="image"><img src="img/gallery-1.jpg" alt="..." class="img-fluid">
              <div class="overlay d-flex align-items-center justify-content-center"><i class="icon-search"></i></div></a></div>
        </div>
        <div class="mix col-lg-3 col-md-3 col-sm-6">
          <div class="item"><a href="img/gallery-2.jpg" data-fancybox="gallery" class="image"><img src="img/gallery-2.jpg" alt="..." class="img-fluid">
              <div class="overlay d-flex align-items-center justify-content-center"><i class="icon-search"></i></div></a></div>
        </div>
        <div class="mix col-lg-3 col-md-3 col-sm-6">
          <div class="item"><a href="img/gallery-3.jpg" data-fancybox="gallery" class="image"><img src="img/gallery-3.jpg" alt="..." class="img-fluid">
              <div class="overlay d-flex align-items-center justify-content-center"><i class="icon-search"></i></div></a></div>
        </div>
        <div class="mix col-lg-3 col-md-3 col-sm-6">
          <div class="item"><a href="img/gallery-4.jpg" data-fancybox="gallery" class="image"><img src="img/gallery-4.jpg" alt="..." class="img-fluid">
              <div class="overlay d-flex align-items-center justify-content-center"><i class="icon-search"></i></div></a></div>
        </div>
      </div>
    </section> -->

    <!-- Page Footer-->
    <footer class="main-footer">
      <div class="container">
        <div class="row">
          <div class="col-md-4">
              <ul class="list-inline social-buttons">
                <li class="list-inline-item">
                  Developer Contact:
                </li>
                <li class="list-inline-item">
                  <a href="https://github.com/JacksonYuanjx" target="_blank">
                    <i class="fab fa-github"></i>
                  </a>
                </li>
                <li class="list-inline-item">
                  <a href="https://www.linkedin.com/in/jackson-yuan/" target="_blank">
                    <i class="fab fa-linkedin-in"></i>
                  </a>
                </li>
                <li class="list-inline-item">
                  <a href="mailto:jacksonyuanjx@hotmail.com?subject=Contact Request">
                    <i class="fas fa-envelope"></i>
                  </a>
                </li>
              </ul>
          </div>
          <!-- <div class="col-md-4">
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
          </div> -->

        </div>
      </div>
      <div class="copyrights">
        <div class="container">
          <div class="row">
            <div class="col-md-6">
              <p>&copy; 2019. All rights reserved. PROJECT TOL</p>
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
    <script src="vendor/bootstrap/js/bootstrap.min.js"></script>
    <script src="vendor/jquery.cookie/jquery.cookie.js"> </script>
    <script src="vendor/@fancyapps/fancybox/jquery.fancybox.min.js"></script>
    <script src="js/front.js"></script>
    
  </body>
</html>