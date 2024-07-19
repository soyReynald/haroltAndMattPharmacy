<?php
include_once("conexion.php");

$baseUrl = "http://localhost/haroltAndMatt/";

if (isset($_GET) && @$_GET['id_to'] && isset($_GET['update_stock'])) { // Here should be specified the request as well
	$email_in_session = $_SESSION['user_email'];
	$sql_to_evaluate_user = "SELECT credit_available FROM users WHERE email = '{$email_in_session}'"; 
	
	$result= $conn->query($sql_to_evaluate_user);
	$row = mysqli_fetch_array($result, MYSQLI_ASSOC);
	
	$credit_of_user = $row['credit_available'];
	
	$id_to_product = $_GET['id_to'];
	$sql = "SELECT id, product_name, quantity_in_stock, product_url, price FROM products WHERE id='$id_to_product'";// 1 medida

	if ($conn->query($sql)) {
		$result = $conn->query($sql);
		$row = mysqli_fetch_array($result, MYSQLI_ASSOC);
		$id_to_product = $row['id'];
		$product_name = $row['product_name'];
		$quantity_in_stock = $row['quantity_in_stock'];
		$price_of_product = $row['price'];
		$product_image_url = $row['product_url'];
		
		
		if ($credit_of_user > $price_of_product) {
		
			$sql = "UPDATE products SET `quantity_in_stock` = quantity_in_stock-1 WHERE id='$id_to_product'"; // 2 medidas
			if ($conn->query($sql) === TRUE) {
				$sql = "UPDATE products SET `quantity_pending` = quantity_pending + 1 WHERE id = '$id_to_product'"; // 3ra medida
				
				if ($conn->query($sql) === TRUE) { // The oat
					
					$sql_to_pending_to_buy = "SELECT quantity_pending FROM products WHERE id = '$id_to_product'";
					if ($conn->query($sql_to_pending_to_buy)) {
						$result = $conn->query($sql_to_pending_to_buy);
						$row = mysqli_fetch_array($result, MYSQLI_ASSOC);
						$chose_by_user = $row['quantity_pending'];
						echo "Everything done, if there was not error, please CHECK in the database";
	
						echo $product_name;
						echo $chose_by_user;
						echo $product_image_url;
						$sql = "INSERT INTO products_in_cart (`product_name`, `quantity_chose`, `product_image_url`) VALUES ('{$product_name}', '{$chose_by_user}', '{$product_image_url}')";
						if ($conn->query($sql) === TRUE) {
							echo "New record created successfully";
							//header("Location: {$baseUrl}");
						}
					} else {
					  echo "Error updating record: " . $conn->error;
					}
				}
			}
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
		  $_SESSION['user_email'] = $email;
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
	$row = mysqli_fetch_array($result, MYSQLI_ASSOC);

	if ($result->num_rows){	
		 $_SESSION['user_signed_in'] = 1;
		 $_SESSION['user_email'] = $row['email'];
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