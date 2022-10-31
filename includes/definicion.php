<?php 
//Datos del portal
if (!isset($_SESSION)) {
	define("NOMBRE_PORTAL", 'PortalCopla');//define("NOMBRE_PORTAL", 'PortalCopla');
	define("NOMBRE_EMPRESA", 'COPLA GROUP S.A.S');//define("NOMBRE_EMPRESA", 'COPLA GROUP S.A.S');
}else{
	$Cons_Datos="EXEC sp_ConsultarDatosPortal";
	$SQL_Datos=sqlsrv_query($conexion,$Cons_Datos);
	$row_Datos=sqlsrv_fetch_array($SQL_Datos);
	define("NOMBRE_PORTAL", $row_Datos['NombrePortal']);
	define("NOMBRE_EMPRESA", $row_Datos['NombreEmpresa']);
	define("NIT_EMPRESA", $row_Datos['NIT']);
	define("SUCURSAL_EMPRESA", $row_Datos['SucursalEmpresa']);
	define("SO",php_uname('s'));
}
define("VERSION", "1.4.5");
define("BDPRO","PortalOneCopla");
define("BDPRUEBAS","");
?>