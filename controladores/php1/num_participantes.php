<?php

require_once ("../conexion/conexion.php");


$sql= "SELECT count(*) as num FROM participante where `estado`='Aceptado'";

$result = $db_connection->query($sql);

 $arr = array();
if ($result->num_rows > 0) {
// output data of each row
while($row = mysqli_fetch_assoc($result)) {
		printf ($row['num']); //cargo los valores en un array
		
	}
}

echo $num;
$db_connection->close();

?>