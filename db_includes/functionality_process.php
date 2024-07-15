<?php
include_once("conexion.php");
$baseUrl = "http://localhost/haroltAndMatt/";

if (isset($_GET) && $_GET['id_to'] && isset($_GET['update_stock'])) { // Here should be specified the request as well

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



$conn->close();

?>