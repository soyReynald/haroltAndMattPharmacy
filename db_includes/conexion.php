<?php
session_start();
define("USER", "root");
define("DB_NAME", "sample_pharmacy");
define("PASSWORD", "");
define("SERVERNAME", "localhost");


$conn = new mysqli(SERVERNAME, USER, PASSWORD, DB_NAME);
// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
} else {
	//echo json_encode("Connected successfully");
}
/*
TO INSERT:


*/

/*
TO UPDATE:

$sql = "UPDATE MyGuests SET lastname='Doe' WHERE id=2";

if ($conn->query($sql) === TRUE) {
  echo "Record updated successfully";
} else {
  echo "Error updating record: " . $conn->error;
}

$conn->close();
*/


?>