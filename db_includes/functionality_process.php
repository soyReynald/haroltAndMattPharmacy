<?php
include_once("conexion.php");

print_r($_POST);

if (isset($_GET) && $_GET['id']) { // Here should be specified the request as well

	$id_to_product = $_GET['id'];
	$sql = "SELECT `id`, `quantity_in_stock` FROM products WHERE id=$id_to_product";
		
		if ($conn->query($sql) === TRUE) {
			while($row = mysql_fetch_array($sql, MYSQL_ASSOC)) {
				$id_to_product = $row['id'];
				$quantity_in_stock = $row['quantity_in_stock'];
			};
		}


	$To_Reduce = (int)$quantity_in_stock - 1;
	$sql = "UPDATE products SET quantity_in_stock = $To_Reduce WHERE id='$id_to_product'";

	if ($conn->query($sql) === TRUE) {
	  echo "Record updated successfully";
	} else {
	  echo "Error updating record: " . $conn->error;
	}
}



$conn->close();

?>