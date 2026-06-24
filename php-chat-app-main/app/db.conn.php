<?php 

# server name
$sName = "177.53.140.149";
# user name
$uName = "paxsaoju1_user";
# password
$pass = ")qQ~eKZ@fF19";

# database name
$db_name = "paxsaoju1_banco";

#creating database connection
try {
    $conn = new PDO("mysql:host=$sName;dbname=$db_name", 
                    $uName, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}catch(PDOException $e){
  echo "Connection failed : ". $e->getMessage();
}