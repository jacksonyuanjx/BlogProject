<?php
    session_start();  // to ensure you are using the same session
    session_destroy();
    header('Location: ../index.php');
?>