<?php
if (!isset($_SESSION)) {
  session_start();
}
include("includes/conect_srv.php");
include("includes/funciones.php");
$Consulta="Select * From uvw_tbl_Archivos";
$SQL=sqlsrv_query($conexion,$Consulta);
$sw=0;
$vOk="";
$cnt=0;
echo "<table border='1'>";
while($row=sqlsrv_fetch_array($SQL)){
	$carp_archivos=ObtenerVariable("RutaArchivos");
	$dir_new=$_SESSION['BD']."/".$carp_archivos."/".$row['CardCode']."/".$row['ID_Categoria']."/".$row['Archivo'];
	if(!file_exists($dir_new)){
		$sw=1;
		echo "<tr>";
		echo "<td>".$row['FechaRegistro']->format('Y-m-d H:i:s')."</td>";
		echo "<td>".$row['NombreUsuario']."</td>";
		echo "<td>"."/".$dir_new."</td>";
		echo "</tr>";
		$cnt++;
	}
}
echo "</table>";
if($sw==0){
	echo "Todos los archivos fueron encontrados!";	
}else{
	echo "<br>";
	echo "Los archivos anteriores no fueron encontrados. Cantidad: ".$cnt;
}
sqlsrv_close($conexion);
?>