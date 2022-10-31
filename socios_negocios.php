<?php require_once("includes/conexion.php");
//require_once("includes/conexion_hn.php");
if(PermitirAcceso(502)||PermitirAcceso(503))

$msg_error="";//Mensaje del error
$sw_ext=0;//Sw que permite saber si la ventana esta abierta en modo pop-up. Si es así, no cargo el menú ni el menú superior.

if(isset($_GET['id'])&&($_GET['id']!="")){
	$IdSN=base64_decode($_GET['id']);
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

if($edit==0){
	$Title="Crear socios de negocios";
}else{
	$Title="Editar socios de negocios";
}

$Num_Dir=0;
$Num_Cont=0;

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
	//$SQL_IDServicio=Seleccionar('uvw_Sap_tbl_Articulos','*',"[CodigoCliente]='".$row['CodigoCliente']."'",'[ItemCode]');
		
	//Historico de gestiones
	//$SQL_HistGestion=Seleccionar('uvw_tbl_Cartera_Gestion','TOP 10 *',"CardCode='".$row['CodigoCliente']."'",'FechaRegistro');
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
	//$SQL_IDServicio=Seleccionar('uvw_Sap_tbl_Articulos','*',"[CodigoCliente]='".$row['CodigoCliente']."'",'[ItemCode]');
		
	//Historico de gestiones
	//$SQL_HistGestion=Seleccionar('uvw_tbl_Cartera_Gestion','TOP 10 *',"CardCode='".$row['CodigoCliente']."'",'FechaRegistro');
}

//Condiciones de pago
$SQL_CondicionPago=Seleccionar('uvw_Sap_tbl_CondicionPago','*','','NombreCondicion');

//Tipos de SN
$SQL_TipoSN=Seleccionar('uvw_tbl_TiposSN','*');

//Regimen tributario
$SQL_RegimenT=Seleccionar('tbl_RegimenTributarioSN','*','','RegimenTributario');

//Tipo documento
$SQL_TipoDoc=Seleccionar('tbl_TipoDocumentoSN','*','','TipoDocumento');

//Tipo entidad
$SQL_TipoEntidad=Seleccionar('tbl_TipoEntidadSN','*','','NombreEntidad');

//Grupos de Clientes
$SQL_GruposClientes=Seleccionar('uvw_Sap_tbl_GruposClientes','*','','GroupName');

//Industrias
$SQL_Industria=Seleccionar('uvw_Sap_tbl_Clientes_Industrias','*','','DeIndustria');

//Territorio
$SQL_Territorio=Seleccionar('uvw_Sap_tbl_Territorios','*','','DeTerritorio');

//Departamentos
$SQL_Dptos=Seleccionar('uvw_tbl_Municipios','Distinct Departamento','','Departamento');

?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php echo $Title;?> | <?php echo NOMBRE_PORTAL;?></title>
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
	.panel-body{
		padding: 0px !important;
	}
	.tabs-container .panel-body{
		padding: 0px !important;
	}
	.nav-tabs > li > a{
		padding: 14px 20px 14px 25px !important;
	}
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
		$("#TipoEntidad").change(function(){
			var TipoEntidad=document.getElementById('TipoEntidad').value;
			var Nombres=document.getElementById('PNNombres');
			var Apellido1=document.getElementById('PNApellido1');
			var Apellido2=document.getElementById('PNApellido2');
			if(TipoEntidad==1){
				Nombres.removeAttribute("readonly");
				Apellido1.removeAttribute("readonly");
				Apellido2.removeAttribute("readonly");
			}else{
				Nombres.value="";
				Apellido1.value="";
				Apellido2.value="";
				Nombres.setAttribute("readonly","readonly");
				Apellido1.setAttribute("readonly","readonly");
				Apellido2.setAttribute("readonly","readonly");
			}
		});
		//NomDir('1');
		$('#TipoEntidad').trigger('change');
	});
</script>
<script>
function SeleccionarFactura(Num, Obj, Frm){
	var div=document.getElementById("dwnAllFact");
	var FactSel=document.getElementById("FactSel");
	var Fac=FactSel.value.indexOf(Num);
	var Link=document.getElementById("LinkAllFact");
	
	if(Fac<0){
		FactSel.value=FactSel.value + Num + "[*]";
	}else{
		var tmp=FactSel.value.replace(Num+"[*]","");
		FactSel.value=tmp;
	}
	
	if(FactSel.value==""){
		div.style.display='none';
	}else{
		div.style.display='';
		Link.setAttribute('href',"sapdownload.php?id=<?php echo base64_encode('15');?>&type=<?php echo base64_encode('2');?>&zip=<?php echo base64_encode('1');?>&ObType="+Obj+"&IdFrm="+Frm+"&DocKey="+FactSel.value);
	}
}
</script>
<?php /*?><script>
function NomDir(id){
	var tipodir=document.getElementById("AdresType"+id);
	var nombredir=document.getElementById("Address"+id);
	
	if(tipodir.value=="B"){
		nombredir.value="<?php echo ObtenerVariable("DirFacturacion");?>";
	}else if(tipodir.value=="S"){
		nombredir.value="<?php echo ObtenerVariable("DirDestino");?>";
	}
}
</script><?php */?>
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
                    <h2><?php echo $Title;?></h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="index1.php">Inicio</a>
                        </li>
                        <li>
                            <a href="#">Socios de negocios</a>
                        </li>
                        <li class="active">
                            <strong><?php echo $Title;?></strong>
                        </li>
                    </ol>
                </div>
            </div>
           
         <div class="wrapper wrapper-content">
			 <form action="socios_negocios.php" method="post" class="form-horizontal" enctype="multipart/form-data" id="EditarSN">
			 <div class="row">
				<div class="col-lg-12">   		
					<div class="ibox-content">
						<?php include("includes/spinner.php"); ?>
						<div class="form-group">
							<div class="col-lg-8">
								<?php 
								if($edit==1){
									if(PermitirFuncion(503)||(PermitirFuncion(504)&&($row['CardType']=="L"))){?>
										<button class="btn btn-warning" type="submit" id="Actualizar"><i class="fa fa-refresh"></i> Actualizar Socio de negocio</button>
								<?php }
								}else{
									if(PermitirFuncion(501)){?>
										<button class="btn btn-primary" type="submit" id="Crear"><i class="fa fa-check"></i> Crear Socio de negocio</button>
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
						 <div class="tabs-container">
								<ul class="nav nav-tabs">
									<li class="active"><a data-toggle="tab" href="#tabSN-1"><i class="fa fa-info-circle"></i> Información general</a></li>
									<li><a data-toggle="tab" href="#tabSN-2"><i class="fa fa-user-circle"></i> Contactos</a></li>
									<li><a data-toggle="tab" href="#tabSN-3"><i class="fa fa-home"></i> Direcciones</a></li>
									<?php if($edit==1){?><li><a data-toggle="tab" href="#tabSN-4"><i class="fa fa-folder-open"></i> Documentos relacionados</a></li><?php } ?>
								</ul>
							   <div class="tab-content">
								   <div id="tabSN-1" class="tab-pane active">
									   <br>
										<div class="form-group">
											<label class="col-lg-1 control-label">Código</label>
											<div class="col-lg-2">
												<input name="CardCode" autofocus="autofocus" type="text" required class="form-control" id="CardCode" value="<?php if($edit==1){echo $row['CodigoCliente'];}?>" <?php if($edit==1){ echo "readonly='readonly'";} ?>>
											</div>
											<div class="col-lg-2">
												<select name="CardType" class="form-control m-b" id="CardType" required>
												<?php
													while($row_TipoSN=sqlsrv_fetch_array($SQL_TipoSN)){?>
														<option value="<?php echo $row_TipoSN['CardType'];?>" <?php if((isset($row['CardType']))&&(strcmp($row_TipoSN['CardType'],$row['CardType'])==0)){ echo "selected=\"selected\"";}elseif(PermitirFuncion(504)&&($row_TipoSN['CardType']=="L")){echo "selected=\"selected\"";}?>><?php echo $row_TipoSN['DE_CardType'];?></option>
												<?php }?>
												</select>
											</div>
										</div>
										<div class="form-group">
											<label class="col-lg-1 control-label">Nombre</label>
											<div class="col-lg-4">
												<input type="text" class="form-control" name="CardName" id="CardName" required value="<?php if($edit==1){ echo utf8_encode($row['NombreCliente']);}?>">
											</div>
											<?php if($edit==1){?>
											<label class="col-lg-1 control-label">Estado servicio</label>
											<div class="col-lg-3">
												<input type="text" readonly class="form-control" name="EstadoServicio" id="EstadoServicio" value="<?php if($edit==1){echo $row['DeEstadoServicioCliente'];}?>">
											</div>
											<?php }?>
										</div>
										<div class="form-group">
											<label class="col-lg-1 control-label">Nombre comercial</label>
											<div class="col-lg-6">
												<input type="text" class="form-control" name="AliasName" id="AliasName" required value="<?php if($edit==1){echo utf8_encode($row['AliasCliente']);}?>">
											</div>
										</div>
										<div class="form-group">
											<label class="col-lg-1 control-label">Grupo</label>
											<div class="col-lg-4">
												<select name="GroupCode" class="form-control m-b select2" id="GroupCode" required>
													<option value="">Seleccione...</option>
												<?php
													while($row_GruposClientes=sqlsrv_fetch_array($SQL_GruposClientes)){?>
														<option value="<?php echo $row_GruposClientes['GroupCode'];?>" <?php if((isset($row['GrupoCliente']))&&(strcmp($row_GruposClientes['GroupCode'],$row['GrupoCliente'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_GruposClientes['GroupName'];?></option>
												<?php }?>
												</select>
											</div>
											<label class="col-lg-1 control-label">Condición de pago</label>
											<div class="col-lg-3">
												<select name="GroupNum" class="form-control m-b" id="GroupNum" required>
													<option value="">Seleccione...</option>
												<?php
													while($row_CondicionPago=sqlsrv_fetch_array($SQL_CondicionPago)){?>
														<option value="<?php echo $row_CondicionPago['IdCondicionPago'];?>" <?php if((isset($row['GroupNum']))&&(strcmp($row_CondicionPago['IdCondicionPago'],$row['GroupNum'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_CondicionPago['NombreCondicion'];?></option>
												<?php }?>
												</select>
											</div>
										</div>
										<div class="form-group">
											<label class="col-lg-1 control-label">NIT</label>
											<div class="col-lg-2">
												<input type="text" class="form-control" name="LicTradNum" id="LicTradNum" required value="<?php if($edit==1){echo $row['LicTradNum'];}?>">
											</div>
											<label class="col-lg-1 control-label">Industria</label>
											<div class="col-lg-2">
												<select name="Industria" class="form-control m-b" id="Industria" required>
												<?php
													while($row_Industria=sqlsrv_fetch_array($SQL_Industria)){?>
														<option value="<?php echo $row_Industria['IdIndustria'];?>" <?php if((isset($row['IdIndustria']))&&(strcmp($row_Industria['IdIndustria'],$row['IdIndustria'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Industria['DeIndustria'];?></option>
												<?php }?>
												</select>
											</div>
											<label class="col-lg-1 control-label">Territorio</label>
											<div class="col-lg-3">
												<select name="Territorio" class="form-control m-b select2" id="Territorio" required>
												<?php
													while($row_Territorio=sqlsrv_fetch_array($SQL_Territorio)){?>
														<option value="<?php echo $row_Territorio['IdTerritorio'];?>" <?php if((isset($row['IdTerritorio']))&&(strcmp($row_Territorio['IdTerritorio'],$row['IdTerritorio'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Territorio['DeTerritorio'];?></option>
												<?php }?>
												</select>
											</div>
										</div>
										<div class="form-group">
											<label class="col-xs-12"><h3 class="bg-muted p-xs b-r-sm"><i class="fa fa-bank"></i> Información tributaria</h3></label>
										</div>
										<div class="form-group">
											<label class="col-lg-1 control-label">Régimen tributario</label>
											<div class="col-lg-3">
												<select name="RegimenTributario" class="form-control m-b" id="RegimenTributario" required>
													<option value="">Seleccione...</option>
												<?php
													while($row_RegimenT=sqlsrv_fetch_array($SQL_RegimenT)){?>
														<option value="<?php echo $row_RegimenT['ID_RegimenTributario'];?>" <?php if((isset($row['U_HBT_RegTrib']))&&(strcmp($row_RegimenT['ID_RegimenTributario'],$row['U_HBT_RegTrib'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_RegimenT['RegimenTributario'];?></option>
												<?php }?>
												</select>
											</div>
											<label class="col-lg-1 control-label">Tipo documento</label>
											<div class="col-lg-3">
												<select name="TipoDocumento" class="form-control m-b" id="TipoDocumento" required>
													<option value="">Seleccione...</option>
												<?php
													while($row_TipoDoc=sqlsrv_fetch_array($SQL_TipoDoc)){?>
														<option value="<?php echo $row_TipoDoc['ID_TipoDocumento'];?>" <?php if((isset($row['U_HBT_TipDoc']))&&(strcmp($row_TipoDoc['ID_TipoDocumento'],$row['U_HBT_TipDoc'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_TipoDoc['TipoDocumento'];?></option>
												<?php }?>
												</select>
											</div>
											<label class="col-lg-1 control-label">Municipio MM</label>
											<div class="col-lg-3">
												<input name="ID_MunicipioMM" type="hidden" id="ID_MunicipioMM" value="<?php if($edit==1){echo $row_MunMM['Codigo'];}?>">
												<input name="MunicipioMM" type="text" class="form-control" id="MunicipioMM" placeholder="Digite para buscar..." value="<?php if($edit==1){echo $row_MunMM['Ciudad'];}?>">
											</div>
										</div>
										<div class="form-group">
											<label class="col-lg-1 control-label">Tipo entidad</label>
											<div class="col-lg-3">
												<select name="TipoEntidad" class="form-control m-b" id="TipoEntidad" required>
													<option value="">Seleccione...</option>
												<?php
													while($row_TipoEntidad=sqlsrv_fetch_array($SQL_TipoEntidad)){?>
														<option value="<?php echo $row_TipoEntidad['ID_TipoEntidad'];?>" <?php if((isset($row['U_HBT_TipEnt']))&&(strcmp($row_TipoEntidad['ID_TipoEntidad'],$row['U_HBT_TipEnt'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_TipoEntidad['NombreEntidad'];?></option>
												<?php }?>
												</select>
											</div>
										</div>
										<div class="form-group">
											<label class="col-lg-1 control-label">Nombres</label>
											<div class="col-lg-3">
												<input name="PNNombres" type="text" class="form-control" id="PNNombres" readonly="readonly" value="<?php if($edit==1){echo $row['U_HBT_Nombres'];}?>">
											</div>	
											<label class="col-lg-1 control-label">Primer apellido</label>
											<div class="col-lg-3">
												<input name="PNApellido1" type="text" class="form-control" id="PNApellido1" readonly="readonly" value="<?php if($edit==1){echo $row['U_HBT_Apellido1'];}?>">
											</div>
											<label class="col-lg-1 control-label">Segundo apellido</label>
											<div class="col-lg-3">
												<input name="PNApellido2" type="text" class="form-control" id="PNApellido2" readonly="readonly" value="<?php if($edit==1){echo $row['U_HBT_Apellido2'];}?>">
											</div>
										</div>
									   <?php if($edit==1){?>
										<div class="form-group">
											<label class="col-xs-12"><h3 class="bg-muted p-xs b-r-sm"><i class="fa fa-credit-card"></i> Datos de finanzas</h3></label>
										</div>
										<div class="form-group">
											<label class="col-lg-2 control-label">Saldo de cuenta</label>
											<div class="col-lg-2">
												<input name="Balance" type="text" class="form-control" id="Balance" value="<?php echo number_format($row['Balance'],2);?>" readonly="readonly">
											</div>
											<label class="col-lg-2 control-label">Limite de crédito</label>
											<div class="col-lg-2">
												<input name="LimiteCredito" type="text" class="form-control" id="LimiteCredito" value="<?php echo number_format($row['Balance'],2);?>" readonly="readonly">
											</div>
											<label class="col-lg-2 control-label">Crédito consumido</label>
											<div class="col-lg-2">
												<input name="CreditoConsumido" type="text" class="form-control" id="CreditoConsumido" value="<?php echo number_format($row['Balance'],2);?>" readonly="readonly">
											</div>
										</div>
									   <?php }?>
								   </div>
								   <div id="tabSN-2" class="tab-pane">
										<br>
											<?php $Cont=1;
											if($edit==1&&$Num_Cont>0){
												$row_Cont=sql_fetch_array($SQL_Cont);
												do{ ?>
											<div id="divCtc_<?php echo $Cont;?>"> 
											<div class="form-group">
												<label class="col-lg-1 control-label">Nombre</label>
												<div class="col-lg-3">
													<input type="text" onChange="CambiarMetodoCtc('<?php echo $Cont;?>');" class="form-control" name="NombreContacto[]" id="NombreContacto<?php echo $Cont;?>" value="<?php if($row_Cont['NombreContacto']!=""){ echo $row_Cont['NombreContacto'];}else{echo $row_Cont['ID_Contacto'];}?>" required>
												</div>
												<label class="col-lg-1 control-label">Segundo nombre</label>
												<div class="col-lg-3">
													<input type="text" onChange="CambiarMetodoCtc('<?php echo $Cont;?>');" class="form-control" name="SegundoNombre[]" id="SegundoNombre<?php echo $Cont;?>" value="<?php echo $row_Cont['SegundoNombre'];?>">
												</div>
												<label class="col-lg-1 control-label">Apellidos</label>
												<div class="col-lg-3">
													<input type="text" onChange="CambiarMetodoCtc('<?php echo $Cont;?>');" class="form-control" name="Apellidos[]" id="Apellidos<?php echo $Cont;?>" value="<?php echo $row_Cont['Apellidos'];?>" required>
												</div>
											</div>
											<div class="form-group">
												<label class="col-lg-1 control-label">Cédula</label>
												<div class="col-lg-3">
													<input type="text" onChange="CambiarMetodoCtc('<?php echo $Cont;?>');" class="form-control" name="CedulaContacto[]" id="CedulaContacto<?php echo $Cont;?>" value="<?php echo $row_Cont['CedulaContacto'];?>">
												</div>
												<label class="col-lg-1 control-label">Teléfono</label>
												<div class="col-lg-3">
													<input type="text" maxlength="20" onChange="CambiarMetodoCtc('<?php echo $Cont;?>');" class="form-control" name="Telefono[]" id="Telefono<?php echo $Cont;?>" value="<?php echo $row_Cont['Telefono1'];?>" required>
												</div>
												<label class="col-lg-1 control-label">Celular</label>
												<div class="col-lg-3">
													<input type="text" maxlength="50" onChange="CambiarMetodoCtc('<?php echo $Cont;?>');" class="form-control" name="TelefonoCelular[]" id="TelefonoCelular<?php echo $Cont;?>" value="<?php echo $row_Cont['TelefonoCelular'];?>">
												</div>
											</div>
											<div class="form-group">
												<label class="col-lg-1 control-label">Actividad económica</label>
												<div class="col-lg-3">
													<select name="ActEconomica[]" onChange="CambiarMetodoCtc('<?php echo $Cont;?>');" class="form-control m-b" id="ActEconomica<?php echo $Cont;?>" required>
														<option value="">Seleccione...</option>
														<option value="EMPLEADO" <?php if($row_Cont['ActEconomica']=='EMPLEADO'){echo "selected=\"selected\"";} ?>>EMPLEADO</option>
														<option value="INDEPENDIENTE" <?php if($row_Cont['ActEconomica']=='INDEPENDIENTE'){echo "selected=\"selected\"";} ?>>INDEPENDIENTE</option>
														<option value="OTRO" <?php if($row_Cont['ActEconomica']=='OTRO'){echo "selected=\"selected\"";} ?>>OTRO</option>
													</select>
												</div>		
												<label class="col-lg-1 control-label">Rep. Legal</label>
												<div class="col-lg-3">
													<select name="RepLegal[]" onChange="CambiarMetodoCtc('<?php echo $Cont;?>');" class="form-control m-b" id="RepLegal<?php echo $Cont;?>" required>
														<option value="NO" <?php if($row_Cont['RepLegal']=='NO'){echo "selected=\"selected\"";} ?>>NO</option>
														<option value="SI" <?php if($row_Cont['RepLegal']=='SI'){echo "selected=\"selected\"";} ?>>SI</option>
													</select>
												</div>
												<label class="col-lg-1 control-label">Email</label>
												<div class="col-lg-3">
													<input type="email" onChange="CambiarMetodoCtc('<?php echo $Cont;?>');" class="form-control" name="Email[]" id="Email<?php echo $Cont;?>" value="<?php echo $row_Cont['CorreoElectronico'];?>">
												</div>
											</div>
											<div class="form-group">
												<label class="col-lg-1 control-label">Cargo/Vínculo</label>
												<div class="col-lg-3">
													<input type="text" onChange="CambiarMetodoCtc('<?php echo $Cont;?>');" class="form-control" name="Posicion[]" id="Posicion<?php echo $Cont;?>" value="<?php echo $row_Cont['Posicion'];?>">
												</div>
											</div>
											<input id="CodigoContacto<?php echo $Cont;?>" name="CodigoContacto[]" type="hidden" value="<?php echo $row_Cont['CodigoContacto'];?>" />
											<input id="MetodoCtc<?php echo $Cont;?>" name="MetodoCtc[]" type="hidden" value="0" />
											<button type="button" id="btnCtc<?php echo $Cont;?>" class="btn btn-warning btn-xs btn_del"><i class="fa fa-minus"></i> Remover</button>
											<br><br>
											</div>
											<?php 
													$Cont++;
												} while($row_Cont=sql_fetch_array($SQL_Cont));
											} ?>
											<div id="divCtc_<?php echo $Cont;?>"> 
											<div class="form-group">
												<label class="col-lg-1 control-label">Nombre</label>
												<div class="col-lg-3">
													<input type="text" class="form-control" name="NombreContacto[]" id="NombreContacto<?php echo $Cont;?>" value="">
												</div>
												<label class="col-lg-1 control-label">Segundo nombre</label>
												<div class="col-lg-3">
													<input type="text" class="form-control" name="SegundoNombre[]" id="SegundoNombre<?php echo $Cont;?>" value="">
												</div>
												<label class="col-lg-1 control-label">Apellidos</label>
												<div class="col-lg-3">
													<input type="text" class="form-control" name="Apellidos[]" id="Apellidos<?php echo $Cont;?>" value="">
												</div>
											</div>
											<div class="form-group">
												<label class="col-lg-1 control-label">Cédula</label>
												<div class="col-lg-3">
													<input type="text" class="form-control" name="CedulaContacto[]" id="CedulaContacto<?php echo $Cont;?>">
												</div>	
												<label class="col-lg-1 control-label">Teléfono</label>
												<div class="col-lg-3">
													<input type="text" maxlength="20" class="form-control" name="Telefono[]" id="Telefono<?php echo $Cont;?>" value="">
												</div>
												<label class="col-lg-1 control-label">Celular</label>
												<div class="col-lg-3">
													<input type="text" maxlength="50" class="form-control" name="TelefonoCelular[]" id="TelefonoCelular<?php echo $Cont;?>" value="">
												</div>
											</div>
											<div class="form-group">
												<label class="col-lg-1 control-label">Actividad económica</label>
												<div class="col-lg-3">
													<select name="ActEconomica[]" class="form-control m-b" id="ActEconomica<?php echo $Cont;?>">
														<option value="">Seleccione...</option>
														<option value="EMPLEADO">EMPLEADO</option>
														<option value="INDEPENDIENTE">INDEPENDIENTE</option>
														<option value="OTRO">OTRO</option>
													</select>
												</div>
												<label class="col-lg-1 control-label">Rep. Legal</label>
												<div class="col-lg-3">
													<select name="RepLegal[]" class="form-control m-b" id="RepLegal<?php echo $Cont;?>">
														<option value="NO">NO</option>
														<option value="SI">SI</option>
													</select>
												</div>
												<label class="col-lg-1 control-label">Email</label>
												<div class="col-lg-3">
													<input type="email" class="form-control" name="Email[]" id="Email<?php echo $Cont;?>" value="">
												</div>
											</div>
											<div class="form-group">
												<label class="col-lg-1 control-label">Cargo/Vínculo</label>
												<div class="col-lg-3">
													<input type="text" class="form-control" name="Posicion[]" id="Posicion<?php echo $Cont;?>" value="">
												</div>
											</div>
											<input id="CodigoContacto<?php echo $Cont;?>" name="CodigoContacto[]" type="hidden" value="0" />
											<input id="MetodoCtc<?php echo $Cont;?>" name="MetodoCtc[]" type="hidden" value="1" />
											<button type="button" id="btnCtc<?php echo $Cont;?>" class="btn btn-success btn-xs" onClick="addFieldCtc(this);"><i class="fa fa-plus"></i> Añadir</button>
											<br><br>
											</div>
								   </div>
								   <div id="tabSN-3" class="tab-pane">
										<br>
											<?php $Cont=1;
											if($edit==1&&$Num_Dir>0){
												$row_Dir=sql_fetch_array($SQL_Dir);
												do{ ?>
											<div id="div_<?php echo $Cont;?>">
												<div class="form-group">
													<label class="col-lg-1 control-label">Tipo dirección</label>
													<div class="col-lg-4">
													  <select name="AdresType[]" onChange="CambiarMetodo('<?php echo $Cont;?>');" id="AdresType<?php echo $Cont;?>" class="form-control m-b" required>
															<option value="B" <?php if($row_Dir['TipoDireccion']=='B'){echo "selected=\"selected\"";} ?>>DIRECCIÓN DE FACTURACIÓN</option>
															<option value="S" <?php if($row_Dir['TipoDireccion']=='S'){echo "selected=\"selected\"";} ?>>DIRECCIÓN DE ENVÍO</option>
														</select>
													</div>
													<label class="col-lg-1 control-label">Nombre dirección</label>
													<div class="col-lg-4">
														<input name="Address[]" onChange="CambiarMetodo('<?php echo $Cont;?>');" type="text" required class="form-control" id="Address<?php echo $Cont;?>" maxlength="50" value="<?php echo $row_Dir['NombreSucursal'];?>">
													</div>
												</div>
												<div class="form-group">
													<label class="col-lg-1 control-label">Dirección</label>
													<div class="col-lg-4">
														<input name="Street[]" onChange="CambiarMetodo('<?php echo $Cont;?>');" type="text" required class="form-control" id="Street<?php echo $Cont;?>" maxlength="100" value="<?php echo $row_Dir['Direccion'];?>">
													</div>
													<label class="col-lg-1 control-label">Departamento</label>
													<div class="col-lg-4">
														<select name="County[]" id="County<?php echo $Cont;?>" class="form-control m-b" required onChange="BuscarCiudad('<?php echo $Cont;?>');CambiarMetodo('<?php echo $Cont;?>');">
															<option value="">Seleccione...</option>
														<?php
															while($row_Dptos=sqlsrv_fetch_array($SQL_Dptos)){?>
																<option value="<?php echo $row_Dptos['Departamento'];?>" <?php if((strcmp($row_Dptos['Departamento'],$row_Dir['Departamento'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Dptos['Departamento'];?></option>
														<?php }?>
														</select>
													</div>
												</div>				
												<div class="form-group">													
													<label class="col-lg-1 control-label">Ciudad</label>
													<div class="col-lg-4">
														<select name="City[]" onChange="BuscarBarrio('<?php echo $Cont;?>');CambiarMetodo('<?php echo $Cont;?>');" id="City<?php echo $Cont;?>" class="form-control m-b" required>
															<option value="">Seleccione...</option>
														<?php
															$SQL_City=Seleccionar('uvw_tbl_Municipios','Distinct Codigo, Ciudad',"Departamento='".$row_Dir['Departamento']."'",'Ciudad');
															while($row_City=sqlsrv_fetch_array($SQL_City)){?>
																<option value="<?php echo $row_City['Codigo'];?>" <?php if((strcmp($row_City['Ciudad'],$row_Dir['Ciudad'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_City['Ciudad'];?></option>
														<?php }?>
														</select>
													</div>
													<label class="col-lg-1 control-label">Barrio</label>
													<div class="col-lg-4">
														<select name="Block[]" onChange="CambiarMetodo('<?php echo $Cont;?>');" id="Block<?php echo $Cont;?>" class="form-control m-b" required>
															<option value="">Seleccione...</option>
														<?php
															$SQL_Barrio=Seleccionar('uvw_Sap_tbl_Barrios','*',"IdMunicipio='".$row_Dir['IdMunicipio']."'",'DeBarrio');
															while($row_Barrio=sqlsrv_fetch_array($SQL_Barrio)){?>
																<option value="<?php echo $row_Barrio['IdBarrio'];?>" <?php if((strcmp($row_Barrio['IdBarrio'],$row_Dir['IdBarrio'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Barrio['DeBarrio'];?></option>
														<?php }?>
														</select>
													</div>
												</div>
											<input id="LineNum<?php echo $Cont;?>" name="LineNum[]" type="hidden" value="<?php echo $row_Dir['NumeroLinea'];?>" />
											<input id="Metodo<?php echo $Cont;?>" name="Metodo[]" type="hidden" value="0" />
											<button type="button" id="<?php echo $Cont;?>" class="btn btn-warning btn-xs btn_del"><i class="fa fa-minus"></i> Remover</button>
											<br><br>
											</div>
											<?php 
													$Cont++;
													$SQL_Dptos=Seleccionar('uvw_tbl_Municipios','Distinct Departamento','','Departamento');
												} while($row_Dir=sql_fetch_array($SQL_Dir));
											} ?>

											<div id="div_<?php echo $Cont;?>">
												<div class="form-group">
													<label class="col-lg-1 control-label">Tipo dirección</label>
													<div class="col-lg-4">
													  <select name="AdresType[]" id="AdresType<?php echo $Cont;?>" class="form-control m-b" required>
															<option value="B">DIRECCIÓN DE FACTURACIÓN</option>
															<option value="S">DIRECCIÓN DE ENVÍO</option>
														</select>
													</div>
													<label class="col-lg-1 control-label">Nombre dirección</label>
													<div class="col-lg-4">
														<input name="Address[]" type="text" required class="form-control" id="Address<?php echo $Cont;?>" maxlength="50">
													</div>
												</div>
												<div class="form-group">
													<label class="col-lg-1 control-label">Dirección</label>
													<div class="col-lg-4">
														<input name="Street[]" type="text" required class="form-control" id="Street<?php echo $Cont;?>" maxlength="100">
													</div>
													<label class="col-lg-1 control-label">Departamento</label>
													<div class="col-lg-4">
														<select name="County[]" id="County<?php echo $Cont;?>" class="form-control m-b" required onChange="BuscarCiudad('<?php echo $Cont;?>');">
															<option value="">Seleccione...</option>
														<?php
															while($row_Dptos=sqlsrv_fetch_array($SQL_Dptos)){?>
																<option value="<?php echo $row_Dptos['Departamento'];?>"><?php echo $row_Dptos['Departamento'];?></option>
														<?php }?>
														</select>
													</div>
												</div>				
												<div class="form-group">
													<label class="col-lg-1 control-label">Ciudad</label>
													<div class="col-lg-4">
														<select name="City[]" id="City<?php echo $Cont;?>" onChange="BuscarBarrio('<?php echo $Cont;?>');" class="form-control m-b" required>
															<option value="">Seleccione...</option>
														</select>
													</div>
													<label class="col-lg-1 control-label">Barrio</label>
													<div class="col-lg-4">
														<select name="Block[]" id="Block<?php echo $Cont;?>" class="form-control m-b" required>
															<option value="">Seleccione...</option>
														</select>
													</div>
												</div>
											<input id="LineNum<?php echo $Cont;?>" name="LineNum[]" type="hidden" value="0" />
											<input id="Metodo<?php echo $Cont;?>" name="Metodo[]" type="hidden" value="1" />
											<button type="button" id="<?php echo $Cont;?>" class="btn btn-success btn-xs" onClick="addField(this);"><i class="fa fa-plus"></i> Añadir</button>
											<br><br>
											</div>	
								   </div>
								   <?php if($edit==1){?>
								   <div id="tabSN-4" class="tab-pane">
										<br>
	<div class="tabs-container">
		<ul class="nav nav-tabs">
			<li class="active"><a data-toggle="tab" href="#tab-4"><i class="fa fa-file-text"></i> Facturas pendientes</a></li>
			<li><a data-toggle="tab" href="#tab-2" onClick="ConsultarTab('2');"><i class="fa fa-phone"></i> Llamadas de servicios</a></li>
			<li><a data-toggle="tab" href="#tab-3" onClick="ConsultarTab('3');"><i class="fa fa-calendar"></i> Actividades</a></li>
			<li><a data-toggle="tab" href="#tab-5" onClick="ConsultarTab('5');"><i class="fa fa-money"></i> Pagos realizados</a></li>
		</ul>
		<div class="tab-content">
			<div id="tab-4" class="tab-pane active">
				<div class="panel-body">
					<div class="form-group">
						<div class="col-lg-12">
							<div class="table-responsive">
							<table class="table table-striped table-bordered">
								<thead>
								<tr>
									<th>Número</th>
									<th>Fecha contabilización</th>
									<th>Fecha vencimiento</th>
									<th>Valor factura</th>
									<th>Abono</th>
									<th>Dias vencidos</th>
									<th>Saldo total</th>						
									<th>Acciones</th>
									<th>Seleccionar</th>
								</tr>
								</thead>
								<tbody>
								<?php while($row_FactPend=sqlsrv_fetch_array($SQL_FactPend)){?>
									 <tr>
										<td><?php echo $row_FactPend['NoDocumento'];?></td>
										<td><?php if($row_FactPend['FechaContabilizacion']->format('Y-m-d')){echo $row_FactPend['FechaContabilizacion']->format('Y-m-d');}else{echo $row_FactPend['FechaContabilizacion'];}?></td>
										<td><?php if($row_FactPend['FechaVencimiento']->format('Y-m-d')){echo $row_FactPend['FechaVencimiento']->format('Y-m-d');}else{echo $row_FactPend['FechaVencimiento'];}?></td>
										<td><?php echo "$".number_format($row_FactPend['TotalDocumento'],2);?></td>
										<td><?php echo "$".number_format($row_FactPend['ValorPagoDocumento'],2);?></td>
										<td><?php echo number_format($row_FactPend['DiasVencidos'],0);?></td>
										<td><?php echo "$".number_format($row_FactPend['SaldoDocumento'],2);?></td>
										<td><a href="sapdownload.php?id=<?php echo base64_encode('15');?>&type=<?php echo base64_encode('2');?>&DocKey=<?php echo base64_encode($row_FactPend['NoInterno']);?>&ObType=<?php echo base64_encode('13');?>&IdFrm=<?php echo base64_encode('0');?>" target="_blank" class="btn btn-link btn-xs"><i class="fa fa-download"></i> Descargar</a></td>
										<td><div class="checkbox checkbox-success"><input type="checkbox" id="singleCheckbox<?php echo $row_FactPend['NoDocumento'];?>" value="" onChange="SeleccionarFactura('<?php echo base64_encode($row_FactPend['NoInterno']);?>','<?php echo base64_encode('13');?>','<?php echo base64_encode('0');?>');" aria-label="Single checkbox One"><label></label></div></td>
									</tr>
								<?php }?>
									<tr id="dwnAllFact" style="display:none"><td colspan="9" class="text-right"><input type="hidden" id="FactSel" name="FactSel" value="" /><a id="LinkAllFact" href="#" target="_blank" class="btn btn-link btn-xs"><i class="fa fa-download"></i> Descargar facturas seleccionadas</a></td></tr>
								</tbody>
							</table>
							</div>
						</div>
					</div>	
				</div>	
			</div>		
			<div id="tab-2" class="tab-pane">
				<div id="dv_llamadasrv" class="panel-body">
		
				</div>	
			</div>
			<div id="tab-3" class="tab-pane">
				<div id="dv_actividades" class="panel-body">
					
				</div>	
			</div>
			
			<div id="tab-5" class="tab-pane">
				<div id="dv_pagosreal" class="panel-body">
						
				</div>	
			</div>
		</div>
	</div>
								   </div>
								   <?php } ?>
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
	 $("#EditarSN").validate({
		 submitHandler: function(form){
			 $('.ibox-content').toggleClass('sk-loading');
			 form.submit();
		}
	});
	 $(".alkin").on('click', function(){
		 $('.ibox-content').toggleClass('sk-loading');
	 });
	 
	$(".select2").select2();
	
	<?php if(PermitirFuncion(504)){ ?>
		$('#CardType option:not(:selected)').attr('disabled',true);
 	<?php }?>
	 
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
<script>
function addField(btn){//Clonar divDir
	var clickID = parseInt($(btn).parent('div').attr('id').replace('div_',''));
	//alert($(btn).parent('div').attr('id'));
	//alert(clickID);
	var newID = (clickID+1);

	$newClone = $('#div_'+clickID).clone(true);

	//div
	$newClone.attr("id",'div_'+newID);

	//select
	$newClone.children("div").eq(0).children("div").eq(0).children("select").eq(0).attr('id','AdresType'+newID);
	$newClone.children("div").eq(1).children("div").eq(1).children("select").eq(0).attr('id','County'+newID);
	$newClone.children("div").eq(1).children("div").eq(1).children("select").eq(0).attr('onChange','BuscarCiudad('+newID+');');
	$newClone.children("div").eq(2).children("div").eq(0).children("select").eq(0).attr('id','City'+newID);
	$newClone.children("div").eq(2).children("div").eq(0).children("select").eq(0).attr('onChange','BuscarBarrio('+newID+');');
	$newClone.children("div").eq(2).children("div").eq(1).children("select").eq(0).attr('id','Block'+newID);
	
	//$newClone.children("div").eq(1).children("div").eq(1).children("select").eq(0).select2('destroy');
	//$newClone.children("div").eq(1).children("div").eq(1).children("select").eq(0).select2();

	//inputs
	$newClone.children("div").eq(0).children("div").eq(1).children("input").eq(0).attr('id','Address'+newID);
	$newClone.children("div").eq(1).children("div").eq(0).children("input").eq(0).attr('id','Street'+newID);
	
	$newClone.children("input").eq(0).attr('id','LineNum'+newID);
	$newClone.children("input").eq(1).attr('id','Metodo'+newID);

	//button
	$newClone.children("button").eq(0).attr('id',''+newID);

	$newClone.insertAfter($('#div_'+clickID));

	//$("#"+clickID).val('Remover');
	document.getElementById(''+clickID).innerHTML="<i class='fa fa-minus'></i> Remover";
	document.getElementById(''+clickID).setAttribute('class','btn btn-warning btn-xs btn_del');
	document.getElementById(''+clickID).setAttribute('onClick','delRow2(this);');

	//$("#"+clickID).addEventListener("click",delRow);

	//$("#"+clickID).bind("click",delRow);
}
	
function addFieldCtc(btn){//Clonar divCtc
	var clickID = parseInt($(btn).parent('div').attr('id').replace('divCtc_',''));
	//alert($(btn).parent('div').attr('id'));
	//alert(clickID);
	var newID = (clickID+1);

	$newClone = $('#divCtc_'+clickID).clone(true);

	//div
	$newClone.attr("id",'divCtc_'+newID);

	//select
	$newClone.children("div").eq(2).children("div").eq(0).children("select").eq(0).attr('id','ActEconomica'+newID);
	$newClone.children("div").eq(2).children("div").eq(1).children("select").eq(0).attr('id','RepLegal'+newID);

	//inputs
	$newClone.children("div").eq(0).children("div").eq(0).children("input").eq(0).attr('id','NombreContacto'+newID);
	$newClone.children("div").eq(0).children("div").eq(1).children("input").eq(0).attr('id','SegundoNombre'+newID);
	$newClone.children("div").eq(0).children("div").eq(2).children("input").eq(0).attr('id','Apellidos'+newID);
	$newClone.children("div").eq(1).children("div").eq(0).children("input").eq(0).attr('id','CedulaContacto'+newID);
	$newClone.children("div").eq(1).children("div").eq(1).children("input").eq(0).attr('id','Telefono'+newID);
	$newClone.children("div").eq(1).children("div").eq(2).children("input").eq(0).attr('id','TelefonoCelular'+newID);
	$newClone.children("div").eq(2).children("div").eq(2).children("input").eq(0).attr('id','Email'+newID);
	$newClone.children("div").eq(3).children("div").eq(0).children("input").eq(0).attr('id','Posicion'+newID);
	
	$newClone.children("input").eq(0).attr('id','CodigoContacto'+newID);
	$newClone.children("input").eq(1).attr('id','MetodoCtc'+newID);

	//button
	$newClone.children("button").eq(0).attr('id','btnCtc'+newID);

	$newClone.insertAfter($('#divCtc_'+clickID));

	//$("#"+clickID).val('Remover');
	document.getElementById('btnCtc'+clickID).innerHTML="<i class='fa fa-minus'></i> Remover";
	document.getElementById('btnCtc'+clickID).setAttribute('class','btn btn-warning btn-xs btn_del');
	document.getElementById('btnCtc'+clickID).setAttribute('onClick','delRow2(this);');

	//$("#"+clickID).addEventListener("click",delRow);

	//$("#"+clickID).bind("click",delRow);
}
</script>
<script>
	 $(document).ready(function(){
		 $(".btn_del").each(function (el){
			 $(this).bind("click",delRow);
		 });
		 
		  var options = {
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
				},
				onSelectItemEvent: function() {
					var value = $("#MunicipioMM").getSelectedItemData().Codigo;
					$("#ID_MunicipioMM").val(value).trigger("change");
				}
			}
		};

		$("#MunicipioMM").easyAutocomplete(options);
	});
</script>
<script>
function delRow(){//Eliminar div
	$(this).parent('div').remove();
}
function delRow2(btn){//Eliminar div
	$(btn).parent('div').remove();
}
</script>
<script>
//Variables de tab
 var tab_2=0;
 var tab_3=0;
 var tab_4=0;
 var tab_5=0;
 var tab_6=0;
	
function BuscarCiudad(id){
	$.ajax({
		type: "POST",
		url: "ajx_cbo_select.php?type=8&id="+document.getElementById('County'+id).value,
		success: function(response){
			$('#City'+id).html(response).fadeIn();
			$('#City'+id).trigger('change');
		}
	});
}

function BuscarBarrio(id){
	$.ajax({
		type: "POST",
		url: "ajx_cbo_select.php?type=13&id="+document.getElementById('City'+id).value,
		success: function(response){
			$('#Block'+id).html(response).fadeIn();
		}
	});
}

function CambiarMetodo(id){
	var inpMetodo=document.getElementById("Metodo"+id);
	inpMetodo.value=2;
}

function CambiarMetodoCtc(id){
	var inpMetodo=document.getElementById("MetodoCtc"+id);
	inpMetodo.value=2;
}

function ConsultarTab(type){
	if(type==2){//Llamada de servicio
		if(tab_2==0){
			$('.ibox-content').toggleClass('sk-loading',true);
			$.ajax({
				type: "POST",
				url: "sn_llamadas_servicios.php?id=<?php if($edit==1){echo base64_encode($row['CodigoCliente']);}?>",
				success: function(response){
					$('#dv_llamadasrv').html(response).fadeIn();
					$('.ibox-content').toggleClass('sk-loading',false);
					tab_2=1;
				}
			});
		}
	}else if(type==3){//Actividades
		if(tab_3==0){
			$('.ibox-content').toggleClass('sk-loading',true);
			$.ajax({
				type: "POST",
				url: "sn_actividades.php?id=<?php if($edit==1){echo base64_encode($row['CodigoCliente']);}?>",
				success: function(response){
					$('#dv_actividades').html(response).fadeIn();
					$('.ibox-content').toggleClass('sk-loading',false);
					tab_3=1;
				}
			});
		}
	}else if(type==5){//Pagos realizados
		if(tab_5==0){
			$('.ibox-content').toggleClass('sk-loading',true);
			$.ajax({
				type: "POST",
				url: "sn_pagos_realizados.php?id=<?php if($edit==1){echo base64_encode($row['CodigoCliente']);}?>",
				success: function(response){
					$('#dv_pagosreal').html(response).fadeIn();
					$('.ibox-content').toggleClass('sk-loading',false);
					tab_5=1;
				}
			});
		}
	}
}
</script>
<!-- InstanceEndEditable -->
</body>

<!-- InstanceEnd --></html>
<?php sqlsrv_close($conexion);?>