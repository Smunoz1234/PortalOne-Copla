<?php
require_once("includes/conexion.php");

$SQL=Seleccionar("tbl_Licencia","*");
$row=sqlsrv_fetch_array($SQL);

echo $row['Licencia'];
//var_dump(is_null($row));

?>
