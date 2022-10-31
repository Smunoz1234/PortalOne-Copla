<?php 
if((isset($_GET['type'])&&($_GET['type']!=""))||(isset($_POST['type'])&&($_POST['type']!=""))){
	require_once("includes/conexion.php");
	header('Content-Type: application/json');
	if(isset($_GET['type'])&&($_GET['type']!="")){
		$type=$_GET['type'];
	}else{
		$type=$_POST['type'];
	}
	if($type==1){//Regenerar procesos de creacion de clientes
		$records=array();
		$Parametros=array(
			'pID' => $_GET['id'],
			'pMetodo' => $_GET['metodo'],
			'pLogin'=> $_SESSION['User']
		);
		$Resultado=EnviarWebServiceSAP('AppPortal_RegenerarInsertarClientePortal_JSON',$Parametros,true);
		$records=array(
			'Estado' => $Resultado->Success,
			'Mensaje' => $Resultado->Mensaje,
			'Objeto' => $Resultado->Objeto,
			'Title' => ($Resultado->Success==1) ? "¡Listo!" : "¡Advertencia!",
			'Icon' => ($Resultado->Success==1) ? "success" : "error",
		);
		echo json_encode($records);
	}
	if($type==2){//Ejecutar WebServices en JSON para cierre de OT
		$records=array();
		$Parametros=array(
			'pIdEvento' => $_GET['Evento'],
			'pFechaInicial' => $_GET['FechaInicial'],
			'pFechaFinal'=> $_GET['FechaFinal'],
			'pSucursal' => $_GET['Sucursal'],
			'pLogin' => $_SESSION['User'],
			'pIdSerieOT' => $_GET['Serie']
		);
		if($_GET['Tipo']=='1'){
			$Param=array(
				"'".strtolower($_SESSION['User'])."'");
			$SQL=EjecutarSP('sp_tbl_CierreOTDetalleCarritoValidar',$Param);
			$row=sqlsrv_fetch_array($SQL);
			if($row['CantError']==0){
				$Metodo="AppPortal_CrearCierreActividadesOrdenServicio";
			}else{
				$records=array(
					'Estado' => 0,
					'Mensaje' => $row['MsjError'],
					'Title' => "¡Advertencia!",
					'Icon' => "error",
				);
				echo json_encode($records);
				exit();
			}			
		}else{
			$RutaAttachSAP=ObtenerDirAttach();
			$Param=array(
				"'".ObtenerVariable("RutaAnexosOT").strtolower($_SESSION['User'])."\'",
				"'".$RutaAttachSAP[0]."'");
			$SQL=EjecutarSP('usp_CopiarArchivosToSAP',$Param);
			$row=sqlsrv_fetch_array($SQL);
			if($row['CantError']==0){
				$Metodo="AppPortal_CrearCierreOrdenServicio";
			}else{
				$records=array(
					'Estado' => 0,
					'Mensaje' => "No se pudieron copiar los anexos a SAP",
					'Title' => "¡Advertencia!",
					'Icon' => "error",
				);
				echo json_encode($records);
				exit();
			}			
		}
		$Resultado=EnviarWebServiceSAP($Metodo,$Parametros,true);
		$records=array(
			'Estado' => $Resultado->Success,
			'Mensaje' => $Resultado->Mensaje,
			'Title' => ($Resultado->Success==1) ? "¡Listo!" : "¡Advertencia!",
			'Icon' => ($Resultado->Success==1) ? "success" : "error",
		);
		echo json_encode($records);
	}
	if($type==3){//Ejecutar WebServices en JSON para creacion de OT
		$records=array();
		$Parametros=array(
			'pIdEvento' => $_GET['Evento'],
			'pPeriodo' => $_GET['Anno'],
			'pFechaInicial' => $_GET['FechaInicial'],
			'pFechaFinal'=> $_GET['FechaFinal'],
			'pSucursal' => $_GET['Sucursal'],
			'pIdCliente' => $_GET['Cliente'],
			'pLogin' => $_SESSION['User'],
			'pIdSerieOT' => $_GET['SeriesOT'],
			'pIdSerieOV' => $_GET['SeriesOV']
		);
		if($_GET['Tipo']=='1'){
			$Metodo="AppPortal_CrearProgramaOrdenServicio";		
		}else{
			$Metodo="AppPortal_CrearProgramaOrdenesVentas";			
		}
		$Resultado=EnviarWebServiceSAP($Metodo,$Parametros,true);
		$records=array(
			'Estado' => $Resultado->Success,
			'Mensaje' => $Resultado->Mensaje,
			'Title' => ($Resultado->Success==1) ? "¡Listo!" : "¡Advertencia!",
			'Icon' => ($Resultado->Success==1) ? "success" : "error",
		);
		echo json_encode($records);
	}
	if($type==4){//Ejecutar WebServices en JSON para cambio de producto
		$records=array();
		$Parametros=array(
			'pIdEvento' => $_GET['Evento'],
			'pFechaInicial' => $_GET['FechaInicial'],
			'pFechaFinal'=> $_GET['FechaFinal'],
			'pSucursal' => $_GET['Sucursal'],
			'pLogin' => $_SESSION['CodUser'],
			'pIdSerieOT' => $_GET['SeriesOT']
		);
		$Metodo="AppPortal_CrearCambioProductoOrdenVenta";
		$Resultado=EnviarWebServiceSAP($Metodo,$Parametros,true);
		$records=array(
			'Estado' => $Resultado->Success,
			'Mensaje' => $Resultado->Mensaje,
			'Title' => ($Resultado->Success==1) ? "¡Listo!" : "¡Advertencia!",
			'Icon' => ($Resultado->Success==1) ? "success" : "error",
		);
		echo json_encode($records);
	}
	if($type==5){//Ejecutar WebServices en JSON para integracion de actividades en rutero
		$records=array();
		$Parametros=array(
			'pLogin' => $_SESSION['User'],
			'pIdUsuario' => $_SESSION['CodUser'],
			'pIdEvento'=> $_GET['IdEvento']
		);
		$Metodo="AppPortal_InsertarActividadesRutas";
		$Resultado=EnviarWebServiceSAP($Metodo,$Parametros,true);
		$records=array(
			'Estado' => $Resultado->Success,
			'Mensaje' => $Resultado->Mensaje,
			'Title' => ($Resultado->Success==1) ? "¡Listo!" : "¡Advertencia!",
			'Icon' => ($Resultado->Success==1) ? "success" : "error",
		);
		echo json_encode($records);
	}
	sqlsrv_close($conexion);
}
?>