<?php 
if(isset($_GET['file'])&&$_GET['file']!=""){
	require_once("includes/conexion.php");
	
	$file=base64_decode($_GET['file']);
	if(!isset($_GET['line'])||$_GET['line']==""){
		$line=1;
	}else{
		$line=base64_decode($_GET['line']);
	}	
	$NombreArchivo="";
	$size=0;
	
	//Validar si el anexo es de SAP o interno de PortalOne. 1 > (default) SAP. 2 > PortalOne	
	if(isset($_GET['type'])&&($_GET['type']==2)){
		
		$carp_archivos=ObtenerVariable("RutaArchivos");
		$carp_anexos="formularios";
		$dir_attach=$_SESSION['BD']."/".$carp_archivos."/".$carp_anexos."/";
		
		$SQL=Seleccionar('uvw_tbl_DocumentosSAP_Anexos','NombreArchivo',"ID_Anexo='".$file."'");
		$row=sqlsrv_fetch_array($SQL);

		$filename = $dir_attach.$row['NombreArchivo'];

		$NombreArchivo=$row['NombreArchivo'];
		$size = filesize($filename);
		
	}else{
		$RutaAttachSAP=ObtenerDirAttach();

		$SQL=Seleccionar('uvw_Sap_tbl_DocumentosSAP_Anexos','NombreArchivo',"AbsEntry='".$file."' AND Line='".$line."'");
		$row=sqlsrv_fetch_array($SQL);

		$filename = $RutaAttachSAP[0].$row['NombreArchivo'];

		$NombreArchivo=$row['NombreArchivo'];
		$size = filesize($filename);
	}	
	
	header("Content-Transfer-Encoding: binary"); 
	header('Content-type: application/pdf', true);
	header("Content-Type: application/force-download"); 
	header('Content-Disposition: attachment; filename="'.$NombreArchivo.'"');
	header("Content-Length: $size"); 
	readfile($filename);
	
	//echo $filename;
}




?>