<?php require_once("includes/conexion.php");
//require_once("includes/conexion_hn.php");
if(PermitirAcceso(1101)||PermitirAcceso(1102))

$msg_error="";//Mensaje del error
$sw_ext=0;//Sw que permite saber si la ventana esta abierta en modo pop-up. Si es así, no cargo el menú ni el menú superior.

if(isset($_GET['id'])&&($_GET['id']!="")){
	$IdOp=base64_decode($_GET['id']);
}
	
if(isset($_GET['ext'])&&($_GET['ext']==1)){
	$sw_ext=1;//Se está abriendo como pop-up
}

if(isset($_POST['swError'])&&($_POST['swError']!="")){//Para saber si ha ocurrido un error.
	$sw_error=$_POST['swError'];
}else{
	$sw_error=0;
}

if(isset($_GET['tl'])&&($_GET['tl']!="")){//0 Si se está creando. 1 Se se está editando.
	$edit=$_GET['tl'];
}elseif(isset($_POST['tl'])&&($_POST['tl']!="")){
	$edit=$_POST['tl'];
}else{
	$edit=0;
}

if(isset($_POST['P'])&&($_POST['P']!="")){
	try{
		
		#Comprobar si el cliente ya esta guardado en la tabla de SN. Si no está guardado se ejecuta el INSERT con el Metodo de actualizar
		//$SQL_Dir=Seleccionar('tbl_SociosNegocios','CardCode',"CardCode='".$_POST['CardCode']."'");
		//$row_Dir=sqlsrv_fetch_array($SQL_Dir);
		
		$Metodo=2;//Actualizar en el web services
		$Type=2;//Ejecutar actualizar en el SP
		
		if($_POST['edit']==0){//Creando SN
			$Metodo=1;
		}
		
		if($_POST['ID_SN']==""){//Insertando en la tabla
			$Type=1;
		}
		
		$ParamSN=array(
			"'".$_POST['CardCode']."'",
			"'".$_POST['CardName']."'",
			"'".$_POST['PNNombres']."'",
			"'".$_POST['PNApellido1']."'",
			"'".$_POST['PNApellido2']."'",
			"'".$_POST['AliasName']."'",
			"'".$_POST['CardType']."'",
			"'".$_POST['TipoEntidad']."'",
			"'".$_POST['TipoDocumento']."'",
			"'".$_POST['LicTradNum']."'",
			"'".$_POST['GroupCode']."'",
			"'".$_POST['RegimenTributario']."'",
			"'".$_POST['ID_MunicipioMM']."'",
			"'".$_POST['GroupNum']."'",
			"'".$_POST['Industria']."'",
			"'".$_POST['Territorio']."'",
			$Metodo,
			"'".$_SESSION['CodUser']."'",
			$Type
		);
		$SQL_SN=EjecutarSP('sp_tbl_SociosNegocios',$ParamSN,$_POST['P']);
		if($SQL_SN){			
			if(base64_decode($_POST['ID_SN'])==""){
				$row_NewIdSN=sqlsrv_fetch_array($SQL_SN);
				$IdSN=$row_NewIdSN[0];
			}else{
				$IdSN=base64_decode($_POST['ID_SN']);
			}			
			
			//Insertar Contactos
			$Count=count($_POST['NombreContacto']);
			$i=0;
			$Delete="Delete From tbl_SociosNegocios_Contactos Where CodigoCliente='".$_POST['CardCode']."'";
			if(sqlsrv_query($conexion,$Delete)){
				while($i<$Count){
					if($_POST['NombreContacto'][$i]!=""){
						//Insertar el registro en la BD
						$ParamInsConct=array(
							"'".$IdSN."'",
							"'".$_POST['CardCode']."'",
							"'".$_POST['CodigoContacto'][$i]."'",
							"'".$_POST['NombreContacto'][$i]."'",
							"'".$_POST['SegundoNombre'][$i]."'",
							"'".$_POST['Apellidos'][$i]."'",
							"'".$_POST['Telefono'][$i]."'",
							"'".$_POST['TelefonoCelular'][$i]."'",
							"'".$_POST['Posicion'][$i]."'",
							"'".$_POST['Email'][$i]."'",
							"'".$_POST['ActEconomica'][$i]."'",
							"'".$_POST['CedulaContacto'][$i]."'",
							"'".$_POST['RepLegal'][$i]."'",
							"'".$_POST['MetodoCtc'][$i]."'",
							"1"
						);
						
						$SQL_InsConct=EjecutarSP('sp_tbl_SociosNegocios_Contactos',$ParamInsConct,$_POST['P']);

						if(!$SQL_InsConct){
							$sw_error=1;
							$msg_error="Ha ocurrido un error al insertar los contactos";
						}
					}
					$i=$i+1;
				}
				//sqlsrv_close($conexion);
				//header('Location:socios_negocios_add.php?a='.base64_encode("OK_SNAdd"));
			}else{
				InsertarLog(1, 45, $Delete);
				$sw_error=1;
				$msg_error="Ha ocurrido un error al eliminar los contactos";
			}
			//Insertar direcciones
			$Count=count($_POST['Address']);
			$i=0;
			$Delete="Delete From tbl_SociosNegocios_Direcciones Where CardCode='".$_POST['CardCode']."'";
			if(sqlsrv_query($conexion,$Delete)){
				while($i<$Count){
					if($_POST['Address'][$i]!=""){
						//Insertar el registro en la BD
						$ParamInsDir=array(
							"'".$IdSN."'",
							"'".$_POST['Address'][$i]."'",
							"'".$_POST['CardCode']."'",
							"'".$_POST['Street'][$i]."'",
							"'".$_POST['Block'][$i]."'",
							"'".$_POST['City'][$i]."'",
							"'".$_POST['County'][$i]."'",
							"'".$_POST['AdresType'][$i]."'",
							"'".$_POST['LineNum'][$i]."'",
							"'".$_POST['Metodo'][$i]."'",
							"1"
						);
						
						$SQL_InsDir=EjecutarSP('sp_tbl_SociosNegocios_Direcciones',$ParamInsDir,$_POST['P']);

						if(!$SQL_InsDir){
							$sw_error=1;
							$msg_error="Ha ocurrido un error al insertar las direcciones";
						}
					}
					$i=$i+1;
				}
				
				//Enviar datos al WebServices
				try{
					require_once("includes/conect_ws.php");
					$Parametros=array(
						'pIdCliente' => $IdSN,
						'pLogin'=>$_SESSION['User']
					);
					$Client->AppPortal_InsertarClientePortal($Parametros);
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
					}else{
						if($_POST['edit']==0){//Mensaje para devuelta
							$Msg=base64_encode("OK_SNAdd");
						}else{
							$Msg=base64_encode("OK_SNEdit");
						}
						
						sqlsrv_close($conexion);						
						if($_POST['ext']==0){//Validar a donde debe ir la respuesta
							header('Location:socios_negocios.php?id='.base64_encode($_POST['CardCode']).'&ext='.$_POST['ext'].'&pag='.$_POST['pag'].'&return='.$_POST['return'].'&a='.$Msg.'&tl=1');
						}else{
							header('Location:socios_negocios.php?id='.base64_encode($_POST['CardCode']).'&ext='.$_POST['ext'].'&a='.$Msg.'&tl=1');
						}
					}
				}catch (Exception $e) {
					echo 'Excepcion capturada: ',  $e->getMessage(), "\n";
				}				
			}else{
				InsertarLog(1, 45, $Delete);
				$sw_error=1;
				$msg_error="Ha ocurrido un error al eliminar las direcciones";
			}
		}else{
			$sw_error=1;
			$msg_error="Ha ocurrido un error al crear el Socio de Negocio";
		}
	}catch (Exception $e){
		echo 'Excepcion capturada: ',  $e->getMessage(), "\n";
	}
	
}

if($edit==1){

	//Cliente
	$SQL=Seleccionar("uvw_Sap_tbl_Clientes","*","[CodigoCliente]='".$IdSN."'");
	$row=sql_fetch_array($SQL);
	
	//Direcciones
	$SQL_Dir=Seleccionar("uvw_Sap_tbl_Clientes_Sucursales","*","[CodigoCliente]='".$row['CodigoCliente']."'");
	$Num_Dir=sql_num_rows($SQL_Dir);
	
	//Contactos
	$SQL_Cont=Seleccionar("uvw_Sap_tbl_ClienteContactos","*","[CodigoCliente]='".$row['CodigoCliente']."'");
	$Num_Cont=sql_num_rows($SQL_Cont);
	
	//Municipio MM
	$SQL_MunMM=Seleccionar('uvw_tbl_Municipios','*',"Codigo='".$row['U_HBT_MunMed']."'");
	$row_MunMM=sql_fetch_array($SQL_MunMM);
	
	//Facturas pendientes
	$SQL_FactPend=Seleccionar('uvw_Sap_tbl_FacturasPendientes','TOP 10 *',"ID_CodigoCliente='".$row['CodigoCliente']."'","FechaContabilizacion","DESC");
		
	//ID de servicios
	$SQL_IDServicio=Seleccionar('uvw_Sap_tbl_Articulos','*',"[CodigoCliente]='".$row['CodigoCliente']."'",'[ItemCode]');
		
	//Historico de gestiones
	$SQL_HistGestion=Seleccionar('uvw_tbl_Cartera_Gestion','TOP 10 *',"CardCode='".$row['CodigoCliente']."'",'FechaRegistro');
}

if($sw_error==1){	

	//Cliente
	$SQL=Seleccionar("uvw_tbl_SociosNegocios","*","[CardCode]='".$IdSN."'");
	$row=sql_fetch_array($SQL);
	
	//Direcciones
	$SQL_Dir=Seleccionar("uvw_tbl_SociosNegocios_Direcciones","*","[CodigoCliente]='".$row['CodigoCliente']."'");
	$Num_Dir=sql_num_rows($SQL_Dir);
	
	//Contactos
	$SQL_Cont=Seleccionar("uvw_tbl_SociosNegocios_Contactos","*","[CodigoCliente]='".$row['CodigoCliente']."'");
	$Num_Cont=sql_num_rows($SQL_Cont);
	
	//Municipio MM
	$SQL_MunMM=Seleccionar('uvw_tbl_Municipios','*',"Codigo='".$row['U_HBT_MunMed']."'");
	$row_MunMM=sql_fetch_array($SQL_MunMM);
	
	//Facturas pendientes
	$SQL_FactPend=Seleccionar('uvw_Sap_tbl_FacturasPendientes','TOP 10 *',"ID_CodigoCliente='".$row['CodigoCliente']."'","FechaContabilizacion","DESC");
		
	//ID de servicios
	$SQL_IDServicio=Seleccionar('uvw_Sap_tbl_Articulos','*',"[CodigoCliente]='".$row['CodigoCliente']."'",'[ItemCode]');
		
	//Historico de gestiones
	$SQL_HistGestion=Seleccionar('uvw_tbl_Cartera_Gestion','TOP 10 *',"CardCode='".$row['CodigoCliente']."'",'FechaRegistro');
}

//Estado documento
$SQL_EstadoOpr=Seleccionar('uvw_tbl_EstadoOportunidad','*');

//Industrias
$SQL_Industria=Seleccionar('uvw_Sap_tbl_Clientes_Industrias','*','','DeIndustria');

//Territorio
$SQL_Territorio=Seleccionar('uvw_Sap_tbl_Territorios','*','','DeTerritorio');

//Nivel de interes
$SQL_NivelInteres=Seleccionar('uvw_Sap_tbl_OportunidadesNivelTipoInteres','*');

//Proyectos
$SQL_Proyecto=Seleccionar('uvw_Sap_tbl_Proyectos','*','','DeProyecto');

//Fuente de información
$SQL_FuenteInfo=Seleccionar('uvw_Sap_tbl_OportunidadesFuenteInformacion','*','','DE_FuenteInfo');

//Ramo industria
$SQL_RamoInd=Seleccionar('uvw_Sap_tbl_OportunidadesRamoIndustria','*','','DescripcionRamoIndustria');

//Etapa
$SQL_Etapa=Seleccionar('uvw_Sap_tbl_OportunidadesEtapas','*','','ID_Etapa');

//Tipos de documentos de marketing
$SQL_TipoDocMark=Seleccionar('tbl_ObjetosSAP','*');

//Empleados de ventas
$SQL_EmpVentas=Seleccionar('uvw_Sap_tbl_EmpleadosVentas','*');

?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php echo NOMBRE_PORTAL;?> | Oportunidad</title>
<?php 
if(isset($_GET['a'])&&($_GET['a']==base64_encode("OK_SNAdd"))){
	echo "<script>
		$(document).ready(function() {
			swal({
                title: '¡Listo!',
                text: 'El Socio de Negocio ha sido creado exitosamente.',
                type: 'success'
            });
		});		
		</script>";
}
if(isset($_GET['a'])&&($_GET['a']==base64_encode("OK_SNEdit"))){
	echo "<script>
		$(document).ready(function() {
			swal({
                title: '¡Listo!',
                text: 'El Socio de Negocio ha sido actualizado exitosamente.',
                type: 'success'
            });
		});		
		</script>";
}
if(isset($_GET['a'])&&($_GET['a']==base64_encode("OK_ArtUpd"))){
	echo "<script>
		$(document).ready(function() {
			swal({
                title: '¡Listo!',
                text: 'El ID de servicio ha sido actualizado exitosamente.',
                type: 'success'
            });
		});		
		</script>";
}
if(isset($sw_error)&&($sw_error==1)){
	echo "<script>
		$(document).ready(function() {
			swal({
                title: '¡Ha ocurrido un error!',
                text: '".$msg_error."',
                type: 'error'
            });
		});		
		</script>";
}
?>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<style>
	.select2-container{ width: 100% !important; }
</style>
<script type="text/javascript">
	$(document).ready(function() {//Cargar los combos dependiendo de otros
		$("#CardCode").change(function(){
			var carcode=document.getElementById('CardCode').value;
			$.ajax({
				type: "POST",
				url: "ajx_cbo_select.php?type=7&id="+carcode,
				success: function(response){
					$('#CondicionPago').html(response).fadeIn();
				}
			});
		});
		
		
	});
</script>
<!-- InstanceEndEditable -->
</head>

<body <?php if($sw_ext==1){echo "class='mini-navbar'"; }?>>

<div id="wrapper">

    <?php if($sw_ext!=1){include("includes/menu.php"); }?>

    <div id="page-wrapper" class="gray-bg">
        <?php if($sw_ext!=1){include("includes/menu_superior.php"); }?>
        <!-- InstanceBeginEditable name="Contenido" -->
        <div class="row wrapper border-bottom white-bg page-heading">
                <div class="col-sm-8">
                    <h2>Oportunidad</h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="index1.php">Inicio</a>
                        </li>
                        <li>
                            <a href="#">Oportunidades</a>
                        </li>
                        <li class="active">
                            <strong>Oportunidad</strong>
                        </li>
                    </ol>
                </div>
            </div>
           
         <div class="wrapper wrapper-content">
			 <form action="socios_negocios.php" method="post" class="form-horizontal" enctype="multipart/form-data" id="FrmOp">
			 <div class="row">
				<div class="col-lg-12">   		
					<div class="ibox-content">
						<?php include("includes/spinner.php"); ?>
						<div class="form-group">
							<div class="col-lg-8">
								<?php 
								if($edit==1){
									if(PermitirFuncion(503)||(PermitirFuncion(504)&&($row['CardType']=="L"))){?>
										<button class="btn btn-warning" type="submit" id="Actualizar"><i class="fa fa-refresh"></i> Actualizar oportunidad</button>
								<?php }
								}else{
									if(PermitirFuncion(501)){?>
										<button class="btn btn-primary" type="submit" id="Crear"><i class="fa fa-check"></i> Crear oportunidad</button>
								<?php }
								}?>
								<?php 
									$EliminaMsg=array("&a=".base64_encode("OK_SNAdd"),"&a=".base64_encode("OK_SNEdit"));
									if(isset($_GET['return'])){
										$_GET['return']=str_replace($EliminaMsg,"",base64_decode($_GET['return']));
									}
									if(isset($_GET['return'])){
										$return=base64_decode($_GET['pag'])."?".$_GET['return'];
									}elseif(isset($_POST['return'])){
										$return=base64_decode($_POST['return']);
									}else{
										$return="socios_negocios.php?";
									}
								if($sw_ext==0){?>
									<a href="<?php echo $return;?>" class="alkin btn btn-outline btn-default"><i class="fa fa-arrow-circle-o-left"></i> Regresar</a>
								<?php }?>
							</div>
							<?php if(($edit==1)&&(PermitirFuncion(302))){?>
							<div class="col-lg-4">
									<a href="llamada_servicio.php?dt_LS=1&Cardcode=<?php echo base64_encode($row['CodigoCliente']);?>" target="_blank" class="pull-right btn btn-primary"><i class="fa fa-plus-circle"></i> Crear llamada de servicio</a>
							</div>
							<?php }?>
						</div>
						<input type="hidden" id="P" name="P" value="<?php if($edit==1){echo "45";}else{echo "38";}?>" />
						<input type="hidden" id="ID_SN" name="ID_SN" value="<?php if(isset($row['IdSNPortal'])){echo base64_encode($row['IdSNPortal']); }?>" />
						<input type="hidden" id="edit" name="edit" value="<?php echo $edit;?>" />
						<input type="hidden" id="ext" name="ext" value="<?php echo $sw_ext;?>" />
						<?php if($sw_ext==0){?>
						<input type="hidden" id="pag" name="pag" value="<?php if(isset($_GET['pag'])){echo $_GET['pag'];}?>" />
						<input type="hidden" id="return" name="return" value="<?php if(isset($_GET['return'])){echo base64_encode($_GET['return']);}?>" />
						<?php }?>
					</div>
				</div>
			 </div>
			 <br>
			 <div class="row">
			 	<div class="col-lg-12">   		
					<div class="ibox-content">
						<?php include("includes/spinner.php"); ?>
						<div class="form-group">
							<label class="col-md-12 col-xs-12"><h3 class="bg-muted p-xs b-r-sm"><i class="fa fa-user"></i> Información de cliente</h3></label>
						</div>
						<div class="form-group">
							<label class="col-lg-1 control-label">Cliente</label>
							<div class="col-lg-4">
								<input name="CardCode" type="hidden" id="CardCode" value="<?php if(($edit==1)||($sw_error==1)){echo $row['CardCode'];}?>">

								<input name="CardName" type="text" required="required" class="form-control" id="CardName" placeholder="Digite para buscar..." value="<?php if(($edit==1)||($sw_error==1)){echo $row['NombreCliente'];}?>" <?php if(($edit==1)&&($row['Cod_Estado']=='C')){echo "readonly";}?>>
							</div>
							<label class="col-lg-1 control-label">Contacto</label>
							<div class="col-lg-3">
								<select name="ContactoCliente" class="form-control m-b" id="ContactoCliente" required <?php if(($edit==1)&&($row['Cod_Estado']=='C')){echo "disabled='disabled'";}?>>
										<option value="">Seleccione...</option>
								<?php
									if($edit==1||$sw_error==1){
										while($row_ContactoCliente=sqlsrv_fetch_array($SQL_ContactoCliente)){?>
											<option value="<?php echo $row_ContactoCliente['CodigoContacto'];?>" <?php if((isset($row['CodigoContacto']))&&(strcmp($row_ContactoCliente['CodigoContacto'],$row['CodigoContacto'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_ContactoCliente['ID_Contacto'];?></option>
								<?php 	}
									}?>
								</select>
							</div>
						</div>
						<div class="form-group">
							<label class="col-lg-1 control-label">Importe de factura total</label>
							<div class="col-lg-2">
								<input name="ImporteFact" type="text" class="form-control" id="ImporteFact" value="" readonly="readonly">
							</div>
							<label class="col-lg-1 control-label">Propietario</label>
							<div class="col-lg-3">
								<input name="Propietario" type="text" class="form-control" id="Propietario" value="" readonly="readonly">
							</div>
						</div>
						<div class="form-group">
							<label class="col-lg-1 control-label">Industria</label>
							<div class="col-lg-2">
								<select name="Industria" class="form-control m-b" id="Industria" required>
									<option value="">Seleccione...</option>
								<?php
									while($row_Industria=sqlsrv_fetch_array($SQL_Industria)){?>
										<option value="<?php echo $row_Industria['IdIndustria'];?>" <?php if((isset($row['IdIndustria']))&&(strcmp($row_Industria['IdIndustria'],$row['IdIndustria'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Industria['DeIndustria'];?></option>
								<?php }?>
								</select>
							</div>
							<label class="col-lg-1 control-label">Territorio</label>
							<div class="col-lg-3">
								<select name="Territorio" class="form-control m-b" id="Territorio" required>
									<option value="">Seleccione...</option>
								<?php
									while($row_Territorio=sqlsrv_fetch_array($SQL_Territorio)){?>
										<option value="<?php echo $row_Territorio['IdTerritorio'];?>" <?php if((isset($row['IdTerritorio']))&&(strcmp($row_Territorio['IdTerritorio'],$row['IdTerritorio'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Territorio['DeTerritorio'];?></option>
								<?php }?>
								</select>
							</div>
						</div>
						<div class="form-group">
							<label class="col-md-12 col-xs-12"><h3 class="bg-muted p-xs b-r-sm"><i class="fa fa-suitcase"></i> Información de la oportunidad</h3></label>
						</div>
						<div class="form-group">
							<label class="col-lg-1 control-label">Nombre de la oportunidad</label>
							<div class="col-lg-5">
								<input autocomplete="off" name="NombreOportunidad" type="text" required="required" class="form-control" id="NombreOportunidad" maxlength="150" value="<?php if($edit==1||$sw_error==1){echo $row['TituloActividad'];}?>" <?php if(($edit==1)&&(!PermitirFuncion(304)||($row['IdEstadoActividad']=='Y')||($row['IdAsignadoPor']!=$_SESSION['CodUser']))){ echo "readonly='readonly'";}?>>
							</div>
							<label class="col-lg-1 control-label">Número de oportunidad</label>
							<div class="col-lg-2">
								<input autocomplete="off" name="NumeroOportunidad" type="text" class="form-control" id="NumeroOportunidad" maxlength="10" value="">
							</div>
							<label class="col-lg-1 control-label">Tipo de oportunidad</label>
							<div class="col-lg-2">
								<select name="TipoOpr" class="form-control m-b" id="TipoOpr" <?php if(($edit==1)&&($row['Cod_Estado']=='C')){echo "disabled='disabled'";}?>>
									<option value="R">Ventas</option>
									<option value="P">Compras</option>
								</select>
							</div>
						</div>
						<div class="form-group">
							<label class="col-lg-1 control-label">Estado</label>
							<div class="col-lg-2">
								<select name="EstadoOpr" class="form-control m-b" id="EstadoOpr" <?php if(($edit==1)&&($row['Cod_Estado']=='C')){echo "disabled='disabled'";}?>>
								  <?php while($row_EstadoOpr=sqlsrv_fetch_array($SQL_EstadoOpr)){?>
										<option value="<?php echo $row_EstadoOpr['Cod_Estado'];?>" <?php if(($edit==1)&&(isset($row['Cod_Estado']))&&(strcmp($row_EstadoOpr['Cod_Estado'],$row['Cod_Estado'])==0)){ echo "selected=\"selected\"";}elseif(($edit==0)&&($row_EstadoOpr['Cod_Estado']=='O')){echo "selected=\"selected\"";}?>><?php echo $row_EstadoOpr['NombreEstado'];?></option>
								  <?php }?>
								</select>
							</div>
							<label class="col-lg-1 control-label">Fecha de inicio</label>
							<div class="col-lg-2 input-group date">
								 <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input name="FechaInicio" id="FechaInicio" type="text" required="required" class="form-control" value="<?php if($edit==1||$sw_error==1){echo $row['DocDate'];}else{echo date('Y-m-d');}?>" <?php if(($edit==1)&&($row['Cod_Estado']=='C')){echo "readonly";}?>>
							</div>
							<label class="col-lg-1 control-label">Fecha de cierre</label>
							<div class="col-lg-2 input-group date">
								 <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input name="FechaCierre" id="FechaCierre" type="text" required="required" class="form-control" value="<?php if($edit==1||$sw_error==1){echo $row['DocDate'];}else{echo date('Y-m-d');}?>" <?php if(($edit==1)&&($row['Cod_Estado']=='C')){echo "readonly";}?>>
							</div>
						</div>
						<div class="form-group">
							<label class="col-lg-1 control-label">% de cierre</label>
							<div class="col-lg-5">
								<div class="progress">
									<div class="progress-bar progress-bar-success" role="progressbar" style="width: 25%;" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100">25%</div>
								</div>
							</div>
						</div>
						 <div class="tabs-container">
								<ul class="nav nav-tabs">
									<li class="active"><a data-toggle="tab" href="#tabOpr-1"><i class="fa fa-shopping-cart"></i> Potencial</a></li>
									<li><a data-toggle="tab" href="#tabOpr-2"><i class="fa fa-book"></i> General</a></li>
									<li><a data-toggle="tab" href="#tabOpr-3"><i class="fa fa-flag-checkered"></i> Etapas</a></li>
									<li><a data-toggle="tab" href="#tabOpr-4"><i class="fa fa-group"></i> Socios de negocios</a></li>
									<li><a data-toggle="tab" href="#tabOpr-5"><i class="fa fa-trophy"></i> Competidores</a></li>
									<li><a data-toggle="tab" href="#tabOpr-6"><i class="fa fa-paperclip"></i> Anexos</a></li>
								</ul>
							   <div class="tab-content">
								   <div id="tabOpr-1" class="tab-pane active">
									   <br>
										<div class="form-group">
											<label class="col-lg-1 control-label">Cierre planificado en</label>
											<div class="col-lg-1">
												<input autocomplete="off" name="CierrePlan" type="text" required="required" class="form-control" id="CierrePlan" maxlength="10" value="">
											</div>
											<div class="col-lg-1">
												<select name="CierreTipo" class="form-control m-b" id="CierreTipo" <?php if(($edit==1)&&($row['Cod_Estado']=='C')){echo "disabled='disabled'";}?>>
													<option value="M">Meses</option>
													<option value="W">Semanas</option>
													<option value="D">Días</option>
												</select>
											</div>
										</div>
										<div class="form-group">
											<label class="col-lg-1 control-label">Fecha de cierre prevista</label>
											<div class="col-lg-2 input-group date">
												 <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input name="FechaCierrePrev" id="FechaCierrePrev" type="text" required="required" class="form-control" value="<?php if($edit==1||$sw_error==1){echo $row['DocDate'];}else{echo date('Y-m-d');}?>" <?php if(($edit==1)&&($row['Cod_Estado']=='C')){echo "readonly";}?>>
											</div>
										</div>
									  	<div class="form-group">
											<label class="col-lg-1 control-label">Monto potencial ($)</label>
											<div class="col-lg-2">
												<input name="MontoPotencial" type="text" class="form-control" id="MontoPotencial" value="">
											</div>
										</div>
									   	<div class="form-group col-lg-3">
											<label class="control-label">Monto ponderado ($)</label>
											<input name="MontoPonderado" type="text" class="form-control" id="MontoPonderado" value="">
										</div>
									   	<div class="form-group">
											<label class="col-lg-1 control-label">% de ganancia bruta</label>
											<div class="col-lg-2">
												<input name="PrcGananciaBruta" type="text" class="form-control" id="PrcGananciaBruta" value="">
											</div>
										</div>
									   	<div class="form-group">
											<label class="col-lg-1 control-label">Ganancia bruta total ($)</label>
											<div class="col-lg-2">
												<input name="GananciaBruta" type="text" class="form-control" id="GananciaBruta" value="">
											</div>
										</div>
									   	<div class="form-group">
											<label class="col-lg-1 control-label">Nivel de interés</label>
											<div class="col-lg-2">
												<select name="NivelInteres" class="form-control m-b" id="NivelInteres" <?php if(($edit==1)&&($row['Cod_Estado']=='C')){echo "disabled='disabled'";}?> required="required">
													<option value="">Seleccione...</option>
													<?php while($row_NivelInteres=sqlsrv_fetch_array($SQL_NivelInteres)){?>
														<option value="<?php echo $row_NivelInteres['ID_NivelInteres'];?>" <?php if(($edit==1)&&(isset($row['ID_NivelInteres']))&&(strcmp($row_NivelInteres['ID_NivelInteres'],$row['ID_NivelInteres'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_NivelInteres['DE_NivelInteres'];?></option>
											  		<?php }?>
												</select>												
											</div>
										</div>
								   </div>
								   <div id="tabOpr-2" class="tab-pane">
										<br>
										<div class="form-group">
											<label class="col-lg-1 control-label">Canal SN</label>
											<div class="col-lg-4">
												<input name="CanalSN" type="hidden" id="CanalSN" value="<?php if(($edit==1)||($sw_error==1)){echo $row['CardCode'];}?>">

												<input name="NombreCanalSN" type="text" required="required" class="form-control" id="NombreCanalSN" placeholder="Digite para buscar..." value="<?php if(($edit==1)||($sw_error==1)){echo $row['NombreCliente'];}?>" <?php if(($edit==1)&&($row['Cod_Estado']=='C')){echo "readonly";}?>>
											</div>
											<label class="col-lg-1 control-label">Contacto</label>
											<div class="col-lg-3">
												<select name="ContactoCanalSN" class="form-control m-b" id="ContactoCanalSN" required <?php if(($edit==1)&&($row['Cod_Estado']=='C')){echo "disabled='disabled'";}?>>
														<option value="">Seleccione...</option>
												<?php
													if($edit==1||$sw_error==1){
														while($row_ContactoCliente=sqlsrv_fetch_array($SQL_ContactoCliente)){?>
															<option value="<?php echo $row_ContactoCliente['CodigoContacto'];?>" <?php if((isset($row['CodigoContacto']))&&(strcmp($row_ContactoCliente['CodigoContacto'],$row['CodigoContacto'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_ContactoCliente['ID_Contacto'];?></option>
												<?php 	}
													}?>
												</select>
											</div>
										</div>
									   	<div class="form-group">
											<label class="col-lg-1 control-label">Proyecto</label>
											<div class="col-lg-3">
												<select name="Proyecto" class="form-control m-b select2" id="Proyecto" required>
													<option value="">Seleccione...</option>
												<?php
													while($row_Proyecto=sqlsrv_fetch_array($SQL_Proyecto)){?>
														<option value="<?php echo $row_Proyecto['IdProyecto'];?>" <?php if((isset($row['IdProyecto']))&&(strcmp($row_Proyecto['IdIndustria'],$row['IdProyecto'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Proyecto['DeProyecto'];?></option>
												<?php }?>
												</select>
											</div>
											<label class="col-lg-1 control-label">Fuente de información</label>
											<div class="col-lg-3">
												<select name="FuenteInfo" class="form-control m-b select2" id="FuenteInfo" required>
													<option value="">Seleccione...</option>
												<?php
													while($row_FuenteInfo=sqlsrv_fetch_array($SQL_FuenteInfo)){?>
														<option value="<?php echo $row_FuenteInfo['ID_FuenteInfo'];?>" <?php if((isset($row['ID_FuenteInfo']))&&(strcmp($row_FuenteInfo['ID_FuenteInfo'],$row['ID_FuenteInfo'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_FuenteInfo['DE_FuenteInfo'];?></option>
												<?php }?>
												</select>
											</div>
											<label class="col-lg-1 control-label">Ramo</label>
											<div class="col-lg-3">
												<select name="RamoInd" class="form-control m-b select2" id="RamoInd" required>
													<option value="">Seleccione...</option>
												<?php
													while($row_RamoInd=sqlsrv_fetch_array($SQL_RamoInd)){?>
														<option value="<?php echo $row_RamoInd['ID_RamoIndustria'];?>" <?php if((isset($row['ID_RamoIndustria']))&&(strcmp($row_RamoInd['ID_RamoIndustria'],$row['ID_RamoIndustria'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_RamoInd['DescripcionRamoIndustria'];?></option>
												<?php }?>
												</select>
											</div>
										</div>
									   	<div class="form-group">
											<label class="col-lg-1 control-label">Comentarios</label>
											<div class="col-lg-6">
												<textarea name="Comentarios" rows="7" maxlength="1000" class="form-control" id="Comentarios" type="text"></textarea>
											</div>											
										</div>
								   </div>
								   <div id="tabOpr-3" class="tab-pane">
										<br>
										<div id="div_<?php echo $Cont;?>" class="bg-muted p-sm">
											 <div class="form-group">
												<label class="col-lg-1 control-label">Etapa</label>
												<div class="col-lg-3">
													<select name="Etapa" class="form-control m-b" id="Etapa" required>
														<option value="">Seleccione...</option>
													<?php
														while($row_Etapa=sqlsrv_fetch_array($SQL_Etapa)){?>
															<option value="<?php echo $row_Etapa['ID_Etapa'];?>" <?php if((isset($row['ID_Etapa']))&&(strcmp($row_Etapa['ID_Etapa'],$row['ID_Etapa'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Etapa['DE_Etapa'];?></option>
													<?php }?>
													</select>
												</div>
												<label class="col-lg-1 control-label">%</label>
												<div class="col-lg-1">
													<input autocomplete="off" name="PrcEtapa" type="text" class="form-control" id="PrcEtapa" maxlength="10" value="">
												</div>
												<label class="col-lg-1 control-label">Fecha de inicio</label>
												<div class="col-lg-2 input-group date">
													 <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input name="FechaInicioEtapa" id="FechaInicioEtapa" type="text" required="required" class="form-control" value="<?php if($edit==1||$sw_error==1){echo $row['DocDate'];}else{echo date('Y-m-d');}?>" <?php if(($edit==1)&&($row['Cod_Estado']=='C')){echo "readonly";}?>>
												</div>
												<label class="col-lg-1 control-label">Fecha de cierre</label>
												<div class="col-lg-2 input-group date">
													 <span class="input-group-addon"><i class="fa fa-calendar"></i></span><input name="FechaCierreEtapa" id="FechaCierreEtapa" type="text" required="required" class="form-control" value="<?php if($edit==1||$sw_error==1){echo $row['DocDate'];}else{echo date('Y-m-d');}?>" <?php if(($edit==1)&&($row['Cod_Estado']=='C')){echo "readonly";}?>>
												</div>
											</div>
											<div class="form-group">
												<label class="col-lg-1 control-label">Monto potencial</label>
												<div class="col-lg-2">
													<input autocomplete="off" name="MontoPotencialEtapa" type="text" class="form-control" id="MontoPotencialEtapa" maxlength="10" value="">
												</div>
												<label class="col-lg-1 control-label">Importe ponderado</label>
												<div class="col-lg-2">
													<input autocomplete="off" name="ImportePonderadoEtapa" type="text" class="form-control" id="ImportePonderadoEtapa" maxlength="10" value="">
												</div>
											</div>
											<div class="form-group">
												<label class="col-lg-1 control-label">Documento asociado</label>
												<div class="col-lg-2">
													<select name="TipoDocEtapa" class="form-control m-b" id="TipoDocEtapa">
														<option value="">(Ninguno)</option>
														<?php while($row_TipoDocMark=sqlsrv_fetch_array($SQL_TipoDocMark)){?>
															<option value="<?php echo $row_TipoDocMark['ID_Objeto'];?>"><?php echo $row_TipoDocMark['DE_Objeto'];?></option>
													  <?php }?>
													</select>
												</div>
												<label class="col-lg-1 control-label">Número de documento</label>
												<div class="col-lg-2">
													<input autocomplete="off" name="NumDocEtapa" type="text" class="form-control" id="NumDocEtapa" maxlength="10" value="">
												</div>
												<label class="col-lg-1 control-label">Comentarios</label>
												<div class="col-lg-4">
													<textarea name="ComentariosEtapa" rows="3" maxlength="1000" class="form-control" id="ComentariosEtapa" type="text"></textarea>
												</div>
											</div>
											<div class="form-group">
												<label class="col-lg-1 control-label">Empleado de ventas</label>
												<div class="col-lg-3">
													<select name="EmpVentasEtapa" class="form-control m-b" id="EmpVentasEtapa">
														<option value="">(Ninguno)</option>
														<?php while($row_EmpVentas=sqlsrv_fetch_array($SQL_EmpVentas)){?>
															<option value="<?php echo $row_EmpVentas['ID_EmpVentas'];?>"><?php echo $row_EmpVentas['DE_EmpVentas'];?></option>
													  <?php }?>
													</select>
												</div>
											</div>
											<button type="button" id="<?php echo $Cont;?>" class="btn btn-warning btn-xs btn_del"><i class="fa fa-minus"></i> Remover</button><br>
										</div>
								   </div>
								   <div id="tabOpr-4" class="tab-pane">
										<br>
										
								   </div>
								   <div id="tabOpr-5" class="tab-pane">
										<br>
										
								   </div>
								   <div id="tabOpr-6" class="tab-pane">
										<br>
										
								   </div>
							   </div>
						   </div>
					</div>
          		</div>
			 </div>
			</form>
        </div>
        <!-- InstanceEndEditable -->
        <?php include("includes/footer.php"); ?>

    </div>
</div>
<?php include("includes/pie.php"); ?>
<!-- InstanceBeginEditable name="EditRegion4" -->
<script>
 $(document).ready(function(){
	 $("#FrmOp").validate({
		 submitHandler: function(form){
			 $('.ibox-content').toggleClass('sk-loading');
			 form.submit();
		}
	});
	 $(".alkin").on('click', function(){
		 $('.ibox-content').toggleClass('sk-loading');
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
					  var value = $("#CardName").getSelectedItemData().CodigoCliente;
					  $("#CardCode").val(value).trigger("change");
				  }
			  }
		 };
	$("#CardName").easyAutocomplete(options);
	 
	 $('#FechaInicio').datepicker({
			todayBtn: "linked",
			keyboardNavigation: false,
			forceParse: false,
			calendarWeeks: true,
			autoclose: true,
			todayHighlight: true,
			format: 'yyyy-mm-dd'
		});
	 $('#FechaCierre').datepicker({
			todayBtn: "linked",
			keyboardNavigation: false,
			forceParse: false,
			calendarWeeks: true,
			autoclose: true,
			todayHighlight: true,
			format: 'yyyy-mm-dd'
		});
	 $('#FechaCierrePrev').datepicker({
			todayBtn: "linked",
			keyboardNavigation: false,
			forceParse: false,
			calendarWeeks: true,
			autoclose: true,
			todayHighlight: true,
			format: 'yyyy-mm-dd'
		});
	 
	$('.dataTables-example').DataTable({
                pageLength: 10,
                dom: '<"html5buttons"B>lTfgitp',
				order: [[ 0, "desc" ]],
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
</script>
<!-- InstanceEndEditable -->
</body>

<!-- InstanceEnd --></html>
<?php sqlsrv_close($conexion);?>