<?php
session_start();
require 'config.php';
require 'functions.php';

if (isset($_GET['code'])){

/*

Step 1. Check if there is already an active session
  a. If session is active, check that session is valid
     i. if session is valid move to Step 2.
    ii. if session is not valid, then create a new session and re-authenticate.
  b. If no session is present then create a new session and authenticate the user.

Step 2. Check if the session has a CharacterID
  a. If session has a Charachter ID then lookup details in the Database
     i. if no details are found, then re-authenticate user and add details to the database
    ii. if details are found then do what we need to do...
  b. If the session does not have a CharacterID then re-authenticate the user.



*/

buildSQL();

//

}
else {

  $_SESSION['state'] = uniqid();
  $State = $_SESSION['state'];
  getCode($State,$Client_ID);

}

?>
