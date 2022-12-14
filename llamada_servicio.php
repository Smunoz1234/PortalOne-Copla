<?php require_once("includes/conexion.php");
PermitirAcceso(301);
$IdLlamada="";
$msg_error="";//Mensaje del error
$dt_LS=0;//sw para saber si vienen datos del SN. 0 no vienen. 1 si vienen.
$TituloLlamada="PLAN DE CONTROL DE PLAGAS";//Titulo por defecto cuando se está creando la llamada de servicio

if(isset($_GET['id'])&&($_GET['id']!="")){
	$IdLlamada=base64_decode($_GET['id']);
}

if(isset($_GET['tl'])&&($_GET['tl']!="")){//0 Creando una llamada de servicio. 1 Editando llamada de servicio.
	$type_llmd=$_GET['tl'];
}elseif(isset($_POST['tl'])&&($_POST['tl']!="")){
	$type_llmd=$_POST['tl'];
}else{
	$type_llmd=0;
}

if(isset($_POST['swError'])&&($_POST['swError']!="")){//Para saber si ha ocurrido un error.
	$sw_error=$_POST['swError'];
}else{
	$sw_error=0;
}

if($type_llmd==0){
	$Title="Crear llamada de servicio";
}else{
	$Title="Editar llamada de servicio";
}
	
if(isset($_POST['P'])&&($_POST['P']==32)){
	//Insertar llamada de servicio
	try{
		//*** Carpeta temporal ***
		$i=0;//Archivos
		$RutaAttachSAP=ObtenerDirAttach();
		$dir=CrearObtenerDirTemp();
		$dir_new=CrearObtenerDirAnx("llamadas");		   
		$route= opendir($dir);
		$DocFiles=array();
		while ($archivo = readdir($route)){ //obtenemos un archivo y luego otro sucesivamente
			if(($archivo == ".")||($archivo == "..")) continue;

			if (!is_dir($archivo)){//verificamos si es o no un directorio
				$DocFiles[$i]=$archivo;
				$i++;
				}
		}
		closedir($route);
		$CantFiles=count($DocFiles);
		
		$ParamInsLlamada=array(
			"NULL",
			"NULL",
			"NULL",
			"'".$_POST['TipoTarea']."'",
			"'".$_POST['AsuntoLlamada']."'",
			"'".$_POST['Series']."'",
			"'".$_POST['EstadoLlamada']."'",
			"'".$_POST['OrigenLlamada']."'",
			"'".$_POST['TipoLlamada']."'",
			"'".$_POST['TipoProblema']."'",
			"'".$_POST['SubTipoProblema']."'",
			"'".$_POST['ClienteLlamada']."'",
			"'".$_POST['ContactoCliente']."'",
			"'".$_POST['TelefonoLlamada']."'",
			"'".$_POST['CorreoLlamada']."'",
			"'".$_POST['ArticuloLlamada']."'",
			"'".$_POST['SucursalCliente']."'",
			"'".$_POST['DireccionLlamada']."'",
			"'".$_POST['CiudadLlamada']."'",
			"'".$_POST['BarrioDireccionLlamada']."'",
			"'".$_POST['EmpleadoLlamada']."'",
			"'".LSiqmlObs($_POST['ComentarioLlamada'])."'",
			"'".LSiqmlObs($_POST['ResolucionLlamada'])."'",
			"'".FormatoFecha($_POST['FechaCreacion'])."'",
			"'".FormatoFecha($_POST['FechaCierre'],$_POST['HoraCierre'])."'",
			"1",
			"'".$_SESSION['CodUser']."'",
			"'".$_SESSION['CodUser']."'",			
			"'".$_POST['CDU_EstadoServicio']."'",
			"'".LSiqmlObs($_POST['CDU_Servicios'])."'",
			"'".LSiqmlObs($_POST['CDU_Areas'])."'",
			"'".LSiqmlObs($_POST['CDU_NombreContacto'])."'",
			"'".LSiqmlObs($_POST['CDU_TelefonoContacto'])."'",
			"'".LSiqmlObs($_POST['CDU_CargoContacto'])."'",
			"'".LSiqmlObs($_POST['CDU_CorreoContacto'])."'",
			"'".$_POST['CDU_Reprogramacion1']."'",
			"'".$_POST['CDU_Reprogramacion2']."'",
			"'".$_POST['CDU_Reprogramacion3']."'",
			"'".$_POST['CDU_CausaReprogramacion1']."'",
			"'".$_POST['CDU_CausaReprogramacion2']."'",
			"'".$_POST['CDU_CausaReprogramacion3']."'",
			"'".$_POST['CDU_CanceladoPor']."'",			
			"1"
		);
		$SQL_InsLlamada=EjecutarSP('sp_tbl_LlamadaServicios',$ParamInsLlamada,32);		
		if($SQL_InsLlamada){
			$row_NewIdLlamada=sqlsrv_fetch_array($SQL_InsLlamada);
			$IdLlamada=$row_NewIdLlamada[0];
			
			try{
				//Mover los anexos a la carpeta de archivos de SAP
				$j=0;
				while($j<$CantFiles){
					$Archivo=FormatoNombreAnexo($DocFiles[$j]);
					$NuevoNombre=$Archivo[0];
					$OnlyName=$Archivo[1];
					$Ext=$Archivo[2];
					
					if(file_exists($dir_new)){
						copy($dir.$DocFiles[$j],$dir_new.$NuevoNombre);
						//move_uploaded_file($_FILES['FileArchivo']['tmp_name'],$dir_new.$NuevoNombre);
						copy($dir_new.$NuevoNombre,$RutaAttachSAP[0].$NuevoNombre);

						//Registrar archivo en la BD
						$ParamInsAnex=array(
							"'191'",
							"'".$row_NewIdLlamada[0]."'",
							"'".$OnlyName."'",
							"'".$Ext."'",
							"1",
							"'".$_SESSION['CodUser']."'",
							"1"					
						);
						$SQL_InsAnex=EjecutarSP('sp_tbl_DocumentosSAP_Anexos',$ParamInsAnex,32);
						if(!$SQL_InsAnex){
							$sw_error=1;
							$msg_error="Error al crear la llamada de servicio";
						}
					}
					$j++;
				}
			}catch (Exception $e) {
				echo 'Excepcion capturada: ',  $e->getMessage(), "\n";
			}			
			
			//Enviar datos al WebServices
			try{
				require_once("includes/conect_ws.php");
				$Parametros=array(
					'pIdLlamada' => $row_NewIdLlamada[0],
					'pLogin'=>$_SESSION['User']
				);
				$Client->InsertarLlamadaServicioPortal($Parametros);
				$Respuesta=$Client->__getLastResponse();
				$Contenido=new SimpleXMLElement($Respuesta,0,false,"s",true);
				$espaciosDeNombres = $Contenido->getNamespaces(true);
				$Nodos = $Contenido->children($espaciosDeNombres['s']);
				$Nodo=	$Nodos->children($espaciosDeNombres['']);
				$Nodo2=	$Nodo->children($espaciosDeNombres['']);
				
				$Archivo=json_decode($Nodo2,true);
				if($Archivo['ID_Respuesta']=="0"){
					//InsertarLog(1, 0, 'Error al generar el informe');
					//throw new Exception('Error al generar el informe. Error de WebServices');		
					$sw_error=1;
					$msg_error=$Archivo['DE_Respuesta'];
					if($_POST['EstadoLlamada']=='-1'){
						$UpdEstado="Update tbl_LlamadasServicios Set Cod_Estado='-3' Where ID_LlamadaServicio='".$IdLlamada."'";
						$SQL_UpdEstado=sqlsrv_query($conexion,$UpdEstado);
					}
				}else{
					//Consultar la llamada para recargarla nuevamente y poder mantenerla
					$SQL_Llamada=Seleccionar('uvw_Sap_tbl_LlamadasServicios','[ID_LlamadaServicio]',"[IdLlamadaPortal]='".$IdLlamada."'");
					$row_Llamada=sqlsrv_fetch_array($SQL_Llamada);
					sqlsrv_close($conexion);
					header('Location:llamada_servicio.php?id='.base64_encode($row_Llamada['ID_LlamadaServicio']).'&tl=1&a='.base64_encode("OK_LlamAdd"));	
				}
			}catch (Exception $e) {
				echo 'Excepcion capturada: ',  $e->getMessage(), "\n";
			}		
		}else{
			$sw_error=1;
			$msg_error="Error al crear la llamada de servicio";
		}
	}catch (Exception $e) {
		echo 'Excepcion capturada: ',  $e->getMessage(), "\n";
	}
}

if(isset($_POST['P'])&&($_POST['P']==33)){//Actualizar llamada de servicio
	try{
		///*** Carpeta temporal ***
		$i=0;//Archivos
		$RutaAttachSAP=ObtenerDirAttach();
		$dir=CrearObtenerDirTemp();
		$dir_new=CrearObtenerDirAnx("llamadas");		   
		$route= opendir($dir);
		$DocFiles=array();
		while ($archivo = readdir($route)){ //obtenemos un archivo y luego otro sucesivamente
			if(($archivo == ".")||($archivo == "..")) continue;

			if (!is_dir($archivo)){//verificamos si es o no un directorio
				$DocFiles[$i]=$archivo;
				$i++;
				}
		}
		closedir($route);
		$CantFiles=count($DocFiles);
		
		$Metodo=2;//Actualizar en el web services
		$Type=2;//Ejecutar actualizar en el SP
		
		if(($sw_error==0)&&(base64_decode($_POST['IdLlamadaPortal'])=="")){
			$Metodo=2;//Actualizar en el web services
			$Type=1;//Ejecutar insertar en el SP
		}elseif(($type_llmd==0)&&($sw_error==1)&&(base64_decode($_POST['IdLlamadaPortal'])!="")){
			$Metodo=1;//Insertar en el web services
			$Type=2;//Ejecutar Actualizar en el SP
		}
		
		$ParamUpdLlamada=array(
			"'".base64_decode($_POST['IdLlamadaPortal'])."'",
			"'".base64_decode($_POST['DocEntry'])."'",
			"'".base64_decode($_POST['DocNum'])."'",
			"'".$_POST['TipoTarea']."'",
			"'".$_POST['AsuntoLlamada']."'",
			"'".$_POST['Series']."'",
			"'".$_POST['EstadoLlamada']."'",
			"'".$_POST['OrigenLlamada']."'",
			"'".$_POST['TipoLlamada']."'",
			"'".$_POST['TipoProblema']."'",
			"'".$_POST['SubTipoProblema']."'",
			"'".$_POST['ClienteLlamada']."'",
			"'".$_POST['ContactoCliente']."'",
			"'".$_POST['TelefonoLlamada']."'",
			"'".$_POST['CorreoLlamada']."'",
			"'".$_POST['ArticuloLlamada']."'",
			"'".$_POST['SucursalCliente']."'",
			"'".$_POST['DireccionLlamada']."'",
			"'".$_POST['CiudadLlamada']."'",
			"'".$_POST['BarrioDireccionLlamada']."'",
			"'".$_POST['EmpleadoLlamada']."'",
			"'".LSiqmlObs($_POST['ComentarioLlamada'])."'",
			"'".LSiqmlObs($_POST['ResolucionLlamada'])."'",
			"'".FormatoFecha($_POST['FechaCreacion'])."'",
			"'".FormatoFecha($_POST['FechaCierre'],$_POST['HoraCierre'])."'",
			"$Metodo",
			"'".$_SESSION['CodUser']."'",
			"'".$_SESSION['CodUser']."'",
			"'".$_POST['CDU_EstadoServicio']."'",
			"'".LSiqmlObs($_POST['CDU_Servicios'])."'",
			"'".LSiqmlObs($_POST['CDU_Areas'])."'",
			"'".LSiqmlObs($_POST['CDU_NombreContacto'])."'",
			"'".LSiqmlObs($_POST['CDU_TelefonoContacto'])."'",
			"'".LSiqmlObs($_POST['CDU_CargoContacto'])."'",
			"'".LSiqmlObs($_POST['CDU_CorreoContacto'])."'",
			"'".$_POST['CDU_Reprogramacion1']."'",
			"'".$_POST['CDU_Reprogramacion2']."'",
			"'".$_POST['CDU_Reprogramacion3']."'",
			"'".$_POST['CDU_CausaReprogramacion1']."'",
			"'".$_POST['CDU_CausaReprogramacion2']."'",
			"'".$_POST['CDU_CausaReprogramacion3']."'",
			"'".$_POST['CDU_CanceladoPor']."'",	
			"$Type"
		);		
		$SQL_UpdLlamada=EjecutarSP('sp_tbl_LlamadaServicios',$ParamUpdLlamada,33);
		if($SQL_UpdLlamada){
			if(base64_decode($_POST['IdLlamadaPortal'])==""){
				$row_NewIdLlamada=sqlsrv_fetch_array($SQL_UpdLlamada);
				$IdLlamada=$row_NewIdLlamada[0];
			}else{
				$IdLlamada=base64_decode($_POST['IdLlamadaPortal']);
			}
			
			try{
				//Mover los anexos a la carpeta de archivos de SAP
				$j=0;
				if($sw_error==1){//Si hay un error, limpiar los anexos ya cargados, para volverlos a cargar a la tabla
					//Registrar archivo en la BD
					$ParamDelAnex=array(
						"'191'",
						"'".$IdLlamada."'",
						"NULL",
						"NULL",
						"NULL",
						"'".$_SESSION['CodUser']."'",
						"2"					
					);
					$SQL_DelAnex=EjecutarSP('sp_tbl_DocumentosSAP_Anexos',$ParamDelAnex,33);
				}
				while($j<$CantFiles){
					$Archivo=FormatoNombreAnexo($DocFiles[$j]);
					$NuevoNombre=$Archivo[0];
					$OnlyName=$Archivo[1];
					$Ext=$Archivo[2];
					
					if(file_exists($dir_new)){
						copy($dir.$DocFiles[$j],$dir_new.$NuevoNombre);
						//move_uploaded_file($_FILES['FileArchivo']['tmp_name'],$dir_new.$NuevoNombre);
						copy($dir_new.$NuevoNombre,$RutaAttachSAP[0].$NuevoNombre);

						//Registrar archivo en la BD
						$ParamInsAnex=array(
							"'191'",
							"'".$IdLlamada."'",
							"'".$OnlyName."'",
							"'".$Ext."'",
							"1",
							"'".$_SESSION['CodUser']."'",
							"1"	
						);
						$SQL_InsAnex=EjecutarSP('sp_tbl_DocumentosSAP_Anexos',$ParamInsAnex,33);
						if(!$SQL_InsAnex){
							$sw_error=1;
							$msg_error="Error al actualizar la llamada de servicio";
							//throw new Exception('Error al insertar los anexos.');			
							//sqlsrv_close($conexion);
						}
					}
					$j++;
				}
			}catch (Exception $e) {
				echo 'Excepcion capturada: ',  $e->getMessage(), "\n";
			}		
			
			//Enviar datos al WebServices
			try{
				require_once("includes/conect_ws.php");
				$Parametros=array(
					'pIdLlamada' => $IdLlamada,
					'pLogin'=>$_SESSION['User']
				);
				$Client->InsertarLlamadaServicioPortal($Parametros);
				$Respuesta=$Client->__getLastResponse();
				$Contenido=new SimpleXMLElement($Respuesta,0,false,"s",true);
				$espaciosDeNombres = $Contenido->getNamespaces(true);
				$Nodos = $Contenido->children($espaciosDeNombres['s']);
				$Nodo=	$Nodos->children($espaciosDeNombres['']);
				$Nodo2=	$Nodo->children($espaciosDeNombres['']);
				
				$Archivo=json_decode($Nodo2,true);
				if($Archivo['ID_Respuesta']=="0"){
					//InsertarLog(1, 0, 'Error al generar el informe');
					//throw new Exception('Error al generar el informe. Error de WebServices');		
					$sw_error=1;
					$msg_error=$Archivo['DE_Respuesta'];
					if($_POST['EstadoLlamada']=='-1'){
						$UpdEstado="Update tbl_LlamadasServicios Set Cod_Estado='-3' Where ID_LlamadaServicio='".$IdLlamada."'";
						$SQL_UpdEstado=sqlsrv_query($conexion,$UpdEstado);
					}
				}else{
					sqlsrv_close($conexion);
					header('Location:llamada_servicio.php?id='.$_POST['DocEntry'].'&tl=1&a='.base64_encode("OK_UpdAdd"));	
					//header('Location:llamada_servicio.php?a='.base64_encode("OK_UpdAdd"));	
				}
			}catch (Exception $e) {
				echo 'Excepcion capturada: ',  $e->getMessage(), "\n";
			}
			//sqlsrv_close($conexion);
			//header('Location:gestionar_llamadas_servicios.php?a='.base64_encode("OK_UpdAdd"));	
		}else{
			$sw_error=1;
			$msg_error="Error al actualizar la llamada de servicio";
			//throw new Exception('Error al actualizar la llamada de servicio');				
			//sqlsrv_close($conexion);
			//exit();
		}
	}catch (Exception $e) {
		echo 'Excepcion capturada: ',  $e->getMessage(), "\n";
	}
	
}

if(isset($_POST['P'])&&($_POST['P']==40)){//Reabrir llamada de servicio
	try{
		require_once("includes/conect_ws.php");
			$Parametros=array(
				'pIdLlamada' => base64_decode($_POST['DocEntry']),
				'pLogin'=>$_SESSION['User']
			);
			$Client->ReabrirLlamadaServicioPortal($Parametros);
			//InsertarLog(2, 40, $Cons_UpdCierreLlamada);
			sqlsrv_close($conexion);
			header('Location:llamada_servicio.php?id='.$_POST['DocEntry'].'&tl=1&a='.base64_encode("OK_OpenLlam"));
			//throw new Exception('Error cerrar la llamada de servicio');				
			//sqlsrv_close($conexion);
			//exit();
	}catch (Exception $e) {
		//InsertarLog(1, 40, $Cons_UpdCierreLlamada);
		echo 'Excepcion capturada: ',  $e->getMessage(), "\n";
	}
}

if(isset($_GET['dt_LS'])&&($_GET['dt_LS'])==1){//Verificar que viene de una Llamada de servicio (Datos Llamada servicio)
	$dt_LS=1;
	
	//Clientes
	$SQL_Cliente=Seleccionar('uvw_Sap_tbl_Clientes','*',"CodigoCliente='".base64_decode($_GET['Cardcode'])."'",'NombreCliente');
	$row_Cliente=sqlsrv_fetch_array($SQL_Cliente);
	
	//Contacto cliente
	$SQL_ContactoCliente=Seleccionar('uvw_Sap_tbl_ClienteContactos','*',"CodigoCliente='".base64_decode($_GET['Cardcode'])."'",'NombreContacto');
		
	//Sucursal cliente
	$SQL_SucursalCliente=Seleccionar('uvw_Sap_tbl_Clientes_Sucursales','*',"CodigoCliente='".base64_decode($_GET['Cardcode'])."'",'NombreSucursal');
}

if($type_llmd==1){
	//Llamada
	$SQL=Seleccionar('uvw_Sap_tbl_LlamadasServicios','*',"ID_LlamadaServicio='".$IdLlamada."'");
	$row=sqlsrv_fetch_array($SQL);
	
	//Clientes	
	$SQL_Cliente=Seleccionar("uvw_Sap_tbl_Clientes","CodigoCliente, NombreCliente","CodigoCliente='".$row['ID_CodigoCliente']."'",'NombreCliente');	
	
	//Contactos clientes
	$SQL_ContactoCliente=Seleccionar('uvw_Sap_tbl_ClienteContactos','*',"CodigoCliente='".$row['ID_CodigoCliente']."'",'NombreContacto');

	//Sucursales
	$SQL_SucursalCliente=Seleccionar('uvw_Sap_tbl_Clientes_Sucursales','*',"CodigoCliente='".$row['ID_CodigoCliente']."' and TipoDireccion='S'",'NombreSucursal');

	//Anexos
	$SQL_AnexoLlamada=Seleccionar('uvw_Sap_tbl_DocumentosSAP_Anexos','*',"AbsEntry='".$row['IdAnexoLlamada']."'");

	//Articulos del cliente (ID servicio)
	$ParamArt=array(
		"'".$row['ID_CodigoCliente']."'",
		"'".$row['NombreSucursal']."'",
		"'0'"
	);
	$SQL_Articulos=EjecutarSP('sp_ConsultarArticulosLlamadas',$ParamArt);

	//Activides relacionadas
	$SQL_Actividad=Seleccionar('uvw_Sap_tbl_Actividades','*',"ID_LlamadaServicio='".$IdLlamada."'",'ID_Actividad');
	
	//Documentos relacionados
	$SQL_DocRel=Seleccionar('uvw_Sap_tbl_LlamadasServiciosDocRelacionados','*',"ID_LlamadaServicio='".$IdLlamada."'");
	
	//Panoramas de riesgos
	$SQL_FrmHallazgos=Seleccionar('uvw_tbl_FrmHallazgos','*',"ID_LlamadaServicio='".$IdLlamada."'",'ID_Frm');
	
}

if($sw_error==1){
	//Si ocurre un error, vuelvo a consultar los datos insertados desde la base de datos.
	$SQL=Seleccionar('uvw_tbl_LlamadasServicios','*',"ID_LlamadaServicio='".$IdLlamada."'");
	$row=sqlsrv_fetch_array($SQL);
	
	//Clientes
	$SQL_Cliente=Seleccionar("uvw_Sap_tbl_Clientes","CodigoCliente, NombreCliente","CodigoCliente='".$row['ID_CodigoCliente']."'",'NombreCliente');
	
	//Contactos clientes
	$SQL_ContactoCliente=Seleccionar('uvw_Sap_tbl_ClienteContactos','*',"CodigoCliente='".$row['ID_CodigoCliente']."'",'NombreContacto');

	//Sucursales
	$SQL_SucursalCliente=Seleccionar('uvw_Sap_tbl_Clientes_Sucursales','*',"CodigoCliente='".$row['ID_CodigoCliente']."' and TipoDireccion='S'",'NombreSucursal');
	
	//Articulos del cliente (ID servicio)
	$ParamArt=array(
		"'".$row['ID_CodigoCliente']."'",
		"'".$row['NombreSucursal']."'",
		"'0'"
	);
	$SQL_Articulos=EjecutarSP('sp_ConsultarArticulosLlamadas',$ParamArt);
	
	//Activides relacionadas
	$SQL_Actividad=Seleccionar('uvw_Sap_tbl_Actividades','*',"ID_LlamadaServicio='".$IdLlamada."'",'ID_Actividad');
	
	//Documentos relacionados
	$SQL_DocRel=Seleccionar('uvw_Sap_tbl_LlamadasServiciosDocRelacionados','*',"ID_LlamadaServicio='".$IdLlamada."'");
	
	//Panoramas de riesgos
	$SQL_FrmHallazgos=Seleccionar('uvw_tbl_FrmHallazgos','*',"ID_LlamadaServicio='".$IdLlamada."'",'ID_Frm');
}

//Tipo de llamada
$SQL_TipoLlamadas=Seleccionar('uvw_Sap_tbl_TipoLlamadas','*','','DeTipoLlamada');

//Serie de llamada
$ParamSerie=array(
	"'".$_SESSION['CodUser']."'",
	"'191'"
);
$SQL_Series=EjecutarSP('sp_ConsultarSeriesDocumentos',$ParamSerie);

//Origen de llamada
$SQL_OrigenLlamada=Seleccionar('uvw_Sap_tbl_LlamadasServiciosOrigen','*','','DeOrigenLlamada');

//Tipo problema llamadas
$SQL_TipoProblema=Seleccionar('uvw_Sap_tbl_TipoProblemasLlamadas','*','','DeTipoProblemaLlamada');

//SubTipo problema llamadas
$SQL_SubTipoProblema=Seleccionar('uvw_Sap_tbl_SubTipoProblemasLlamadas','*','','DeSubTipoProblemaLlamada');

//Estado servicio llamada
$SQL_EstServLlamada=Seleccionar('uvw_Sap_tbl_LlamadasServiciosEstadoServicios','*','','DeEstadoServicio');

//Cancelado por llamada
$SQL_CanceladoPorLlamada=Seleccionar('uvw_Sap_tbl_LlamadasServiciosCanceladoPor','*','','DeCanceladoPor','DESC');

//Causa reprogramacion llamada
$SQL_CausaReprog=Seleccionar('uvw_Sap_tbl_LlamadasServiciosReprogramacion','*','','DeReprogramacion');

//Cola llamada
//$SQL_ColaLlamada=Seleccionar('uvw_Sap_tbl_ColaLlamadas','*','','DeColaLlamada');

//Empleados
$SQL_EmpleadoLlamada=Seleccionar('uvw_Sap_tbl_Empleados','*',"UsuarioSAP <> ''",'NombreEmpleado');

//Estado llamada
$SQL_EstadoLlamada=Seleccionar('uvw_tbl_EstadoLlamada','*');

?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php echo $Title;?> | <?php echo NOMBRE_PORTAL;?></title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<?php 
if(isset($_GET['a'])&&($_GET['a']==base64_encode("OK_LlamAdd"))){
	echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Listo!',
                text: 'La llamada de servicio ha sido creada exitosamente.',
                icon: 'success'
            });
		});		
		</script>";
}
if(isset($_GET['a'])&&($_GET['a']==base64_encode("OK_UpdAdd"))){
	echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Listo!',
                text: 'La llamada de servicio ha sido actualizada exitosamente.',
                icon: 'success'
            });
		});		
		</script>";
}
if(isset($_GET['a'])&&($_GET['a']==base64_encode("OK_ActAdd"))){
	echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Listo!',
                text: 'La actividad ha sido agregada exitosamente.',
                icon: 'success'
            });
		});		
		</script>";
}
if(isset($_GET['a'])&&($_GET['a']==base64_encode("OK_UpdActAdd"))){
	echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Listo!',
                text: 'La actividad ha sido actualizada exitosamente.',
                icon: 'success'
            });
		});		
		</script>";
}
if(isset($_GET['a'])&&($_GET['a']==base64_encode("OK_OVenAdd"))){
	echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Listo!',
                text: 'La Orden de venta ha sido agregada exitosamente.',
                icon: 'success'
            });
		});		
		</script>";
}
if(isset($_GET['a'])&&($_GET['a']==base64_encode("OK_OVenUpd"))){
	echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Listo!',
                text: 'La Orden de venta ha sido actualizada exitosamente.',
                icon: 'success'
            });
		});		
		</script>";
}
if(isset($_GET['a'])&&($_GET['a']==base64_encode("OK_EVenAdd"))){
	echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Listo!',
                text: 'La Entrega de venta ha sido agregada exitosamente.',
                icon: 'success'
            });
		});		
		</script>";
}
if(isset($_GET['a'])&&($_GET['a']==base64_encode("OK_EVenUpd"))){
	echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Listo!',
                text: 'La Entrega de venta ha sido actualizada exitosamente.',
                icon: 'success'
            });
		});		
		</script>";
}
if(isset($_GET['a'])&&($_GET['a']==base64_encode("OK_OpenLlam"))){
	echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Listo!',
                text: 'La llamada de servicio ha sido abierta nuevamente.',
                icon: 'success'
            });
		});		
		</script>";
}
if(isset($_GET['a'])&&($_GET['a']==base64_encode("OK_DelAct"))){
	echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Listo!',
                text: 'La actividad ha sido eliminado exitosamente.',
                icon: 'success'
            });
		});		
		</script>";
}
if(isset($_GET['a'])&&($_GET['a']==base64_encode("OK_OpenAct"))){
	echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Listo!',
                text: 'La actividad ha sido abierta nuevamente.',
                icon: 'success'
            });
		});		
		</script>";
}
if(isset($_GET['a'])&&($_GET['a']==base64_encode("OK_FrmAdd"))){
	echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Listo!',
                text: 'El hallazgo ha sido registrado exitosamente.',
                icon: 'success'
            });
		});		
		</script>";
}
if(isset($_GET['a'])&&($_GET['a']==base64_encode("OK_FrmUpd"))){
	echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Listo!',
                text: 'El hallazgo ha sido actualizado exitosamente.',
                icon: 'success'
            });
		});		
		</script>";
}
if(isset($sw_error)&&($sw_error==1)){
	echo "<script>
		$(document).ready(function() {
			Swal.fire({
                title: '¡Ha ocurrido un error!',
                text: '".LSiqmlObs($msg_error)."',
                icon: 'error'
            });
		});		
		</script>";
}
?>
<style>
	.ibox-title a{
		color: inherit !important;
	}
	.collapse-link:hover{
		cursor: pointer;
	}
</style>
<script type="text/javascript">
	$(document).ready(function() {//Cargar los combos dependiendo de otros
		$("#ClienteLlamada").change(function(){
			$('.ibox-content').toggleClass('sk-loading',true);
			var Cliente=document.getElementById('ClienteLlamada').value;
			$.ajax({
				type: "POST",
				url: "ajx_cbo_select.php?type=2&id="+Cliente,
				success: function(response){
					$('#ContactoCliente').html(response).fadeIn();
					$('#ContactoCliente').trigger('change');
					$('.ibox-content').toggleClass('sk-loading',false);
				}
			});
			$.ajax({
				type: "POST",
				url: "ajx_cbo_select.php?type=3&id="+Cliente+"&tdir=S",
				success: function(response){
					$('#SucursalCliente').html(response).fadeIn();
					$('#SucursalCliente').trigger('change');
					$('.ibox-content').toggleClass('sk-loading',false);
				}
			});
			$('.ibox-content').toggleClass('sk-loading',false);
		});
		$("#SucursalCliente").change(function(){
			$('.ibox-content').toggleClass('sk-loading',true);
			var Cliente=document.getElementById('ClienteLlamada').value;
			var Sucursal=document.getElementById('SucursalCliente').value;
			$.ajax({
				url:"ajx_buscar_datos_json.php",
				data:{type:1,CardCode:Cliente,Sucursal:Sucursal},
				dataType:'json',
				success: function(data){
					document.getElementById('DireccionLlamada').value=data.Direccion;
					document.getElementById('BarrioDireccionLlamada').value=data.Barrio;
					document.getElementById('CiudadLlamada').value=data.Ciudad;
					document.getElementById('CDU_NombreContacto').value=data.NombreContacto;
					document.getElementById('CDU_TelefonoContacto').value=data.TelefonoContacto;
					document.getElementById('CDU_CargoContacto').value=data.CargoContacto;
					document.getElementById('CDU_CorreoContacto').value=data.CorreoContacto;
				}
			});
			$.ajax({
				type: "POST",
				url: "ajx_cbo_select.php?type=11&id="+Cliente+"&suc="+Sucursal,
				success: function(response){
					$('#ArticuloLlamada').html(response).fadeIn();
					$('#ArticuloLlamada').trigger('change');
					$('.ibox-content').toggleClass('sk-loading',false);
				}
			});
		});
		$("#ContactoCliente").change(function(){
			$('.ibox-content').toggleClass('sk-loading',true);
			var Contacto=document.getElementById('ContactoCliente').value;
			$.ajax({
				url:"ajx_buscar_datos_json.php",
				data:{type:5,Contacto:Contacto},
				dataType:'json',
				success: function(data){
					document.getElementById('TelefonoLlamada').value=data.Telefono;
					document.getElementById('CorreoLlamada').value=data.Correo;
					$('.ibox-content').toggleClass('sk-loading',false);
				}
			});
		});
		$("#ArticuloLlamada").change(function(){
			$('.ibox-content').toggleClass('sk-loading',true);
			var ID=document.getElementById('ArticuloLlamada').value;
			if(ID!=""){
				$.ajax({
					url:"ajx_buscar_datos_json.php",
					data:{type:6,id:ID},
					dataType:'json',
					success: function(data){
						document.getElementById('CDU_Servicios').value=data.Servicios;
						document.getElementById('CDU_Areas').value=data.Areas;
						$('.ibox-content').toggleClass('sk-loading',false);
					}
				});
			}else{
				document.getElementById('CDU_Servicios').value='';
				document.getElementById('CDU_Areas').value='';
				/*document.getElementById('CDU_NombreContacto').value='';
				document.getElementById('CDU_TelefonoContacto').value='';
				document.getElementById('CDU_CargoContacto').value='';
				document.getElementById('CDU_CorreoContacto').value='';*/
				$('.ibox-content').toggleClass('sk-loading',false);	
			}
			$('.ibox-content').toggleClass('sk-loading',false);			
		});
		$("#TipoTarea").change(function(){
			$('.ibox-content').toggleClass('sk-loading',true);
			var TipoTarea=document.getElementById('TipoTarea').value;
			if(TipoTarea=="Interna"){
				document.getElementById('ClienteLlamada').value='<?php echo NIT_EMPRESA;?>';
				document.getElementById('NombreClienteLlamada').value='<?php echo NOMBRE_EMPRESA;?>';
				document.getElementById('NombreClienteLlamada').readOnly=true;
				$('#ClienteLlamada').trigger('change');
				$('.ibox-content').toggleClass('sk-loading',false);
				//HabilitarCampos(0);
			}else{
				document.getElementById('ClienteLlamada').value='';
				document.getElementById('NombreClienteLlamada').value='';
				document.getElementById('NombreClienteLlamada').readOnly=false;
				$('#ClienteLlamada').trigger('change');
				$('.ibox-content').toggleClass('sk-loading',false);
				//HabilitarCampos(1);
				
			}		
		});
		/*$("#TipoLlamada").change(function(){
			$.ajax({
				type: "POST",
				url: "ajx_cbo_select.php?type=15&id="+document.getElementById('TipoLlamada').value,
				success: function(response){
					$('#TipoProblema').html(response).fadeIn();
				}
			});
		});*/
	});

function HabilitarCampos(type=1){
	if(type==0){//Deshabilitar
		document.getElementById('DatosCliente').style.display='none';
		document.getElementById('swTipo').value="1";
	}else{//Habilitar
		document.getElementById('DatosCliente').style.display='block';
		document.getElementById('swTipo').value="0";
	}
}
function ConsultarDatosCliente(){
	var Cliente=document.getElementById('ClienteLlamada');
	if(Cliente.value!=""){
		self.name='opener';
	remote=open('socios_negocios.php?id='+Base64.encode(Cliente.value)+'&ext=1&tl=1','remote','location=no,scrollbar=yes,menubars=no,toolbars=no,resizable=yes,fullscreen=yes,status=yes');
	remote.focus();
	}
}
function ConsultarArticulo(){
	var Articulo=document.getElementById('ArticuloLlamada');
	if(Articulo.value!=""){
		self.name='opener';
		remote=open('articulos.php?id='+Base64.encode(Articulo.value)+'&ext=1&tl=1','remote','location=no,scrollbar=yes,menubars=no,toolbars=no,resizable=yes,fullscreen=yes,status=yes');
		remote.focus();
	}
}
function ValidarCampos(){
	var result=true;
	
	var EstadoServicio = document.getElementById("CDU_EstadoServicio");
	var CanceladoPor = document.getElementById("CDU_CanceladoPor");
	
	var FReprog1 = document.getElementById("CDU_Reprogramacion1");
	var CausaReprog1 = document.getElementById("CDU_CausaReprogramacion1");
	
	var FReprog2 = document.getElementById("CDU_Reprogramacion2");
	var CausaReprog2 = document.getElementById("CDU_CausaReprogramacion2");
	
	var FReprog3 = document.getElementById("CDU_Reprogramacion3");
	var CausaReprog3 = document.getElementById("CDU_CausaReprogramacion3");
	
	if(EstadoServicio.value=='2'){
		if (CanceladoPor.value=='' || CanceladoPor.value=='N/A'){
			result=false;
			swal({
				title: '¡Error!',
				text: 'Debe seleccionar un valor en el campo Cancelado Por.',
				type: 'error'
			});			
		}else{
			result=true;
		}			
	}
	
	if(FReprog1.value!="" || FReprog2.value!="" || FReprog3.value!=""){
		if(FReprog1.value!=""  && CausaReprog1.value==""){
			result=false;
			swal({
				title: '¡Error!',
				text: 'Debe seleccionar una causa en la Reprogramación 1.',
				type: 'error'
			});			
		}
		if(FReprog2.value!="" && CausaReprog2.value==""){
			result=false;
			swal({
				title: '¡Error!',
				text: 'Debe seleccionar una causa en la Reprogramación 2.',
				type: 'error'
			});			
		}
		if(FReprog3.value!="" && CausaReprog3.value==""){
			result=false;
			swal({
				title: '¡Error!',
				text: 'Debe seleccionar una causa en la Reprogramación 3.',
				type: 'error'
			});			
		}
	}
	
	return result;
}
</script>
<!-- InstanceEndEditable -->
</head>

<body>

<div id="wrapper">

    <?php include("includes/menu.php"); ?>

    <div id="page-wrapper" class="gray-bg">
        <?php include("includes/menu_superior.php"); ?>
        <!-- InstanceBeginEditable name="Contenido" -->
        <div class="row wrapper border-bottom white-bg page-heading">
                <div class="col-sm-8">
                    <h2><?php echo $Title;?></h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="index1.php">Inicio</a>
                        </li>
                        <li>
                            <a href="#">Gesti&oacute;n de tareas</a>
                        </li>
                        <li>
                            <a href="gestionar_llamadas_servicios.php">Gestionar llamadas de servicios</a>
                        </li>
                        <li class="active">
                            <strong><?php echo $Title;?></strong>
                        </li>
                    </ol>
                </div>
            </div>
           
         <div class="wrapper wrapper-content">
			<?php if($type_llmd==1){?>
			<div class="row">
				<div class="col-lg-3">
					<div class="ibox ">
						<div class="ibox-title">
							<h5><span class="font-normal">Llamada de servicio</span></h5>
						</div>
						<div class="ibox-content">
							<h3 class="no-margins"><?php echo $row['DocNum'];?></h3>
						</div>
					</div>
				</div>
				<div class="col-lg-3">
					<div class="ibox ">
						<div class="ibox-title">
							<h5><span class="font-normal">Creada por</span></h5>
						</div>
						<div class="ibox-content">
							<h3 class="no-margins"><?php if($row['UsuarioCreacion']!=""){echo $row['UsuarioCreacion'];}else{echo "&nbsp;";}?></h3>
						</div>
					</div>
				</div>
				<div class="col-lg-3">
					<div class="ibox ">
						<div class="ibox-title">
							<h5><span class="font-normal">Actualizado por</span></h5>
						</div>
						<div class="ibox-content">
							<h3 class="no-margins"><?php if($row['UsuarioActualizacion']!=""){echo $row['UsuarioActualizacion'];}else{echo "&nbsp;";}?></h3>
						</div>
					</div>
				</div>
				<div class="col-lg-3">
					<div class="ibox ">
						<div class="ibox-title">
							<h5><span class="font-normal">Fecha actualización</span></h5>
						</div>
						<div class="ibox-content">
							<h3 class="no-margins"><?php if($row['FechaActualizacion']!=""){ echo $row['FechaActualizacion']." ".$row['HoraActualizacion'];}else{echo "&nbsp;";}?></h3>
						</div>
					</div>
				</div>
			</div>
			<?php }?>
			<?php if($type_llmd==1){?>
				<div class="ibox-content">
				<?php include("includes/spinner.php"); ?>
					<div class="row">
						<div class="col-lg-12"> 
							<div class="ibox">
								<div class="ibox-title bg-success">
									<h5 class="collapse-link"><i class="fa fa-play-circle"></i> Acciones</h5>
									 <a class="collapse-link pull-right">
										<i class="fa fa-chevron-up"></i>
									</a>	
								</div>
								<div class="ibox-content">
									<div class="form-group">
										<div class="col-lg-6">
											<a href="sapdownload.php?id=<?php echo base64_encode('15');?>&type=<?php echo base64_encode('2');?>&DocKey=<?php echo base64_encode($row['ID_LlamadaServicio']);?>&ObType=<?php echo base64_encode('191');?>&IdFrm=<?php echo base64_encode($row['Series']);?>" target="_blank" class="btn btn-outline btn-success"><i class="fa fa-download"></i> Descargar formato</a>
											<a href="#" class="btn btn-outline btn-primary"><i class="fa fa-envelope"></i> Enviar correo</a>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>			
			<br>
			<?php }?>
			 <div class="ibox-content">
				  <?php include("includes/spinner.php"); ?>
          <div class="row"> 
           <div class="col-lg-12">
              <form action="llamada_servicio.php" method="post" class="form-horizontal" enctype="multipart/form-data" id="CrearLlamada">
				<div id="DatosCliente" <?php //if($row['TipoTarea']=='Interna'){ echo 'style="display: none;"';}?>>
				<div class="ibox">
					<div class="ibox-title bg-success">
						<h5 class="collapse-link"><i class="fa fa-group"></i> Información de cliente</h5>
						 <a class="collapse-link pull-right">
							<i class="fa fa-chevron-up"></i>
						</a>	
					</div>
					<div class="ibox-content">	
						<div class="form-group">
							<label class="col-lg-1 control-label"><i onClick="ConsultarDatosCliente();" title="Consultar cliente" style="cursor: pointer" class="btn-xs btn-success fa fa-search"></i> Cliente</label>
							<div class="col-lg-3">
								<input name="ClienteLlamada" type="hidden" id="ClienteLlamada" value="<?php if(($type_llmd==1)||($sw_error==1)){echo $row['ID_CodigoCliente'];}elseif($dt_LS==1){echo $row_Cliente['CodigoCliente'];}?>">
								<input name="NombreClienteLlamada" type="text" required="required" class="form-control" id="NombreClienteLlamada" placeholder="Digite para buscar..." <?php if(($type_llmd==1)&&(!PermitirFuncion(302)||($row['IdEstadoLlamada']=='-1')||($row['TipoTarea']=='Interna'))||($dt_LS==1)||($type_llmd==1)){ echo "readonly='readonly'";}?> value="<?php if(($type_llmd==1)||($sw_error==1)){echo $row['NombreClienteLlamada'];}elseif($dt_LS==1){echo $row_Cliente['NombreCliente'];}?>">
							</div>
							<label class="col-lg-1 control-label">Contacto</label>
							<div class="col-lg-3">
								<select name="ContactoCliente" class="form-control" id="ContactoCliente" <?php if(($type_llmd==1)&&(!PermitirFuncion(302)||($row['IdEstadoLlamada']=='-1'))){ echo "disabled='disabled'";}?>>
								  <?php if(($type_llmd==0)||($sw_error==1)){?><option value="">Seleccione...</option><?php }?>
								  <?php if(($type_llmd==1)||($sw_error==1)){while($row_ContactoCliente=sqlsrv_fetch_array($SQL_ContactoCliente)){?>
										<option value="<?php echo $row_ContactoCliente['CodigoContacto'];?>" <?php if((isset($row['IdContactoLLamada']))&&(strcmp($row_ContactoCliente['CodigoContacto'],$row['IdContactoLLamada'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_ContactoCliente['ID_Contacto'];?></option>
								  <?php }}?>
								</select>
							</div>
							<label class="col-lg-1 control-label">Sucursal</label>
							<div class="col-lg-3">
								<select name="SucursalCliente" class="form-control select2" id="SucursalCliente" <?php if(($type_llmd==1)&&(!PermitirFuncion(302)||($row['IdEstadoLlamada']=='-1'))){ echo "disabled='disabled'";}?>>
								  <?php if(($type_llmd==0)||($sw_error==1)){?><option value="">Seleccione...</option><?php }?>
								  <?php if(($type_llmd==1)||($sw_error==1)){while($row_SucursalCliente=sqlsrv_fetch_array($SQL_SucursalCliente)){?>
										<option value="<?php echo $row_SucursalCliente['NombreSucursal'];?>" <?php if((isset($row['NombreSucursal']))&&(strcmp($row_SucursalCliente['NombreSucursal'],$row['NombreSucursal'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_SucursalCliente['NombreSucursal'];?></option>
								  <?php }}?>
								</select>
							</div>
						</div>
						<div class="form-group">
							<label class="col-lg-1 control-label">Dirección</label>
							<div class="col-lg-3">
								<input name="DireccionLlamada" type="text" required="required" class="form-control" id="DireccionLlamada" maxlength="100" <?php if(($type_llmd==1)&&(!PermitirFuncion(302)||($row['IdEstadoLlamada']=='-1'))){ echo "readonly='readonly'";}?> value="<?php if(($type_llmd==1)||($sw_error==1)){echo $row['DireccionLlamada'];}?>">
							</div>
							<label class="col-lg-1 control-label">Barrio</label>
							<div class="col-lg-3">
								<input name="BarrioDireccionLlamada" type="text" class="form-control" id="BarrioDireccionLlamada" maxlength="50" <?php if(($type_llmd==1)&&(!PermitirFuncion(302)||($row['IdEstadoLlamada']=='-1'))){ echo "readonly='readonly'";}?> value="<?php if(($type_llmd==1)||($sw_error==1)){echo $row['BarrioDireccionLlamada'];}?>">
							</div>
							<label class="col-lg-1 control-label">Teléfono</label>
							<div class="col-lg-3">
								<input name="TelefonoLlamada" type="text" class="form-control" required="required" id="TelefonoLlamada" maxlength="50" <?php if(($type_llmd==1)&&(!PermitirFuncion(302)||($row['IdEstadoLlamada']=='-1'))){ echo "readonly='readonly'";}?> value="<?php if(($type_llmd==1)||($sw_error==1)){echo $row['TelefonoContactoLlamada'];}?>">
							</div>
						</div>
						<div class="form-group">
							<label class="col-lg-1 control-label">Ciudad</label>
							<div class="col-lg-3">
								<input name="CiudadLlamada" type="text" class="form-control" id="CiudadLlamada" maxlength="100" value="<?php if(($type_llmd==1)||($sw_error==1)){echo $row['CiudadLlamada'];}?>" <?php if(($type_llmd==1)&&(!PermitirFuncion(302)||($row['IdEstadoLlamada']=='-1'))){ echo "readonly='readonly'";}?>>
							</div>
							<label class="col-lg-1 control-label">Correo</label>
							<div class="col-lg-3">
								<input name="CorreoLlamada" type="text" class="form-control" id="CorreoLlamada" maxlength="100" <?php if(($type_llmd==1)&&(!PermitirFuncion(302)||($row['IdEstadoLlamada']=='-1'))){ echo "readonly='readonly'";}?> value="<?php if(($type_llmd==1)||($sw_error==1)){echo $row['CorreoContactoLlamada'];}?>">
							</div>
						</div>
						<div class="form-group">					
							<label class="col-lg-1 control-label"><i onClick="ConsultarArticulo();" title="Consultar ID Servicio" style="cursor: pointer" class="btn-xs btn-success fa fa-search"></i> ID servicio</label>
							<div class="col-lg-7">
								<select name="ArticuloLlamada" required class="form-control select2" id="ArticuloLlamada" <?php if(($type_llmd==1)&&(!PermitirFuncion(302)||($row['IdEstadoLlamada']=='-1'))){ echo "disabled='disabled'";}?>>
										<option value="">Seleccione...</option>
									<?php if(($type_llmd==1)||($sw_error==1)){while($row_Articulos=sqlsrv_fetch_array($SQL_Articulos)){?>
										<option value="<?php echo $row_Articulos['ItemCode'];?>" <?php if((isset($row['IdArticuloLlamada']))&&(strcmp($row_Articulos['ItemCode'],$row['IdArticuloLlamada'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Articulos['ItemCode']." - ".$row_Articulos['ItemName']." (SERV: ".substr($row_Articulos['Servicios'],0,20)." - ÁREA: ".substr($row_Articulos['Areas'],0,20).")";?></option>
									<?php }}?>
								</select>
							</div>
						</div>
					</div>
				</div>
				</div>
				<div class="ibox">
					<div class="ibox-title bg-success">
						<h5 class="collapse-link"><i class="fa fa-info-circle"></i> Información de llamada</h5>
						 <a class="collapse-link pull-right">
							<i class="fa fa-chevron-up"></i>
						</a>	
					</div>
					<div class="ibox-content">
						<div class="form-group">
							<label class="col-lg-1 control-label">Asunto de llamada</label>
							<div class="col-lg-5">
								<input autocomplete="off" name="AsuntoLlamada" type="text" required="required" class="form-control" id="AsuntoLlamada" maxlength="150" <?php if(($type_llmd==1)&&(!PermitirFuncion(302)||($row['IdEstadoLlamada']=='-1'))){ echo "readonly='readonly'";}?> value="<?php if(($type_llmd==1)||($sw_error==1)){echo $row['AsuntoLlamada'];}else{echo $TituloLlamada;}?>">
							</div>							
							<label class="col-lg-1 control-label">Tipo tarea</label>
							<div class="col-lg-2">
								<select name="TipoTarea" class="form-control" id="TipoTarea" <?php if(($type_llmd==1)&&(!PermitirFuncion(302)||($row['IdEstadoLlamada']=='-1'))){ echo "disabled='disabled'";}?>>
									<option value="Externa" <?php if((isset($row['TipoTarea']))&&($row['TipoTarea']=='Externa')){ echo "selected=\"selected\"";}?>>Externa</option>
									<option value="Interna" <?php if((isset($row['TipoTarea']))&&($row['TipoTarea']=='Interna')){ echo "selected=\"selected\"";}?>>Interna</option>
								</select>
							</div>
							<label class="col-lg-1 control-label">Llamada servicio</label>
							<div class="col-lg-2">
								<input autocomplete="off" name="Ticket" type="text" class="form-control" id="Ticket" maxlength="150" readonly="readonly" value="<?php if(($type_llmd==1)||($sw_error==1)){echo $row['DocNum'];}?>">
							</div>
						</div>
						<div class="form-group">
							<label class="col-lg-1 control-label">Origen</label>
							<div class="col-lg-2">
								<select name="OrigenLlamada" class="form-control" required="required" id="OrigenLlamada" <?php if(($type_llmd==1)&&(!PermitirFuncion(302)||($row['IdEstadoLlamada']=='-1'))){ echo "disabled='disabled'";}?>>
										<option value="">Seleccione...</option>
								  <?php while($row_OrigenLlamada=sqlsrv_fetch_array($SQL_OrigenLlamada)){?>
										<option value="<?php echo $row_OrigenLlamada['IdOrigenLlamada'];?>" <?php if((isset($row['IdOrigenLlamada']))&&(strcmp($row_OrigenLlamada['IdOrigenLlamada'],$row['IdOrigenLlamada'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_OrigenLlamada['DeOrigenLlamada'];?></option>
								  <?php }?>
								</select>
							</div>
							<label class="col-lg-1 control-label">Tipo llamada</label>
							<div class="col-lg-2">
								<select name="TipoLlamada" class="form-control" required="required" id="TipoLlamada" <?php if(($type_llmd==1)&&(!PermitirFuncion(302)||($row['IdEstadoLlamada']=='-1'))){ echo "disabled='disabled'";}?>>
										<option value="">Seleccione...</option>
								  <?php while($row_TipoLlamadas=sqlsrv_fetch_array($SQL_TipoLlamadas)){?>
										<option value="<?php echo $row_TipoLlamadas['IdTipoLlamada'];?>" <?php if((isset($row['IdTipoLlamada']))&&(strcmp($row_TipoLlamadas['IdTipoLlamada'],$row['IdTipoLlamada'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_TipoLlamadas['DeTipoLlamada'];?></option>
								  <?php }?>
								</select>
							</div>
							<div class="col-lg-3">&nbsp;</div>
							<label class="col-lg-1 control-label">Estado</label>
							<div class="col-lg-2">
								<select name="EstadoLlamada" class="form-control" id="EstadoLlamada" <?php if(($type_llmd==1)&&(!PermitirFuncion(302)||($row['IdEstadoLlamada']=='-1'))){ echo "disabled='disabled'";}?>>
								  <?php while($row_EstadoLlamada=sqlsrv_fetch_array($SQL_EstadoLlamada)){?>
										<option value="<?php echo $row_EstadoLlamada['Cod_Estado'];?>" <?php if((isset($row['IdEstadoLlamada']))&&(strcmp($row_EstadoLlamada['Cod_Estado'],$row['IdEstadoLlamada'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_EstadoLlamada['NombreEstado'];?></option>
								  <?php }?>
								</select>
							</div>
						</div>
						<div class="form-group">
							<label class="col-lg-1 control-label">Tipo problema</label>
							<div class="col-lg-2">
								<select name="TipoProblema" class="form-control" id="TipoProblema" required <?php if(($type_llmd==1)&&(!PermitirFuncion(302)||($row['IdEstadoLlamada']=='-1'))){ echo "disabled='disabled'";}?>>
										<option value="">Seleccione...</option>
								  <?php while($row_TipoProblema=sqlsrv_fetch_array($SQL_TipoProblema)){?>
										<option value="<?php echo $row_TipoProblema['IdTipoProblemaLlamada'];?>" <?php if((isset($row['IdTipoProblemaLlamada']))&&(strcmp($row_TipoProblema['IdTipoProblemaLlamada'],$row['IdTipoProblemaLlamada'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_TipoProblema['DeTipoProblemaLlamada'];?></option>
								  <?php }?>
								</select>
							</div>
							<label class="col-lg-1 control-label">SubTipo problema</label>
							<div class="col-lg-2">
								<select name="SubTipoProblema" class="form-control" required id="SubTipoProblema" <?php if(($type_llmd==1)&&(!PermitirFuncion(302)||($row['IdEstadoLlamada']=='-1'))){ echo "disabled='disabled'";}?>>
										<option value="">Seleccione...</option>
								  <?php while($row_SubTipoProblema=sqlsrv_fetch_array($SQL_SubTipoProblema)){?>
										<option value="<?php echo $row_SubTipoProblema['IdSubTipoProblemaLlamada'];?>" <?php if((isset($row['IdSubTipoProblemaLlamada']))&&(strcmp($row_SubTipoProblema['IdSubTipoProblemaLlamada'],$row['IdSubTipoProblemaLlamada'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_SubTipoProblema['DeSubTipoProblemaLlamada'];?></option>
								  <?php }?>
								</select>
							</div>
							<div class="col-lg-3">&nbsp;</div>
							<label class="col-lg-1 control-label">Serie</label>
							<div class="col-lg-2">
								<select name="Series" class="form-control" id="Series" <?php if(($type_llmd==1)&&(!PermitirFuncion(302)||($row['IdEstadoLlamada']=='-1'))){ echo "disabled='disabled'";}?>>
								  <?php while($row_Series=sqlsrv_fetch_array($SQL_Series)){?>
										<option value="<?php echo $row_Series['IdSeries'];?>" <?php if((isset($row['Series']))&&(strcmp($row_Series['IdSeries'],$row['Series'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Series['DeSeries'];?></option>
								  <?php }?>
								</select>
							</div>
						</div>
						<div class="form-group">
							<label class="col-lg-1 control-label">Asignado a</label>
							<div class="col-lg-5">
								<select name="EmpleadoLlamada" class="form-control select2" id="EmpleadoLlamada" <?php if(($type_llmd==1)&&(!PermitirFuncion(302)||($row['IdEstadoLlamada']=='-1'))){ echo "disabled='disabled'";}?>>
										<option value="">(Sin asignar)</option>
								  <?php while($row_EmpleadoLlamada=sqlsrv_fetch_array($SQL_EmpleadoLlamada)){?>
										<option value="<?php echo $row_EmpleadoLlamada['ID_Empleado'];?>" <?php if((isset($row['IdAsignadoA']))&&(strcmp($row_EmpleadoLlamada['ID_Empleado'],$row['IdAsignadoA'])==0)){ echo "selected=\"selected\"";}elseif(($type_llmd==0)&&(isset($_SESSION['CodigoSAP']))&&(strcmp($row_EmpleadoLlamada['ID_Empleado'],$_SESSION['CodigoSAP'])==0)){echo "selected=\"selected\"";}?>><?php echo $row_EmpleadoLlamada['NombreEmpleado'];?></option>
								  <?php }?>
								</select>
							</div>
							<label class="col-lg-1 control-label">Cola</label>
							<div class="col-lg-2">
								<select name="ColaLlamada" class="form-control" id="ColaLlamada" <?php if(($type_llmd==1)&&(!PermitirFuncion(302)||($row['IdEstadoLlamada']=='-1'))){ echo "disabled='disabled'";}?>>
										<option value="">Seleccione...</option>
								  <?php /*while($row_ColaLlamada=sqlsrv_fetch_array($SQL_ColaLlamada)){?>
										<option value="<?php echo $row_ColaLlamada['IdColaLlamada'];?>" <?php if((isset($row['IdColaLlamada']))&&(strcmp($row_ColaLlamada['IdColaLlamada'],$row['IdColaLlamada'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_ColaLlamada['DeColaLlamada'];?></option>
								  <?php }*/?>
								</select>
							</div>
							<label class="col-lg-1 control-label">Fecha de creación</label>
							<div class="col-lg-2 input-group date">
								 <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input name="FechaCreacion" type="text" required="required" class="form-control" id="FechaCreacion" value="<?php if(($type_llmd==1)&&($row['FechaCreacionLLamada'])!=""){echo $row['FechaCreacionLLamada'];}else{echo date('Y-m-d');}?>" readonly='readonly'>
							</div>
						</div>
						<div class="form-group">
							<label class="col-lg-1 control-label">Comentario</label>
							<div class="col-lg-8">
								<textarea name="ComentarioLlamada" rows="7" maxlength="2000" class="form-control" id="ComentarioLlamada" type="text" <?php if(($type_llmd==1)&&(!PermitirFuncion(302)||($row['IdEstadoLlamada']=='-1'))){ echo "readonly='readonly'";}?>><?php if(($type_llmd==1)||($sw_error==1)){echo $row['ComentarioLlamada'];}?></textarea>
							</div>
						</div>
					</div>
				</div>	
				<div class="ibox">
					<div class="ibox-title bg-success">
						<h5 class="collapse-link"><i class="fa fa-edit"></i> Información adicional</h5>
						 <a class="collapse-link pull-right">
							<i class="fa fa-chevron-up"></i>
						</a>	
					</div>
					<div class="ibox-content">
						<div class="form-group">
							<label class="col-lg-1 control-label">Nombre de contacto</label>
							<div class="col-lg-3">
								<input autocomplete="off" name="CDU_NombreContacto" type="text" class="form-control" id="CDU_NombreContacto" maxlength="100" <?php if(($type_llmd==1)&&(!PermitirFuncion(302)||($row['IdEstadoLlamada']=='-1'))){ echo "readonly='readonly'";}?> value="<?php if(($type_llmd==1)||($sw_error==1)){echo $row['CDU_NombreContacto'];}?>">
							</div>
							<label class="col-lg-1 control-label">Teléfono de contacto</label>
							<div class="col-lg-3">
								<input autocomplete="off" name="CDU_TelefonoContacto" type="text" class="form-control" id="CDU_TelefonoContacto" maxlength="100" <?php if(($type_llmd==1)&&(!PermitirFuncion(302)||($row['IdEstadoLlamada']=='-1'))){ echo "readonly='readonly'";}?> value="<?php if(($type_llmd==1)||($sw_error==1)){echo $row['CDU_TelefonoContacto'];}?>">
							</div>
							<label class="col-lg-1 control-label">Estado de servicio</label>
							<div class="col-lg-3">
								<select name="CDU_EstadoServicio" class="form-control" id="CDU_EstadoServicio" <?php if(($type_llmd==1)&&(!PermitirFuncion(302)||($row['IdEstadoLlamada']=='-1'))){ echo "disabled='disabled'";}?> required>
								  <?php while($row_EstServLlamada=sqlsrv_fetch_array($SQL_EstServLlamada)){?>
										<option value="<?php echo $row_EstServLlamada['IdEstadoServicio'];?>" <?php if((($type_llmd==0)&&($row_EstServLlamada['IdEstadoServicio']==0))||((isset($row['CDU_EstadoServicio']))&&(strcmp($row_EstServLlamada['IdEstadoServicio'],$row['CDU_EstadoServicio'])==0))){ echo "selected=\"selected\"";}?>><?php echo $row_EstServLlamada['DeEstadoServicio'];?></option>
								  <?php }?>
								</select>
							</div>
							
						</div>
						<div class="form-group">
							<label class="col-lg-1 control-label">Cargo de contacto</label>
							<div class="col-lg-3">
								<input autocomplete="off" name="CDU_CargoContacto" type="text" class="form-control" id="CDU_CargoContacto" maxlength="100" <?php if(($type_llmd==1)&&(!PermitirFuncion(302)||($row['IdEstadoLlamada']=='-1'))){ echo "readonly='readonly'";}?> value="<?php if(($type_llmd==1)||($sw_error==1)){echo $row['CDU_CargoContacto'];}?>">
							</div>
							<label class="col-lg-1 control-label">Correo de contacto</label>
							<div class="col-lg-3">
								<input autocomplete="off" name="CDU_CorreoContacto" type="text" class="form-control" id="CDU_CorreoContacto" maxlength="100" <?php if(($type_llmd==1)&&(!PermitirFuncion(302)||($row['IdEstadoLlamada']=='-1'))){ echo "readonly='readonly'";}?> value="<?php if(($type_llmd==1)||($sw_error==1)){echo $row['CDU_CorreoContacto'];}?>">
							</div>
							<label class="col-lg-1 control-label">Cancelado por</label>
							<div class="col-lg-3">
								<select name="CDU_CanceladoPor" class="form-control" id="CDU_CanceladoPor" <?php if(($type_llmd==1)&&(!PermitirFuncion(302)||($row['IdEstadoLlamada']=='-1'))){ echo "disabled='disabled'";}?> required>
								  <?php while($row_CanceladoPorLlamada=sqlsrv_fetch_array($SQL_CanceladoPorLlamada)){?>
										<option value="<?php echo $row_CanceladoPorLlamada['IdCanceladoPor'];?>" <?php if((isset($row['CDU_CanceladoPor']))&&(strcmp($row_CanceladoPorLlamada['IdCanceladoPor'],$row['CDU_CanceladoPor'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_CanceladoPorLlamada['DeCanceladoPor'];?></option>
								  <?php }?>
								</select>
							</div>
						</div>
						<div class="col-lg-6">
							<div class="form-group">
								<label class="col-lg-2 control-label">Reprogramación 1</label>
								<div class="col-md-4 input-group date">
									 <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input name="CDU_Reprogramacion1" placeholder="YYYY-MM-DD" type="text" class="form-control" id="CDU_Reprogramacion1" value="<?php if(($type_llmd==1)&&($row['CDU_Reprogramacion1'])!=""){echo $row['CDU_Reprogramacion1'];}?>" readonly='readonly'>
								</div>
								<label class="col-lg-2 control-label">Causa</label>
								<div class="col-md-4">
									<select name="CDU_CausaReprogramacion1" class="form-control" id="CDU_CausaReprogramacion1" <?php if(($type_llmd==1)&&(!PermitirFuncion(302)||($row['IdEstadoLlamada']=='-1'))){ echo "disabled='disabled'";}?>>
										<option value="">Seleccione...</option>
									  <?php while($row_CausaReprog=sqlsrv_fetch_array($SQL_CausaReprog)){?>
											<option value="<?php echo $row_CausaReprog['IdReprogramacion'];?>" <?php if((isset($row['CDU_CausaReprogramacion1']))&&(strcmp($row_CausaReprog['IdReprogramacion'],$row['CDU_CausaReprogramacion1'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_CausaReprog['DeReprogramacion'];?></option>
									  <?php }?>
									</select>
								</div>					
							</div>
							<div class="form-group">
								<label class="col-lg-2 control-label">Reprogramación 2</label>
								<div class="col-md-4 input-group date">
									 <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input name="CDU_Reprogramacion2" placeholder="YYYY-MM-DD" type="text" class="form-control" id="CDU_Reprogramacion2" value="<?php if(($type_llmd==1)&&($row['CDU_Reprogramacion2'])!=""){echo $row['CDU_Reprogramacion2'];}?>" readonly='readonly'>
								</div>
								<label class="col-lg-2 control-label">Causa</label>
								<div class="col-md-4">
								<?php $SQL_CausaReprog=Seleccionar('uvw_Sap_tbl_LlamadasServiciosReprogramacion','*','','DeReprogramacion'); ?>
									<select name="CDU_CausaReprogramacion2" class="form-control" id="CDU_CausaReprogramacion2" <?php if(($type_llmd==1)&&(!PermitirFuncion(302)||($row['IdEstadoLlamada']=='-1'))){ echo "disabled='disabled'";}?>>
										<option value="">Seleccione...</option>
									 <?php while($row_CausaReprog=sqlsrv_fetch_array($SQL_CausaReprog)){?>
											<option value="<?php echo $row_CausaReprog['IdReprogramacion'];?>" <?php if((isset($row['CDU_CausaReprogramacion2']))&&(strcmp($row_CausaReprog['IdReprogramacion'],$row['CDU_CausaReprogramacion2'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_CausaReprog['DeReprogramacion'];?></option>
									  <?php }?>
									</select>
								</div>	
							</div>
							<div class="form-group">
								<label class="col-lg-2 control-label">Reprogramación 3</label>
								<div class="col-md-4 input-group date">
									 <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input name="CDU_Reprogramacion3" placeholder="YYYY-MM-DD" type="text" class="form-control" id="CDU_Reprogramacion3" value="<?php if(($type_llmd==1)&&($row['CDU_Reprogramacion3'])!=""){echo $row['CDU_Reprogramacion3'];}?>" readonly='readonly'>
								</div>
								<label class="col-lg-2 control-label">Causa</label>
								<div class="col-md-4">
								<?php $SQL_CausaReprog=Seleccionar('uvw_Sap_tbl_LlamadasServiciosReprogramacion','*','','DeReprogramacion'); ?>
									<select name="CDU_CausaReprogramacion3" class="form-control" id="CDU_CausaReprogramacion3" <?php if(($type_llmd==1)&&(!PermitirFuncion(302)||($row['IdEstadoLlamada']=='-1'))){ echo "disabled='disabled'";}?>>
										<option value="">Seleccione...</option>
									  <?php while($row_CausaReprog=sqlsrv_fetch_array($SQL_CausaReprog)){?>
											<option value="<?php echo $row_CausaReprog['IdReprogramacion'];?>" <?php if((isset($row['CDU_CausaReprogramacion3']))&&(strcmp($row_CausaReprog['IdReprogramacion'],$row['CDU_CausaReprogramacion3'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_CausaReprog['DeReprogramacion'];?></option>
									  <?php }?>
									</select>
								</div>	
							</div>
						</div>
						<div class="col-lg-6">
							<div class="form-group">
								<label class="col-lg-1 control-label">Servicios</label>
								<div class="col-lg-11">
									<textarea name="CDU_Servicios" rows="5" class="form-control" id="CDU_Servicios" type="text" <?php if(($type_llmd==1)&&(!PermitirFuncion(302)||($row['IdEstadoLlamada']=='-1'))){ echo "readonly='readonly'";}?>><?php if(($type_llmd==1)||($sw_error==1)){echo $row['CDU_Servicios'];}?></textarea>
								</div>
							</div>
							<div class="form-group">
								<label class="col-lg-1 control-label">Áreas</label>
								<div class="col-lg-11">
									<textarea name="CDU_Areas" rows="5" class="form-control" id="CDU_Areas" type="text" <?php if(($type_llmd==1)&&(!PermitirFuncion(302)||($row['IdEstadoLlamada']=='-1'))){ echo "readonly='readonly'";}?>><?php if(($type_llmd==1)||($sw_error==1)){echo $row['CDU_Areas'];}?></textarea>
								</div>
							</div>
						</div>						
					</div>
				</div>	
				<div class="ibox">
					<div class="ibox-title bg-success">
						<h5 class="collapse-link"><i class="fa fa-check-circle"></i> Cierre de llamada</h5>
						 <a class="collapse-link pull-right">
							<i class="fa fa-chevron-up"></i>
						</a>	
					</div>
					<div class="ibox-content">
						<div class="form-group">
							<label class="col-lg-1 control-label">Resolución de llamada</label>
							<div class="col-lg-8">
								<textarea name="ResolucionLlamada" rows="5" maxlength="2000" type="text" required="required" class="form-control" id="ResolucionLlamada" <?php if(($type_llmd==1)&&(!PermitirFuncion(302)||($row['IdEstadoLlamada']=='-1'))){ echo "readonly='readonly'";}?>><?php if(($type_llmd==1)||($sw_error==1)){echo $row['ResolucionLlamada'];}?></textarea>
							</div>
						</div>
						<div class="form-group">
							<label class="col-lg-1 control-label">Fecha de cierre</label>
							<div class="col-lg-2 input-group date">
								 <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input name="FechaCierre" type="text" required="required" class="form-control" id="FechaCierre" value="<?php if(($type_llmd==1)&&($row['FechaCierreLLamada'])!=""){echo $row['FechaCierreLLamada'];}else{echo date('Y-m-d');}?>" readonly='readonly'>
							</div>
							<div class="col-lg-2 input-group clockpicker" data-autoclose="true">
								<input name="HoraCierre" id="HoraCierre" type="text" class="form-control" value="<?php if(($type_llmd==1)&&($row['FechaCierreLLamada'])!=""){echo $row['FechaHoraCierreLLamada']->format('H:i');}else{echo date('H:i');}?>" required="required" readonly='readonly'>
								<span class="input-group-addon">
									<span class="fa fa-clock-o"></span>
								</span>
							</div>
						</div>
					</div>
				</div>	
				<div class="ibox">
					<div class="ibox-title bg-success">
						<h5 class="collapse-link"><i class="fa fa-paperclip"></i> Anexos</h5>
						 <a class="collapse-link pull-right">
							<i class="fa fa-chevron-up"></i>
						</a>	
					</div>
					<div class="ibox-content">	
						<?php if($type_llmd==1){
								if($row['IdAnexoLlamada']!=0){?>
								<div class="form-group">
									<div class="col-xs-12">
										<?php while($row_AnexoLlamada=sqlsrv_fetch_array($SQL_AnexoLlamada)){
													$Icon=IconAttach($row_AnexoLlamada['FileExt']);?>
											<div class="file-box">
												<div class="file">
													<a href="attachdownload.php?file=<?php echo base64_encode($row_AnexoLlamada['AbsEntry']);?>&line=<?php echo base64_encode($row_AnexoLlamada['Line']);?>" target="_blank">
														<div class="icon">
															<i class="<?php echo $Icon;?>"></i>
														</div>
														<div class="file-name">
															<?php echo $row_AnexoLlamada['NombreArchivo'];?>
															<br/>
															<small><?php echo $row_AnexoLlamada['Fecha'];?></small>
														</div>
													</a>
												</div>
											</div>
										<?php }?>
									</div>
								</div>
						<?php }else{ echo "<p>Sin anexos.</p>"; }
							}?>
						<?php
						if(isset($_GET['return'])){
							$return=base64_decode($_GET['pag'])."?".$_GET['return'];
						}else{
							$return="gestionar_llamadas_servicios.php";
						}
				  		$return=QuitarParametrosURL($return,array("a"));?>
						<input type="hidden" id="P" name="P" value="<?php if(($type_llmd==0)&&($sw_error==0)){echo "32";}else{echo "33";}?>" />
						<input type="hidden" id="swTipo" name="swTipo" value="0" />
						<input type="hidden" id="swError" name="swError" value="<?php echo $sw_error;?>" />
						<input type="hidden" id="tl" name="tl" value="<?php echo $type_llmd;?>" />
						<input type="hidden" id="IdLlamadaPortal" name="IdLlamadaPortal" value="<?php if(($type_llmd==1)&&($sw_error==0)){echo base64_encode($row['IdLlamadaPortal']);}elseif(($type_llmd==1)&&($sw_error==1)){echo base64_encode($row['ID_LlamadaServicio']);}elseif(($type_llmd==0)&&($sw_error==1)){echo base64_encode($row['ID_LlamadaServicio']);}?>" />
						<input type="hidden" id="DocEntry" name="DocEntry" value="<?php if($type_llmd==1){echo base64_encode($row['ID_LlamadaServicio']);}?>" />
						<input type="hidden" id="DocNum" name="DocNum" value="<?php if($type_llmd==1){echo base64_encode($row['DocNum']);}?>" />
						<input type="hidden" id="ClienteLlamadaInterno" name="ClienteLlamadaInterno" value="<?php echo base64_encode(NIT_EMPRESA);?>" />
						<input type="hidden" id="SucursalClienteInterno" name="SucursalClienteInterno" value="<?php echo base64_encode(SUCURSAL_EMPRESA);?>" />
					  
					   </form>
						<?php if(($type_llmd==0)||(($type_llmd==1)&&($row['IdEstadoLlamada']!='-1'))){?> 
						<div class="row">
							<form action="upload.php" class="dropzone" id="dropzoneForm" name="dropzoneForm">
								<?php if($sw_error==0){LimpiarDirTemp();}?>
								<div class="fallback">
									<input name="File" id="File" type="file" form="dropzoneForm" />
								</div>
							 </form>
						</div>
						<?php }?>
					</div>
				</div>
				   <div class="form-group">
						<br>
						<?php if(($type_llmd==1)&&(PermitirFuncion(302)&&(($row['IdEstadoLlamada']=='-3')||($row['IdEstadoLlamada']=='-2')))){?> 
							<div class="col-lg-2">							
								<button class="btn btn-warning" type="submit" form="CrearLlamada" id="Actualizar" onClick="ValidarFrm();"><i class="fa fa-refresh"></i> Actualizar llamada</button>
							</div>
						<?php }?>
						<?php if(($type_llmd==1)&&(PermitirFuncion(302)&&($row['IdEstadoLlamada']=='-1'))){?> 
							<div class="col-lg-2">
								<button class="btn btn-success" type="submit" form="CrearLlamada" onClick="EnviarFrm('40');" id="Reabrir"><i class="fa fa-reply"></i> Reabrir</button>
							</div>
						<?php }?>
						<?php if($type_llmd==0){?> 
							<div class="col-lg-2">
								<button class="btn btn-primary" form="CrearLlamada" type="submit" id="Crear" onClick="ValidarFrm();"><i class="fa fa-check"></i> Crear llamada</button>  
							</div>
						<?php }?>
							<div class="col-lg-2">
								<a href="<?php echo $return;?>" class="alkin btn btn-outline btn-default"><i class="fa fa-arrow-circle-o-left"></i> Regresar</a>
							</div>
					</div>
			  		<br><br>
			   <?php if($type_llmd==1){?>
				<div class="ibox">
					<div class="ibox-title bg-success">
						<h5 class="collapse-link"><i class="fa fa-pencil-square-o"></i> Seguimiento de llamada</h5>
						 <a class="collapse-link pull-right">
							<i class="fa fa-chevron-up"></i>
						</a>	
					</div>
					<div class="ibox-content">
						<div class="tabs-container">
							<ul class="nav nav-tabs">
								<li class="active"><a data-toggle="tab" href="#tab-1"><i class="fa fa-calendar"></i> Actividades</a></li>
								<li><a data-toggle="tab" href="#tab-2"><i class="fa fa-tags"></i> Documentos relacionados</a></li>
								<li><a data-toggle="tab" href="#tab-3"><i class="fa fa-clipboard"></i> Formatos adicionales</a></li>
							</ul>
							<div class="tab-content">
							<div id="tab-1" class="tab-pane active">
								<div class="panel-body">
									<div class="row">
									<?php if(PermitirFuncion(302)&&(($row['IdEstadoLlamada']=='-3')||($row['IdEstadoLlamada']=='-2'))){?> 
									<button type="button" onClick="javascript:location.href='actividad.php?dt_LS=1&TTarea=<?php echo base64_encode($row['TipoTarea']);?>&Cardcode=<?php echo base64_encode($row['ID_CodigoCliente']);?>&Contacto=<?php echo base64_encode($row['IdContactoLLamada']);?>&Sucursal=<?php echo base64_encode($row['NombreSucursal']);?>&Direccion=<?php echo base64_encode($row['DireccionLlamada']);?>&Ciudad=<?php echo base64_encode($row['CiudadLlamada']);?>&Barrio=<?php echo base64_encode($row['BarrioDireccionLlamada']);?>&Telefono=<?php echo base64_encode($row['TelefonoContactoLlamada']);?>&Correo=<?php echo base64_encode($row['CorreoContactoLlamada']);?>&LS=<?php echo base64_encode($IdLlamada);?>&return=<?php echo base64_encode($_SERVER['QUERY_STRING']);?>&pag=<?php echo base64_encode('llamada_servicio.php');?>'" class="alkin btn btn-primary btn-xs"><i class="fa fa-plus-circle"></i> Agregar actividad</button>
									<?php }?>
									</div>
									<br>
									<div class="table-responsive">
										<table class="table table-striped table-bordered table-hover dataTables-example" >
											<thead>
											<tr>
												<th>Número</th>
												<th>Asignado por</th>
												<th>Asignado a</th>
												<th>Titulo</th>											
												<th>Fecha creación</th>
												<th>Fecha limite</th>
												<th>Dias venc.</th>
												<th>Estado</th>
												<th>Acciones</th>
											</tr>
											</thead>
											<tbody>
											<?php while($row_Actividad=sqlsrv_fetch_array($SQL_Actividad)){ 
														$DVenc=DiasTranscurridos(date('Y-m-d'),$row_Actividad['FechaFinActividad']);
												?>
												 <tr class="gradeX">
													<td><?php echo $row_Actividad['ID_Actividad'];?></td>
													<td><?php echo $row_Actividad['DeAsignadoPor'];?></td>
													<td><?php if($row_Actividad['NombreEmpleado']!=""){echo $row_Actividad['NombreEmpleado'];}else{echo "(Sin asignar)";}?></td>
													<td><?php echo $row_Actividad['TituloActividad'];?></td>
													<td><?php if($row_Actividad['FechaHoraInicioActividad']!=""){ echo $row_Actividad['FechaHoraInicioActividad']->format('Y-m-d H:s');}else{?><p class="text-muted">--</p><?php }?></td>
													<td><?php if($row_Actividad['FechaHoraFinActividad']!=""){ echo $row_Actividad['FechaHoraFinActividad']->format('Y-m-d H:s');}else{?><p class="text-muted">--</p><?php }?></td>
													<td><p class='<?php echo $DVenc[0];?>'><?php echo $DVenc[1];?></p></td>
													<td><span <?php if($row_Actividad['IdEstadoActividad']=='N'){echo "class='label label-info'";}else{echo "class='label label-danger'";}?>><?php echo $row_Actividad['DeEstadoActividad'];?></span></td>
													<td><a href="actividad.php?tl=1&id=<?php echo base64_encode($row_Actividad['ID_Actividad']);?>&return=<?php echo base64_encode($_SERVER['QUERY_STRING']);?>&pag=<?php echo base64_encode('llamada_servicio.php');?>" class="alkin btn btn-success btn-xs"><i class="fa fa-folder-open-o"></i> Abrir</a></td>
												</tr>
											<?php }?>
											</tbody>
										</table>
									</div>
								</div>
							</div>
							<div id="tab-2" class="tab-pane">
								<div class="panel-body">
									<div class="row">
									<?php if(PermitirFuncion(302)&&($row['IdEstadoLlamada']=='-3')){?>
									<button type="button" onClick="javascript:location.href='orden_venta.php?dt_LS=1&Cardcode=<?php echo base64_encode($row['ID_CodigoCliente']);?>&Contacto=<?php echo base64_encode($row['IdContactoLLamada']);?>&Sucursal=<?php echo base64_encode($row['NombreSucursal']);?>&Direccion=<?php echo base64_encode($row['DireccionLlamada']);?>&TipoLlamada=<?php echo base64_encode($row['IdTipoLlamada']);?>&ItemCode=<?php echo base64_encode($row['IdArticuloLlamada']);?>&LS=<?php echo base64_encode($IdLlamada);?>&return=<?php echo base64_encode($_SERVER['QUERY_STRING']);?>&pag=<?php echo base64_encode('llamada_servicio.php');?>'" class="alkin btn btn-primary btn-xs"><i class="fa fa-plus-circle"></i> Agregar Orden de venta con LMT</button>
									<button type="button" onClick="javascript:location.href='orden_venta.php?dt_LS=1&Cardcode=<?php echo base64_encode($row['ID_CodigoCliente']);?>&Contacto=<?php echo base64_encode($row['IdContactoLLamada']);?>&Sucursal=<?php echo base64_encode($row['NombreSucursal']);?>&Direccion=<?php echo base64_encode($row['DireccionLlamada']);?>&TipoLlamada=<?php echo base64_encode($row['IdTipoLlamada']);?>&ItemCode=<?php echo base64_encode($row['IdArticuloLlamada']);?>&LS=<?php echo base64_encode($IdLlamada);?>&return=<?php echo base64_encode($_SERVER['QUERY_STRING']);?>&pag=<?php echo base64_encode('llamada_servicio.php');?>&LMT=false'" class="alkin btn btn-success btn-xs"><i class="fa fa-plus-circle"></i> Agregar Orden de venta sin LMT</button>
									<?php }?>
									</div>
									<br>
									<div class="table-responsive">
										<table class="table table-striped table-bordered table-hover dataTables-example" >
											<thead>
											<tr>
												<th>Tipo de documento</th>
												<th>Número de documento</th>
												<th>Fecha de documento</th>
												<th>Autorización</th>
												<th>Estado de documento</th>
												<th>Creado por</th>
												<th>Acciones</th>
											</tr>
											</thead>
											<tbody>
											<?php while($row_DocRel=sqlsrv_fetch_array($SQL_DocRel)){ ?>
												 <tr class="gradeX">
													<td><?php echo $row_DocRel['DeObjeto'];?></td>
													<td><?php echo $row_DocRel['DocNum'];?></td>
													<td><?php echo $row_DocRel['DocDate'];?></td>
													<td><?php echo $row_DocRel['DeAuthPortal'];?></td>
													<td><span <?php if($row_DocRel['Cod_Estado']=='O'){echo "class='label label-info'";}else{echo "class='label label-danger'";}?>><?php echo $row_DocRel['NombreEstado'];?></span></td>
													<td><?php echo $row_DocRel['Usuario'];?></td>
													<td>
													<?php if($row_DocRel['Link']!=""){?>
														<a href="<?php echo $row_DocRel['Link'];?>.php?id=<?php echo base64_encode($row_DocRel['DocEntry']);?>&id_portal=<?php echo base64_encode($row_DocRel['IdPortal']);?>&tl=1&return=<?php echo base64_encode($_SERVER['QUERY_STRING']);?>&pag=<?php echo base64_encode('llamada_servicio.php');?>" class="btn btn-success btn-xs"><i class="fa fa-folder-open-o"></i> Abrir</a>
													<?php }?>
													<?php if($row_DocRel['Link']!=""&&$row_DocRel['Descargar']!=""){echo "-";}?>
													<?php if($row_DocRel['Descargar']!=""){?>
														<a href="sapdownload.php?id=<?php echo base64_encode('15');?>&type=<?php echo base64_encode('2');?>&DocKey=<?php echo base64_encode($row_DocRel['DocEntry']);?>&ObType=<?php echo base64_encode($row_DocRel['IdObjeto']);?>&IdFrm=<?php echo base64_encode($row_DocRel['IdFormato']);?>" target="_blank" class="btn btn-warning btn-xs"><i class="fa fa-download"></i> Descargar</a>
													<?php }?>
													</td>
												</tr>
											<?php }?>
											</tbody>
										</table>
									</div>
								</div>
							</div>
							<div id="tab-3" class="tab-pane">
								<div class="panel-body">
									<div class="row">
									<?php if(PermitirFuncion(302)&&($row['IdEstadoLlamada']=='-3')){?> 
									<button type="button" onClick="javascript:location.href='frm_hallazgos.php?frm=<?php echo base64_encode("136");?>&dt_LS=1&TTarea=<?php echo base64_encode($row['TipoTarea']);?>&Cardcode=<?php echo base64_encode($row['ID_CodigoCliente']);?>&Contacto=<?php echo base64_encode($row['IdContactoLLamada']);?>&Sucursal=<?php echo base64_encode($row['NombreSucursal']);?>&Direccion=<?php echo base64_encode($row['DireccionLlamada']);?>&Ciudad=<?php echo base64_encode($row['CiudadLlamada']);?>&Barrio=<?php echo base64_encode($row['BarrioDireccionLlamada']);?>&Telefono=<?php echo base64_encode($row['TelefonoContactoLlamada']);?>&Correo=<?php echo base64_encode($row['CorreoContactoLlamada']);?>&LS=<?php echo base64_encode($IdLlamada);?>&return=<?php echo base64_encode($_SERVER['QUERY_STRING']);?>&pag=<?php echo base64_encode('llamada_servicio.php');?>'" class="btn btn-primary btn-xs"><i class="fa fa-plus-circle"></i> Crear Panorama de riesgo</button>
									<?php }?>
									</div>
									<br>
									<div class="table-responsive">
										<table class="table table-striped table-bordered table-hover dataTables-example" >
											<thead>
											<tr>
												<th>Tipo de documento</th>
												<th>Número de documento</th>
												<th>Fecha de documento</th>
												<th>Estado de documento</th>
												<th>Creado por</th>
												<th>Acciones</th>
											</tr>
											</thead>
											<tbody>
											<?php while($row_FrmHallazgos=sqlsrv_fetch_array($SQL_FrmHallazgos)){ ?>
												 <tr class="gradeX">
													<td>Panorama de riesgo</td>
													<td><?php echo $row_FrmHallazgos['ID_Frm'];?></td>
													<td><?php echo $row_FrmHallazgos['FechaRegistro']->format('Y-m-d H:i');?></td>
													<td><?php echo $row_FrmHallazgos['NombreEstado'];?></td>
													<td><?php echo $row_FrmHallazgos['NombreEmpleado'];?></td>
													<td><a href="frm_hallazgos.php?id=<?php echo base64_encode($row_FrmHallazgos['ID_Frm']);?>&tl=1&return=<?php echo base64_encode($_SERVER['QUERY_STRING']);?>&pag=<?php echo base64_encode('llamada_servicio.php');?>&frm=<?php echo base64_encode("136");?>" class="alkin btn btn-success btn-xs"><i class="fa fa-folder-open-o"></i> Editar</a> - <a href="sapdownload.php?id=<?php echo base64_encode('13');?>&type=<?php echo base64_encode('2');?>&DocKey=<?php echo base64_encode($row_FrmHallazgos['ID_Frm']);?>" target="_blank" class="btn btn-warning btn-xs"><i class="fa fa-download"></i> Descargar</a></td>
												</tr>
											<?php }?>
											</tbody>
										</table>
									</div>
								</div>
							</div>
						</div>
						</div>	
					</div>
				</div>	
			   <?php }?>
		   </div>
			</div>
          </div>
        </div>
        <!-- InstanceEndEditable -->
        <?php include("includes/footer.php"); ?>

    </div>
</div>
<?php include("includes/pie.php"); ?>
<!-- InstanceBeginEditable name="EditRegion4" -->
<script>
	 $(document).ready(function(){
		 $("#CrearLlamada").validate({
			 submitHandler: function(form){
				$('.ibox-content').toggleClass('sk-loading',true);
					 if(ValidarCampos()){
						 form.submit();
					 }else{
						 $('.ibox-content').toggleClass('sk-loading',false);
					 }
				}
			});
		 $(".alkin").on('click', function(){
				 $('.ibox-content').toggleClass('sk-loading');
			});
			
		 maxLength('ComentarioLlamada');
		 maxLength('ResolucionLlamada');
		 
		$('#FechaCreacion').datepicker({
                todayBtn: "linked",
                keyboardNavigation: false,
                forceParse: false,
                calendarWeeks: true,
                autoclose: true,
				format: 'yyyy-mm-dd',
			 	todayHighlight: true,
			 	startDate: '<?php if($type_llmd==1){ echo $row['FechaCreacionLLamada'];}else{echo date('Y-m-d');}?>'
            });
		 <?php if(($type_llmd==1)&&(PermitirFuncion(302)&&(($row['IdEstadoLlamada']=='-3')||($row['IdEstadoLlamada']=='-2')))){?>
		 $('#FechaCierre').datepicker({
                todayBtn: "linked",
                keyboardNavigation: false,
                forceParse: false,
                calendarWeeks: true,
                autoclose: true,
				format: 'yyyy-mm-dd',
				todayHighlight: true,
			 	startDate: '<?php echo $row['FechaCreacionLLamada'];?>',
			 	endDate: '<?php echo date('Y-m-d');?>'
            });
		 $('.clockpicker').clockpicker();
		<?php  }?>
		$('#CDU_Reprogramacion1').datepicker({
                todayBtn: "linked",
                keyboardNavigation: false,
                forceParse: false,
                calendarWeeks: true,
                autoclose: true,
				format: 'yyyy-mm-dd',
				todayHighlight: true
            });
		$('#CDU_Reprogramacion2').datepicker({
                todayBtn: "linked",
                keyboardNavigation: false,
                forceParse: false,
                calendarWeeks: true,
                autoclose: true,
				format: 'yyyy-mm-dd',
				todayHighlight: true
            });
		$('#CDU_Reprogramacion3').datepicker({
                todayBtn: "linked",
                keyboardNavigation: false,
                forceParse: false,
                calendarWeeks: true,
                autoclose: true,
				format: 'yyyy-mm-dd',
				todayHighlight: true
            });
		 $(".select2").select2();
		 var options = {
			url: function(phrase) {
				return "ajx_buscar_datos_json.php?type=7&id="+phrase;
			},

			getValue: "NombreBuscarCliente",
			requestDelay: 400,
			list: {
				match: {
					enabled: true
				},
				onClickEvent: function() {
					var value = $("#NombreClienteLlamada").getSelectedItemData().CodigoCliente;
					$("#ClienteLlamada").val(value).trigger("change");
				}
			}
		};
		 var options2 = {
			url: function(phrase) {
				return "ajx_buscar_datos_json.php?type=8&id="+phrase;
			},

			getValue: "Ciudad",
			requestDelay: 400,
			template: {
				type: "description",
				fields: {
					description: "Codigo"
				}
			},
			list: {
				match: {
					enabled: true
				}
			}
		};

		<?php 
		if($type_llmd==0){?>
		$("#NombreClienteLlamada").easyAutocomplete(options);
		<?php } ?>
		$("#CiudadLlamada").easyAutocomplete(options2);
		<?php if($dt_LS==1){?>
		$('#ClienteLlamada').trigger('change'); 
	 	<?php }?>
		
		<?php 
		if($type_llmd==1){?>
			$('#Series option:not(:selected)').attr('disabled',true);	 
		<?php } ?>
		 
		$('.dataTables-example').DataTable({
                pageLength: 10,
                dom: '<"html5buttons"B>lTfgitp',
				language: {
					"decimal":        "",
					"emptyTable":     "No se encontraron resultados.",
					"info":           "Mostrando _START_ - _END_ de _TOTAL_ registros",
					"infoEmpty":      "Mostrando 0 - 0 de 0 registros",
					"infoFiltered":   "(filtrando de _MAX_ registros)",
					"infoPostFix":    "",
					"thousands":      ",",
					"lengthMenu":     "Mostrar _MENU_ registros",
					"loadingRecords": "Cargando...",
					"processing":     "Procesando...",
					"search":         "Filtrar:",
					"zeroRecords":    "Ningún registro encontrado",
					"paginate": {
						"first":      "Primero",
						"last":       "Último",
						"next":       "Siguiente",
						"previous":   "Anterior"
					},
					"aria": {
						"sortAscending":  ": Activar para ordenar la columna ascendente",
						"sortDescending": ": Activar para ordenar la columna descendente"
					}
				},
                buttons: []

            });		 
	});

function ValidarFrm(){
	var EstLlamada=document.getElementById('EstadoLlamada');
	var txtResol=document.getElementById('ResolucionLlamada');
	//var EstServ=document.getElementById('CDU_EstadoServicio');
	if(EstLlamada.value=='-1'){
		txtResol.setAttribute("required","required");
		//EstServ.setAttribute("required","required");
	}else{
		txtResol.removeAttribute("required");
		//EstServ.removeAttribute("required");
	}
}
	
function EnviarFrm(P=33){
	var vP=document.getElementById('P');
	vP.value=P;
}
</script>
<script>
 Dropzone.options.dropzoneForm = {
		paramName: "File", // The name that will be used to transfer the file
		maxFilesize: "<?php echo ObtenerVariable("MaxSizeFile");?>", // MB
	 	maxFiles: "<?php echo ObtenerVariable("CantidadArchivos");?>",
		uploadMultiple: true,
		addRemoveLinks: true,
		dictRemoveFile: "Quitar",
	 	acceptedFiles: "<?php echo ObtenerVariable("TiposArchivos");?>",
		dictDefaultMessage: "<strong>Haga clic aqui para cargar anexos</strong><br>Tambien puede arrastrarlos hasta aqui<br><h4><small>(máximo <?php echo ObtenerVariable("CantidadArchivos");?> archivos a la vez)<small></h4>",
		dictFallbackMessage: "Tu navegador no soporta cargue de archivos mediante arrastrar y soltar",
	 	removedfile: function(file) {
		  $.get( "includes/procedimientos.php", {
			type: "3",
		  	nombre: file.name
		  }).done(function( data ) {
		 	var _ref;
		  	return (_ref = file.previewElement) !== null ? _ref.parentNode.removeChild(file.previewElement) : void 0;
		 	});
		 }
	};
</script>
<!-- InstanceEndEditable -->
</body>

<!-- InstanceEnd --></html>
<?php sqlsrv_close($conexion);?>