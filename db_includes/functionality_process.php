<?php
include_once("conexion.php");

$baseUrl = "http://localhost/haroltAndMatt/";

if (isset($_GET) && @$_GET['id_to'] && isset($_GET['update_stock'])) { // Here should be specified the request as well

	$id_to_product = $_GET['id_to'];
	$sql = "SELECT id, quantity_in_stock FROM products WHERE id='$id_to_product'";
		
		if ($conn->query($sql)) {
			$result = $conn->query($sql);
			$row = mysqli_fetch_array($result, MYSQLI_ASSOC);
			$id_to_product = $row['id'];
			$quantity_in_stock = $row['quantity_in_stock'];
		
			$sql = "UPDATE products SET `quantity_in_stock` = quantity_in_stock-1 WHERE id='$id_to_product'";
			if ($conn->query($sql) === TRUE) {
				echo "Record updated successfully";
				$sql = "UPDATE products SET `quantity_pending` = quantity_pending + 1 WHERE id = '$id_to_product'";
				
				if ($conn->query($sql) === TRUE){
					header("Location: {$baseUrl}");
				}
			} else {
			  echo "Error updating record: " . $conn->error;
			}
	
		}

}

$data = $_POST;
if (isset($data['submit']) && $data['submit'] == "Register"){
	$name = $data['name'];
	
	$email = $data['email'];
	$password = $data['password'];
	$password_confirmation = $data['password_confirmation'];

	if ($password === $password_confirmation){
		// We can continue with the registration
		$sql = "INSERT INTO users (`name`, `email`, `password`)
		VALUES ('{$name}', '{$email}', PASSWORD('{$password}'))";

		if ($conn->query($sql) === TRUE) {
		  echo "New record created successfully";
		  $_SESSION['user_signed_in'] = 1;
		  if($_SESSION['user_signed_in']) {
			header("Location: {$baseUrl}?user=success_to_create");
		  }
		} else {
		  echo "Error: " . $sql . "<br>" . $conn->error;
		} 
	} else {
		// The passwords doesn't match
		header("Location: {$baseUrl}?pass=wrong");
	}		
}


if(isset($data) && $data['login'] == "Sign in"){	
	$password = mysqli_real_escape_string($conn, $data['password']);
	$email = mysqli_real_escape_string($conn, $data['email']);
	
	$sql ="SELECT * FROM users WHERE email = '{$email}'";

	$result= $conn->query($sql);

	if ($result->num_rows){
		 $_SESSION['user_signed_in'] = 1;
		if($_SESSION['user_signed_in'] === 1) {
			header("Location: {$baseUrl}");	 
		}
	} else {
		return mysqli_error();
	}
}

if(isset($_GET) && $_GET['logout'] == 1){
	session_destroy();
	header("Location: {$baseUrl}");
}

$conn->close();

?>