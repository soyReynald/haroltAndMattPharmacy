<?php
include_once("conexion.php");

$baseUrl = "http://localhost/haroltAndMatt/";

if (isset($_GET) && @$_GET['id_to'] && isset($_GET['update_stock']) && @$_GET['update_stock'] == '1') { // Here should be specified the request as well
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
		

		if ($credit_of_user >= $price_of_product) {
		
			$sql = "UPDATE products SET `quantity_in_stock` = quantity_in_stock-1 WHERE id='$id_to_product'"; // 2 medidas
			if ($conn->query($sql) === TRUE) {
				
				$sql = "UPDATE products SET `quantity_pending` = quantity_pending + 1 WHERE id = '$id_to_product'"; // 3ra medida
				
				if ($conn->query($sql) === TRUE) { // The oat
					// First I should check if the product has not being chosen by the user
						
					// If so, then I should update instead of insert
					update_or_insert_into_other_TABLE($conn, $id_to_product);
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
			$sqlToGetUser = "SELECT id FROM users where email = '{$email}'";
			$result= $conn->query($sqlToGetUser);
			$row = mysqli_fetch_array($result, MYSQLI_ASSOC);
			
			if ($result->num_rows) {
				$_SESSION['user_email'] = $row['id'];
				header("Location: {$baseUrl}?user=success_to_create");
			}
			
		  }
		} else {
		  echo "Error: " . $sql . "<br>" . $conn->error;
		} 
	} else {
		// The passwords doesn't match
		header("Location: {$baseUrl}?pass=wrong");
	}		
}


if(isset($data) && @$data['login'] == "Sign in"){	
	$password = mysqli_real_escape_string($conn, $data['password']);
	$email = mysqli_real_escape_string($conn, $data['email']);
	
	$sql ="SELECT * FROM users WHERE email = '{$email}'";

	$result= $conn->query($sql);
	$row = mysqli_fetch_array($result, MYSQLI_ASSOC);

	if ($result->num_rows){	
		 $_SESSION['user_signed_in'] = 1;
		 $_SESSION['user_email'] = $row['email'];
		 $_SESSION['user_id'] = $row['id'];
		 
		if($_SESSION['user_signed_in'] === 1) {
			header("Location: {$baseUrl}");	 
		}
	} else {
		return mysqli_error();
	}
}

if (isset($_GET) && @$_GET['id_to'] && isset($_GET['update_stock']) && @$_GET['update_stock'] == 'remove') {
	$id_to_product = $_GET['id_to'];
	$user_id = $_SESSION['user_id'];

	$select_sql = "SELECT * FROM products_in_cart WHERE id_of_product = '$id_to_product' AND quantity_chose = 1";
	
	$result_of_select = $conn->query($select_sql);
	$row = mysqli_fetch_array($result_of_select, MYSQLI_ASSOC);
	if ($result_of_select->num_rows == 1) {
		// To remove if the quantity is equal to 1
		$sql_to_delete_the_last_product = "DELETE FROM products_in_cart WHERE id_of_product";
		if ($conn->query($sql_to_delete_the_last_product) === TRUE) {
			header("Location: {$baseUrl}");
		} else {
		  echo "Error deleting record: " . $conn->error;
		}	
	} else {
		// To update when the product is still in the database
		$sql_to_update_quantity = "UPDATE products_in_cart SET quantity_chose = quantity_chose-1 WHERE user_id_who_chose = '{$user_id}' AND id_of_product = '{$id_to_product}'";

		if ($conn->query($sql_to_update_quantity) === TRUE) {
			echo "Products updated successfully";
			header("Location: {$baseUrl}/product_cart.php");
		} else {
			echo "Error in conexion or updating".$conn->mysqli_error();
			header("Location: {$baseUrl}");
		}
	}
}


if(@$_GET['logout'] == 1){
	session_destroy();
	header("Location: {$baseUrl}");
}




// End of PRE-PROCESS 
// Beggining of FUNCTIONS
// Functions
function update_or_insert_into_other_TABLE($conexion_to_db, $id_of_product) {
	$sql_to_pending_to_buy = "SELECT * FROM products WHERE id = '$id_of_product'";
	
	$result= $conexion_to_db->query($sql_to_pending_to_buy);
	$row = mysqli_fetch_array($result, MYSQLI_ASSOC);
	
	if ($result->num_rows) {
		$user_id = $_SESSION['user_id'];
		$product_in_product = $row['price'];
		$product_id = $row['id'];
		$sql_check_if_product_has_been_updated = "SELECT quantity_chose, user_id_who_chose, id_of_product FROM products_in_cart WHERE user_id_who_chose = '{$user_id}' AND id_of_product = '{$product_id}'";
		// Functionality of adding products by user (1)
		$result_of_buying_cart_query = $conexion_to_db->query($sql_check_if_product_has_been_updated);
		$rows_in_cart = mysqli_fetch_array($result_of_buying_cart_query, MYSQLI_ASSOC);
		
		if ($result_of_buying_cart_query->num_rows != 0) {
			if ($rows_in_cart['quantity_chose'] > 0) {
				// TO update
				update_products_in_cart($conexion_to_db, $user_id, $product_in_product);
			} 
		} else {
				// TO insert 
				// $product_id -- IS UP HERE ABOVE
				$product_name = $row['product_name'];
				$quantity_in_stock = $row['quantity_in_stock'];
				$quantity_pending = $row['quantity_pending'];
				$price = $row['price'];
				$product_url = $row['product_url'];
				insert_products_in_cart($conexion_to_db, $user_id, $product_name, $quantity_pending, $price, $product_url, $product_id); 
		}
	}
}

function update_products_in_cart($conn, $user, $product_price) {
	$baseUrl = "http://localhost/haroltAndMatt/";
	$sql = "UPDATE products_in_cart SET `quantity_chose` = quantity_chose+1, `price` = '{$product_price}' WHERE user_id_who_chose = '{$user}'"; // 2 medidas

	if ($conn->query($sql) === TRUE) {
		echo "Product, updated sucessfully";
		$sqlToCheckQuantity = "";
		header("Location: {$baseUrl}");
	}
}

function insert_products_in_cart($conexion_to_db, $user_id, $_product_name, $_quantity_pending, $price, $product_image, $id_to_product) {
	$baseUrl = "http://localhost/haroltAndMatt/";
	
	$sql = "INSERT INTO products_in_cart (`user_id_who_chose`, `product_name`, `quantity_chose`, `price`, `product_image_url`, `id_of_product`) VALUES ('{$user_id}','{$_product_name}', 1, '{$price}', '{$product_image}', '{$id_to_product}')";
	
	if ($conexion_to_db->query($sql) === TRUE) {
		echo "Product, added sucessfully";
		$sqlToCheckQuantity = "";
		header("Location: {$baseUrl}");
	} else{
		$conexion_to_db->mysqli_error();
	}
	
}

// END of FUNCTIONS
// Jehová le preguntó a Job: ¿Dónde estabas cuando puce límite a las aguas? ...
// Hasta aquí: Att: Adonai.

// Ya es un nuevo día, así que: Buen día Jesús :)
// (He SURELY answered: Buen día Reyo).

// ----
// ----
$conn->close();

?>