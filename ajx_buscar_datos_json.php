<?php 
if((isset($_GET['type'])&&($_GET['type']!=""))||(isset($_POST['type'])&&($_POST['type']!=""))){
	require_once("includes/conexion.php");
	header('Content-Type: application/json');
	if(isset($_GET['type'])&&($_GET['type']!="")){
		$type=$_GET['type'];
	}else{
		$type=$_POST['type'];
	}
	   
	if($type==1){//Buscar direccion y barrio dependiendo de la sucursal
		$Consulta="Select * From uvw_Sap_tbl_Clientes_Sucursales Where CodigoCliente='".$_GET['CardCode']."' and NombreSucursal='".$_GET['Sucursal']."'";
		//echo $Consulta;
		$SQL=sqlsrv_query($conexion,$Consulta);
		$records=array();
		$row=sqlsrv_fetch_array($SQL);
		$records=array(
			'Direccion' => $row['Direccion'],
			'Ciudad' => $row['Ciudad'],
			'Barrio' => $row['Barrio'],
			'NombreContacto' => $row['NombreContacto'],
			'TelefonoContacto' => $row['TelefonoContacto'],
			'CargoContacto' => $row['CargoContacto'],
			'CorreoContacto' => $row['CorreoContacto']
		);
		echo json_encode($records);
	}
	if($type==2){//Buscar datos internos cuando la actividad es de tipo Interna
		$Consulta="Select Top 1 * From uvw_Sap_tbl_Clientes_Sucursales Where CodigoCliente='".NIT_EMPRESA."'";
		$SQL=sqlsrv_query($conexion,$Consulta);
		$records=array();
		$row=sqlsrv_fetch_array($SQL);
		$records=array(
			'CodigoCliente' => $row['CodigoCliente'],
			'NombreCliente' => $row['NombreCliente'],
			'NombreSucursal' => $row['NombreSucursal'],
			'Direccion' => $row['Direccion'],
			'Barrio' => $row['Barrio']
		);
		echo json_encode($records);
	}
	if($type==3){//Buscar direccion de facturacion dependiendo del cliente
		$Consulta="Select * From uvw_Sap_tbl_Clientes_Sucursales Where CodigoCliente='".$_GET['CardCode']."' And NombreSucursal='".$_GET['Sucursal']."'";
		//echo $Consulta;
		$SQL=sqlsrv_query($conexion,$Consulta);
		$records=array();
		$row=sqlsrv_fetch_array($SQL);
		$records=array(
			'NombreSucursal' => $row['NombreSucursal'],
			'Direccion' => $row['Direccion'],
			'Barrio' => $row['Barrio'],
			'TipoDireccion' => $row['TipoDireccion']
		);
		echo json_encode($records);
	}
	if($type==4){//Buscar direccion de destino dependiendo del cliente (no usado)
		$Consulta="Select * From uvw_Sap_tbl_Clientes_Sucursales Where CodigoCliente='".$_GET['CardCode']."' and TipoDireccion='S'";
		//echo $Consulta;
		$SQL=sqlsrv_query($conexion,$Consulta);
		$records=array();
		$row=sqlsrv_fetch_array($SQL);
		$records=array(
			'NombreSucursal' => $row['NombreSucursal'],
			'Direccion' => $row['Direccion'],
			'Barrio' => $row['Barrio'],
			'TipoDireccion' => $row['TipoDireccion']
		);
		echo json_encode($records);
	}
	if($type==5){//Buscar Telefono y correo del contacto
		$Consulta="Select * From uvw_Sap_tbl_ClienteContactos Where CodigoContacto='".$_GET['Contacto']."'";
		//echo $Consulta;
		$SQL=sqlsrv_query($conexion,$Consulta);
		$records=array();
		$row=sqlsrv_fetch_array($SQL);
		$records=array(
			'Telefono' => $row['Telefono1'],
			'Correo' => $row['CorreoElectronico']
		);
		echo json_encode($records);
	}
	if($type==6){//Consultar grupo de articulos en la llamada de servicio
		$Consulta="Select * From uvw_Sap_tbl_ArticulosLlamadas Where ItemCode='".$_GET['id']."'";
		//echo $Consulta;
		$SQL=sqlsrv_query($conexion,$Consulta);
		$records=array();
		$row=sqlsrv_fetch_array($SQL);
		$records=array(
			'ItemCode' => $row['ItemCode'],
			'ItmsGrpCod' => $row['ItmsGrpCod'],
			'ItmsGrpNam' => $row['ItmsGrpNam'],
			'NombreSucursal' => $row['NombreSucursal'],
			'Servicios' => $row['Servicios'],
			'Areas' => $row['Areas'],
			'NombreContacto' => $row['NombreContacto'],
			'TelefonoContacto' => $row['TelefonoContacto'],
			'CargoContacto' => $row['CargoContacto'],
			'CorreoContacto' => $row['CorreoContacto']
			//'Posicion' => $row['Posicion'],
			//'DeOLT' => $row['DeOLT']
		);
		echo json_encode($records);
	}
	if($type==7){//Consultar clientes
		$Param=array("'".$_GET['id']."'",$_SESSION['CodUser']);
		$SQL=EjecutarSP('sp_ConsultarClientes',$Param);
		$records=array();
		$j=0;
		while($row=sqlsrv_fetch_array($SQL)){
			$records[$j]=array(
				'CodigoCliente' => $row['CodigoCliente'],
				'NombreCliente' => $row['NombreCliente'],
				'NombreBuscarCliente' => $row['NombreBuscarCliente']
			);
			$j++;
		}		
		echo json_encode($records);
	}
	if($type==8){//Consultar municipios
		$Consulta="Select * From uvw_Sap_tbl_SN_Municipio Where ID_Municipio LIKE '%".$_GET['id']."%' OR DE_Municipio LIKE '%".$_GET['id']."%'";
		//echo $Consulta;
		$SQL=sqlsrv_query($conexion,$Consulta);
		$records=array();
		$j=0;
		while($row=sqlsrv_fetch_array($SQL)){
			$records[$j]=array(
				'Codigo' => $row['ID_Municipio'],
				'Ciudad' => $row['DE_Municipio'],
				'Departamento' => $row['DeDepartamento']
			);
			$j++;
		}		
		echo json_encode($records);
	}
	if($type==9){//Consultar si hay actividades nuevas asignadas
		$Consulta="Select TOP 1 ID_Actividad From uvw_Sap_tbl_Actividades Where ID_EmpleadoActividad='".$_GET['user']."' And FechaCreacion='".date('Y-m-d')."'";
		//echo $Consulta;
		$SQL=sqlsrv_query($conexion,$Consulta);
		$records=array();
		$row=sqlsrv_fetch_array($SQL);
		$records=array(
			'ID' => $row['ID_Actividad']
		);	
		echo json_encode($records);
	}
	if($type==10){//Consultar tipo de gestion cartera (Telefono o Direccion)
		$Consulta="Select TipoDestino From uvw_tbl_Cartera_TipoGestion Where ID_TipoGestion='".$_GET['tge']."'";
		//echo $Consulta;
		$SQL=sqlsrv_query($conexion,$Consulta);
		$records=array();
		$row=sqlsrv_fetch_array($SQL);
		$records=array(
			'TDest' => $row['TipoDestino']
		);	
		echo json_encode($records);
	}
	if($type==11){//Consultar los datos de las facturas vencidas con sus intereses en mora
		$Param=array("'".base64_decode($_GET['CardCode'])."'",$_GET['IntMora'],$_GET['FactNoVenc']);
		$SQL=EjecutarSP('sp_CalcularIntMoraFactVencida',$Param);
		$records=array();
		$SumIntMora=0;
		$SumSaldo=0;
		$SumGastoCob=0;
		$SumCobPre=0;
		while($row=sqlsrv_fetch_array($SQL)){
			$SumIntMora=$SumIntMora+$row['InteresesMora'];
			$SumSaldo=$SumSaldo+$row['SaldoDocumento'];
			$SumGastoCob=$SumGastoCob+$row['GastosCobranza'];
			$SumCobPre=$SumCobPre+$row['CobroPrejuridico'];
		}
		$records=array(
			'TotalSaldo' => $SumSaldo,
			'TotalIntMora' => $SumIntMora,
			'TotalGastosCob' => $SumGastoCob,
			'TotalCobroPre' => $SumCobPre
		);	
		echo json_encode($records);
	}
	if($type==12){//Consultar articulos para grilla
		$Param=array("'".$_GET['data']."'","'".$_GET['whscode']."'","'".$_GET['tipodoc']."'");
		$SQL=EjecutarSP('sp_ConsultarArticulos',$Param);
		$records=array();
		$j=0;
		while($row=sqlsrv_fetch_array($SQL)){
			$records[$j]=array(
				'IdArticulo' => $row['IdArticulo'],
				'DescripcionArticulo' => $row['DescripcionArticulo'],
				'NombreBuscarArticulo' => $row['NombreBuscarArticulo'],
				'UndMedida' => $row['UndMedida'],
				'PrecioSinIVA' => number_format($row['PrecioSinIVA'],2),
				'PrecioConIVA' => number_format($row['PrecioConIVA'],2),
				'CodAlmacen' => $row['CodAlmacen'],
				'Almacen' => $row['Almacen'],
				'StockAlmacen' => number_format($row['StockAlmacen'],2),
				'StockGeneral' => number_format($row['StockGeneral'],2)
			);
			$j++;
		}		
		echo json_encode($records);
	}
	if($type==13){//Consultar URL del tipo de categoria
		$SQL=Seleccionar("uvw_tbl_TipoCategoria","*","ID_TipoCategoria='".$_GET['TipoCat']."'");
		$records=array();
		$row=sqlsrv_fetch_array($SQL);
		$records=array(
			'URL' => $row['URL']
		);	
		echo json_encode($records);
	}
	if($type==14){//Consultar datos del atributo en RADIUS (DIALNET - MySQL)
		require_once("includes/conexion_mysql.php");
		$SQL=Seleccionar("dictionary","RecommendedOP, RecommendedTable, Value","Attribute='".$_GET['NomAtt']."'",'','',3);
		$records=array();
		$row=mysqli_fetch_array($SQL);
		$records=array(
			'RecommendedOP' => $row['RecommendedOP'],
			'RecommendedTable' => $row['RecommendedTable'],
			'Value' => $row['Value']
		);
		mysqli_close($conexion_mysql);
		echo json_encode($records);
	}
	if($type==15){//Consultar fecha de actualizacion de documentos de SAP B1
		$Param=array("'".base64_decode($_GET['docentry'])."'","'".$_GET['objtype']."'","'".$_GET['date']."'");
		$SQL=EjecutarSP('sp_ConsultarFechaActDocSAP',$Param);
		$records=array();
		$row=sqlsrv_fetch_array($SQL);
		$records=array(
			'Result' => $row['Result']
		);	
		echo json_encode($records);
	}
	if($type==16){//Consultar el comentario sugerido en la cartera de gestion
		$SQL=Seleccionar("uvw_tbl_Cartera_ResultadoGestion","ID_ResultadoGestion, ComentariosSugeridos","ID_ResultadoGestion='".$_GET['Res']."'");
		$records=array();
		$row=sqlsrv_fetch_array($SQL);
		$records=array(
			'Comentarios' => $row['ComentariosSugeridos']
		);
		echo json_encode($records);
	}
	if($type==17){//Consultar si todos los items con lote ya fueron seleccionados
		$Param=array("'".$_GET['cardcode']."'","'".$_GET['whscode']."'","'".$_GET['objtype']."'","'".$_SESSION['CodUser']."'");
		$SQL=EjecutarSP('sp_ConsultarLotesVerificarSeleccion',$Param);
		$Valid=1;
		$records=array();
		while($row=sqlsrv_fetch_array($SQL)){
			if($row['CantSolicitada']!=$row['CantTotalSalida']){
				$Valid=2;
			}				
		}	
		$records=array(
			'Result' => $Valid
		);	
		echo json_encode($records);
	}
	if($type==18){//Consultar el LicTradNum de un cliente
		$SQL=Seleccionar("uvw_Sap_tbl_Clientes","LicTradNum","CodigoCliente='".$_GET['CardCode']."'");
		$records=array();
		$row=sqlsrv_fetch_array($SQL);
		$records=array(
			'LicTradNum' => $row['LicTradNum']
		);
		echo json_encode($records);
	}
	if($type==19){//Consultar si todos los items con seriales ya fueron seleccionados
		$Param=array("'".$_GET['cardcode']."'","'".$_GET['whscode']."'","'".$_GET['objtype']."'","'".$_SESSION['CodUser']."'");
		$SQL=EjecutarSP('sp_ConsultarSerialesVerificarSeleccion',$Param);
		$Valid=1;
		$records=array();
		while($row=sqlsrv_fetch_array($SQL)){
			if($row['CantSolicitada']!=$row['CantTotalSalida']){
				$Valid=2;
			}
		}
		$records=array(
			'Result' => $Valid
		);
		echo json_encode($records);
	}
	if($type==20){//Buscar seriales para seleccionarlos
		$Parametros=array(
			"'".$_GET['id']."'",
			"'".$_GET['whscode']."'",
			"'".$_GET['tipotrans']."'",
			"'".$_GET['cardcode']."'",
			"'".strtoupper($_GET['buscar'])."'"
		);
		$SQL=EjecutarSP('sp_ConsultarInventarioSeriales',$Parametros,0,2);
		$row=sql_fetch_array($SQL,2);
		
		$records=array();
		$records=array(
			'IdSerial' => $row['IdSerial']
		);
		echo json_encode($records);
	}
	if($type==21){//Buscar dosificacion de articulo en detalle de documentos (COPLA)
		$SQL=Seleccionar("uvw_Sap_tbl_Dosificaciones","Dosificacion","ItemCode='".$_GET['itemcode']."' and MetodoAplicacion='".$_GET['metodo']."'");
		$row=sql_fetch_array($SQL);		
		if($row['Dosificacion']!=""){
			$Value=$row['Dosificacion'];
		}else{
			$Value=0;
		}
		$records=array();
		$records=array(
			'Dosificacion' => $Value
		);
		echo json_encode($records);
	}
	if($type==22){//Comprobar si existe un correo electronico
		$records=array();
		if(ValidarEmail(base64_decode($_GET['email']))){
			$records=array(
				'Result' => 1
			);
		}else{
			$records=array(
				'Result' => 0
			);
		}		
		echo json_encode($records);
	}
	if($type==23){//Enviar email por SQL
		$records=array();
		$Resultado=EnviarCorreo($_GET['id'],$_GET['objtype'],$_GET['plantilla']);
		$records=array(
			'Estado' => $Resultado,
			'Mensaje' => ($Resultado) ? "Correo enviado exitosamente" : "No se pudo enviar el correo",
			'Title' => ($Resultado) ? "¡Listo!" : "¡Advertencia!",
			'Icon' => ($Resultado) ? "success" : "error",
		);
		echo json_encode($records);
	}
	if($type==24){//Consultar lista total de articulos para el cambio de producto
		$Param=array("'".$_GET['id']."'");
		$SQL=EjecutarSP('sp_ConsultarArticulosTodos',$Param);
		$records=array();
		$j=0;
		while($row=sqlsrv_fetch_array($SQL)){
			$records[$j]=array(
				'IdArticulo' => $row['IdArticulo'],
				'DescripcionArticulo' => $row['DescripcionArticulo'],
				'NombreBuscarArticulo' => $row['NombreBuscarArticulo']
			);
			$j++;
		}		
		echo json_encode($records);
	}
	if($type==25){//Consultar datos del usuario de SAP
		$SQL=Seleccionar("uvw_Sap_tbl_Empleados","*","ID_Empleado='".$_GET['id']."'");
		$records=array();
		$row=sqlsrv_fetch_array($SQL);
		$records=array(
			'Cedula' => $row['Cedula'],
			'Telefono' => $row['Celular'],
			'Email' => $row['CorreoElectronico']
		);	
		echo json_encode($records);
	}
	if($type==26){//Obtener nuevo ID de la secuencia de la actividad para el calendario ruta
		$SQL=EjecutarSP('sp_ObtenerIDActividad');
		$records=array();
		$row=sqlsrv_fetch_array($SQL);
		$records=array(
			'NewID' => $row['NewIDActividad']
		);	
		echo json_encode($records);
	}
	if($type==27){//Consultar stock en la entrega o salida de documentos de marketing
		$Param=array("'".$_GET['cardcode']."'","'".$_GET['whscode']."'","'".$_GET['objtype']."'","'".$_SESSION['CodUser']."'");
		$SQL=EjecutarSP('sp_ConsultarStockDocMarketing',$Param);
		$row=sqlsrv_fetch_array($SQL);
		if($row['CantError']==1){
			$records=array(
				'Estado' => 0,
				'Mensaje' => $row['MsjError'],
				'Title' => "¡Advertencia!",
				'Icon' => "error",
			);
		}else{
			$records=array(
				'Estado' => 1
			);
		}
		echo json_encode($records);
	}
	if($type==28){//Consultar si ya existen OT creadas en la creacion de OT en lote
		if($_GET['doc']==1){//Llamada de servicio
			$SQL=Seleccionar("tbl_CreacionProgramaOrdenesServicio","*","Usuario='".strtolower($_SESSION['User'])."' and ID_LLamadaServicio <> 0");
		}else{//Orden de venta
			$SQL=Seleccionar("tbl_CreacionProgramaOrdenesServicio","*","Usuario='".strtolower($_SESSION['User'])."' and ID_OrdenVenta <> 0");
		}
		
		$records=array();
		$row=sqlsrv_fetch_array($SQL);
		if($row['ID']!=""){
			$Result=1;
		}else{
			$Result=0;
		}
		$records=array(
			'Result' => $Result,
			'Msg' => ($_GET['doc']==1) ? 'Ya hay OT creadas, ¿Desea crearlas de nuevo?' : 'Ya hay Ordenes de venta creadas, ¿Desea crearlas de nuevo?'
		);	
		echo json_encode($records);
	}
	sqlsrv_close($conexion);
}
?>