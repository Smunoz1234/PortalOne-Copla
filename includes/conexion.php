<?php 
if (!isset($_SESSION)) {
  session_start();
}
if (!isset($_SESSION['User'])||$_SESSION['User']==""||$_SESSION['Perfil']=="") {
	$this_url = base64_encode($_SERVER['REQUEST_URI']);
	
	if(file_exists('logout.php')){
		header('Location:logout.php?return_url='.urlencode($this_url));
		}else{
			header('Location:../logout.php');
		}
	exit();
}

if(file_exists("includes/conect_srv.php")){
	require("includes/conect_srv.php");
}else{
	require("conect_srv.php");
}

//$onload_body="onLoad='Reloj();' onkeyup='ResetC();' onclick='ResetC();' onMouseOver='ResetC();' onMouseOut='ResetC();'";
//Funciones
if(file_exists("includes/funciones.php")){
	include("includes/funciones.php");
	require_once("includes/LSiqml.php");
}else{
	include("funciones.php");
	require_once("LSiqml.php");
}
//Declaraciones
if(file_exists("includes/definicion.php")){
	include("includes/definicion.php");
}else{
	include("definicion.php");
}
?>