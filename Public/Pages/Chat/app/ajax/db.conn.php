<?php
ob_start();

# server name
$sName = "66.45.252.210";
# user name
$uName = "QC";
# password
$pass = "12345678";
$db_name = "quickchat";

#creating database connection
try {
  $conn = new PDO(
    "mysql:host=$sName;dbname=$db_name",
    $uName,
    $pass
  );
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
  echo "Connection failed : " . $e->getMessage();
}
