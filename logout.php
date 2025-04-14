 <?php
 session_start();
 
 // Add your logout logic here, for example:
 session_destroy();
 header("Location: login.php");
 exit();