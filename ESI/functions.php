<?php

/////////////////////////////////////////////
//           Get & Post Statements         //
/////////////////////////////////////////////

//Post Request (Working)
function postData($Post_URL,$Headers,$Form_Values){

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $Post_URL);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $Headers);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS,$Form_Values);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

  $Json_Output = curl_exec($ch);

  if ($Json_Output === FALSE){
    echo "cURL Error: " . curl_error($ch);
  };

  curl_close($ch);
  $Decoded_Json = json_decode($Json_Output,True);
  return $Decoded_Json;

}

// Get Request (Working)
function getData($Get_URL,$Headers){

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $Get_URL);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $Headers);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

  $Json_Output = curl_exec($ch);

  if ($Json_Output === FALSE){
    echo "cURL Error: " . curl_error($ch);
  };

  curl_close($ch);
  $Decoded_Json = json_decode($Json_Output,True);
  return $Decoded_Json;
}

/////////////////////////////////////////////
//            Authentication Flow          //
/////////////////////////////////////////////

//Initial Authentication Redirect (Working)
function getCode($State,$Client_ID){

  $Redirect_URI = urlencode("http://127.0.0.1/ESI/index.php");
  $Scopes = urldecode("esi-wallet.read_character_wallet.v1 esi-contracts.read_character_contracts.v1");
  $SSO_URL = "https://login.eveonline.com/v2/oauth/authorize/?response_type=code";
  $Auth_URL = $SSO_URL."&redirect_uri=".$Redirect_URI."&client_id=".$Client_ID."&scope=".$Scopes."&State=".$State;
  header("Location: $Auth_URL");

}

// Get refresh and Auth tokens  (Working)
function getToken(){

  global $Client_ID, $Secret_Key;
  $Code = $_GET["code"];
  $Post_URL = "https://login.eveonline.com/v2/oauth/token";
  $Auth_Header = "Authorization: Basic ".base64_encode("$Client_ID:$Secret_Key");
  $Form_Values = "grant_type=authorization_code&code=$Code&state=$State";

  $Headers = array();
  $Headers [] = "$Auth_Header";
  $Headers [] = "Content-Type: application/x-www-form-urlencoded";
  $Headers [] = "Host: login.eveonline.com";

  return postData($Post_URL,$Headers,$Form_Values);
}

//Verify Token and get Character details
function verifyToken($Access_Token){

  $Get_URL = "https://login.eveonline.com/oauth/verify";
  $Auth_Header = "Authorization: Bearer ".$Access_Token;
  $Headers = array();
  $Headers [] = $Auth_Header;
  $Json = getData($Get_URL,$Headers);
  $_SESSION['Character_ID']=$Json['CharacterID'];

  return $Json;

}

/////////////////////////////////////////////
//              SQL Management             //
/////////////////////////////////////////////

//Run SQL Query
function runSQL($SQL){

  require 'config.php';

  $SQL_Connection = mysqli_connect($SQL_Host, $SQL_Username, $SQL_Password, $SQL_DB, $SQL_Port);
  if (!$SQL_Connection) {
      die("Connection failed: " . mysqli_connect_error());
  }
  else {
    echo "<br> Connection Successfull <br>";
  }
  if (mysqli_query($SQL_Connection, $SQL)) {
    echo "New record created successfully";
  }
  else {
    echo "Error: " . $SQL . "<br>" . mysqli_error($SQL_Connection);
  }
}

//Build SQL Insert Statement
function buildInsertSQL(){

  $Token_Data = getToken();
  $Verify_Data = verifyToken($Token_Data['access_token']);
  $SQL_Array = array();
  $SQL_Array ['Character_ID'] = "'".$Verify_Data['CharacterID']."'";
  $SQL_Array ['Character_Name'] = "'".$Verify_Data['CharacterName']."'";
  $SQL_Array ['Access_Token'] = "'".$Token_Data['access_token']."'";
  $SQL_Array ['Refresh_Token'] = "'".$Token_Data['refresh_token']."'";
  $SQL_Array ['Expire_Time'] = "'".$Verify_Data['ExpiresOn']."'";

  $SQL_Columns = implode(", ",array_keys($SQL_Array));
  $SQL_Values = implode(", ",array_values($SQL_Array));
  $SQL = "INSERT INTO authenticated_characters ($SQL_Columns) VALUES ($SQL_Values) ON DUPLICATE KEY UPDATE";

  runSQL($SQL);

}

//Build SQL Update Statement
function buildUpdateSQL(){

//UPDATE `authenticated_characters` SET `Access_Token`=[value-3],`Refresh_Token`=[value-4],`Expire_Time`=[value-5] WHERE `Character_ID`=[value-1]
}

//Build SQL Query
function querySQL(){

}

/////////////////////////////////////////////
//          Authenticated Requests         //
/////////////////////////////////////////////

// Wallet Balance
function getBalance($Access_Token, $Character_ID){
  $Get_URL = "https://esi.evetech.net/latest/characters/$Character_ID/wallet";
  $Auth_Header = "Authorization: Bearer ".$Access_Token;
  $Headers = array();
  $Headers [] = $Auth_Header;
  getData($Get_URL,$Headers);
}



?>
