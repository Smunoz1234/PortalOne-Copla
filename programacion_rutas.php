<?php require_once("includes/conexion.php");
PermitirAcceso(312);
$sw=0;

$ParamSucursal=array(
	"'".$_SESSION['CodUser']."'"
);
$SQL_Suc=EjecutarSP('sp_ConsultarSucursalesUsuario',$ParamSucursal);

$SQL_TiposEstadoServ=Seleccionar("uvw_tbl_TipoEstadoServicio","*");
$Num_TiposEstadoServ=sqlsrv_num_rows($SQL_TiposEstadoServ);

//$i=0;
$FilRec="";
//$Filtro="";

//Fechas
if(isset($_GET['FechaInicial'])&&$_GET['FechaInicial']!=""){
	$FechaInicial=$_GET['FechaInicial'];
	$sw=1;
}else{
	//Restar 7 dias a la fecha actual
	$fecha = date('Y-m-d');
	$nuevafecha = strtotime ('-'.ObtenerVariable("DiasRangoFechasDocSAP").' day');
	$nuevafecha = date ( 'Y-m-d' , $nuevafecha);
	$FechaInicial=$nuevafecha;
}
if(isset($_GET['FechaFinal'])&&$_GET['FechaFinal']!=""){
	$FechaFinal=$_GET['FechaFinal'];
	$sw=1;
}else{
	//sumar 7 dias a la fecha actual
	$fecha = date('Y-m-d');
	$nuevafecha = strtotime ('+'.ObtenerVariable("DiasRangoFechasDocSAP").' day');
	$nuevafecha = date ( 'Y-m-d' , $nuevafecha);
	$FechaFinal=$nuevafecha;
}

$Sede = isset($_GET['Sede']) ? $_GET['Sede'] : "";
$Recurso = isset($_GET['Recursos']) ? implode(',',$_GET['Recursos']) : "";
$Cliente = isset($_GET['Cliente']) ? $_GET['Cliente'] : "";
$NomSucursal = isset($_GET['Sucursal']) ? $_GET['Sucursal'] : "";


//Lista de recursos (Tecnicos)
$ParamRec=array(
	"'".$_SESSION['CodUser']."'",
	"'".$Sede."'"	
);

$SQL_Recursos=EjecutarSP("sp_ConsultarTecnicos",$ParamRec);

if(isset($_GET['Recursos'])&&$_GET['Recursos']!=""){
	$FilRec=implode(',',$_GET['Recursos']);
	$sw=1;
}

//Consultar actividades
if($sw==1){
	$ParamEvento=array(
		"'".$Sede."'",
		//"'".$Recurso."'",
		"'".$Cliente."'",
		"'".$NomSucursal."'",
		"'".FormatoFecha($FechaInicial)."'",
		"'".FormatoFecha($FechaFinal)."'",
		"'".$_SESSION['CodUser']."'"
	);

	$SQL_Evento=EjecutarSP("sp_ConsultarDatosCalendarioRutas",$ParamEvento);
	
	//Obtengo el IdEvento
	$row_Evento=sqlsrv_fetch_array($SQL_Evento);

	$ParamCons=array(
		"'".$Recurso."'",
		"'".$row_Evento['IdEvento']."'",
		"'".$_SESSION['CodUser']."'"
	);

	$SQL_Actividad=EjecutarSP("sp_ConsultarDatosCalendarioRutasRecargar",$ParamCons);
	
	//Consultamos la lista de OT pendientes
	$ParamOT=array(
		"1",
		"'".$_SESSION['CodUser']."'",
		"'".$row_Evento['IdEvento']."'",
		"'".$Sede."'",
		"'".$Cliente."'",
		"'".$NomSucursal."'",
		"'".FormatoFecha($FechaInicial)."'",
		"'".FormatoFecha($FechaFinal)."'"
	);

	$SQL_OT=EjecutarSP("sp_ConsultarDatosCalendarioRutasOT",$ParamOT);
	$Num_OT=sqlsrv_num_rows($SQL_OT);
	
	//TRAER LOS DATOS UNICOS PARA FILTRAR
	
	//Llamada de servicio
	$SQL_LlamadaServicio=Seleccionar("tbl_LlamadasServicios_Rutas","DISTINCT DocNum","Usuario='".$_SESSION['CodUser']."' and IdEvento='".$row_Evento['IdEvento']."'","DocNum");
	
	//Series
	$SQL_Series=Seleccionar("tbl_LlamadasServicios_Rutas","DISTINCT IdSeries, DeSeries","Usuario='".$_SESSION['CodUser']."' and IdEvento='".$row_Evento['IdEvento']."'","DeSeries");
	
	//Cliente
	$SQL_Cliente=Seleccionar("tbl_LlamadasServicios_Rutas","DISTINCT ID_CodigoCliente, NombreClienteLlamada","Usuario='".$_SESSION['CodUser']."' and IdEvento='".$row_Evento['IdEvento']."'","NombreClienteLlamada");
	
	//SucursalCliente
	$SQL_SucursalCliente=Seleccionar("tbl_LlamadasServicios_Rutas","DISTINCT NombreSucursal","Usuario='".$_SESSION['CodUser']."' and IdEvento='".$row_Evento['IdEvento']."'","NombreSucursal");
	
	//Servicios
	$SQL_Servicios=Seleccionar("tbl_LlamadasServicios_Rutas","DISTINCT Servicios","Usuario='".$_SESSION['CodUser']."' and IdEvento='".$row_Evento['IdEvento']."'","Servicios");
	
	//Areas
	$SQL_Areas=Seleccionar("tbl_LlamadasServicios_Rutas","DISTINCT Areas","Usuario='".$_SESSION['CodUser']."' and IdEvento='".$row_Evento['IdEvento']."'","Areas");
	
	//ArticuloLlamada
	$SQL_ArticuloLlamada=Seleccionar("tbl_LlamadasServicios_Rutas","DISTINCT IdArticuloLlamada, DeArticuloLlamada","Usuario='".$_SESSION['CodUser']."' and IdEvento='".$row_Evento['IdEvento']."'","DeArticuloLlamada");
	
	//Tipo de llamada	
	$SQL_TipoLlamada=Seleccionar("tbl_LlamadasServicios_Rutas","DISTINCT IdTipoLlamada, DeTipoLlamada","Usuario='".$_SESSION['CodUser']."' and IdEvento='".$row_Evento['IdEvento']."'","DeTipoLlamada");
	
	//Ciudad
	$SQL_Ciudad=Seleccionar("tbl_LlamadasServicios_Rutas","DISTINCT CiudadLlamada","Usuario='".$_SESSION['CodUser']."' and IdEvento='".$row_Evento['IdEvento']."'","CiudadLlamada");	
	
}


?>
<!DOCTYPE html>
<html class="light-style">

<head>
<?php include("includes/cabecera_new.php"); ?>
<title>Programaci??n de rutas | <?php echo NOMBRE_PORTAL;?></title>
<style>
	body, html{
		font-size: 13px;
		background: #f5f5f5;
	}
	.ps__thumb-y{
		height: 15px !important;
	}
	.event-striped {
		background-image:linear-gradient(45deg, rgba(255,255,255,0.15) 25%, transparent 25%, transparent 50%, rgba(255,255,255,0.15) 50%, rgba(255,255,255,0.15) 75%, transparent 75%, transparent);
		background-size:.75rem .75rem;
	}
</style>
<script type="text/javascript">
	$(document).ready(function() {//Cargar los almacenes dependiendo del proyecto
		$("#NombreCliente").change(function(){
			var NomCliente=document.getElementById("NombreCliente");
			var Cliente=document.getElementById("Cliente");
			if(NomCliente.value==""){
				Cliente.value="";
				$("#Cliente").trigger("change");
			}
		});
		$("#Cliente").change(function(){
			var Cliente=document.getElementById("Cliente");
			$.ajax({
				type: "POST",
				url: "ajx_cbo_sucursales_clientes_simple.php?CardCode="+Cliente.value,
				success: function(response){
					$('#Sucursal').html(response).fadeIn();
				}
			});
		});
		$("#Sede").change(function(){
			$.ajax({
				type: "POST",
				url: "ajx_cbo_select.php?type=27&id="+document.getElementById('Sede').value,
				success: function(response){
					$('#Recursos').html(response);
					$("#Recursos").trigger("change");
				}
			});
		});
	});
</script>

</head>

<body>
    <div class="container-fluid">
	 <!-- Event modal -->
		<div class="modal modal-top fade" id="ModalAct" data-backdrop="static" data-keyboard="false">
		  <div id="ContenidoModal" class="modal-dialog modal-xl">
			
		  </div>
		</div>
		<!-- / Event modal -->
		
		<div class="row mb-md-3 mt-md-4">
			<div id="accordionTitle" class="card col-lg-12 p-md-4">
				<div class="pt-3 pr-3 pl-3 pb-0 mb-2 bg-primary text-white">
					<a class="d-flex justify-content-between text-white" data-toggle="collapse" aria-expanded="true" href="#accordionTitle-1">
						<h4 class="pr-2"><i class="fas fa-filter"></i> Agregue los filtros necesarios</h4>
						<div class="collapse-icon"></div>
					</a>
				</div>
				<div id="accordionTitle-1" class="collapse show" data-parent="#accordionTitle">
					<form action="programacion_rutas.php" method="get" class="form-horizontal" id="frmProgramacion">
						<div class="form-row">
							<div class="form-group col-lg-3">
								<label class="form-label">Fechas</label>
								<div class="input-group">
									<input name="FechaInicial" type="text" class="form-control" id="FechaInicial" value="<?php echo $FechaInicial;?>" placeholder="YYYY-MM-DD">
									<span class="input-group-prepend px-2 bg-light text-center pt-2">hasta</span>
									<input name="FechaFinal" type="text" class="form-control" id="FechaFinal" value="<?php echo $FechaFinal;?>" placeholder="YYYY-MM-DD">
								</div>
							</div>
							<div class="form-group col-lg-3">
								<label class="form-label">Sede</label>
								 <div class="select2-success">
								  <select name="Sede" id="Sede" class="select2 form-control">
									  <option value="">(TODOS)</option>
									  <?php
									  while($row_Suc=sqlsrv_fetch_array($SQL_Suc)){?>
											<option value="<?php echo $row_Suc['IdSucursal'];?>" <?php if((isset($_GET['Sede'])&&($_GET['Sede']!=""))&&(strcmp($row_Suc['IdSucursal'],$_GET['Sede'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Suc['DeSucursal'];?></option>
									  <?php }?>
								  </select>
								</div>
							</div>
							<div class="form-group col-lg-3">
								<label class="form-label">&nbsp;</label>
								<button id="btnFiltrar" type="submit" class="btn btn-success load mt-4"><i class="fas fa-filter"></i> Filtrar datos</button>
							</div>
							<div class="form-group col-lg-3">
								<button id="btnGuardar" type="button" class="btn btn-danger load pull-right mt-4" disabled onClick="EjecutarProceso();"><i class="fas fa-save"></i> Guardar todo</button>
								<button id="btnPendientes" type="button" class="btn btn-warning pull-right mr-2 mt-4" disabled onClick="VerificarPendientes();"><i class="fas fa-tags"></i> Pendientes por enviar</button>							
							</div>
						</div>
						<div class="form-row">
							<div class="form-group col-lg-3">
								<label class="form-label">Cliente</label>
								<input name="Cliente" type="hidden" id="Cliente" value="<?php if(isset($_GET['Cliente'])&&($_GET['Cliente']!="")){ echo $_GET['Cliente'];}?>">
								<input name="NombreCliente" type="text" class="form-control" id="NombreCliente" placeholder="Ingrese para buscar..." value="<?php if(isset($_GET['NombreCliente'])&&($_GET['NombreCliente']!="")){ echo $_GET['NombreCliente'];}?>">
							</div>
							<div class="form-group col-lg-3">
								<label class="form-label">Sucursal</label>
								<div class="select2-success">
								  <select name="Sucursal" id="Sucursal" class="select2 form-control">
									 <option value="">(TODOS)</option>
									 <?php 
									 if(isset($_GET['Sucursal'])){//Cuando se ha seleccionado una opci??n
										 if(PermitirFuncion(205)){
											$Where="CodigoCliente='".$_GET['Cliente']."'";
											$SQL_Sucursal=Seleccionar("uvw_Sap_tbl_Clientes_Sucursales","NombreSucursal",$Where);
										 }else{
											$Where="CodigoCliente='".$_GET['Cliente']."' and ID_Usuario = ".$_SESSION['CodUser'];
											$SQL_Sucursal=Seleccionar("uvw_tbl_SucursalesClienteUsuario","NombreSucursal",$Where);	
										 }
										 while($row_Sucursal=sqlsrv_fetch_array($SQL_Sucursal)){?>
											<option value="<?php echo $row_Sucursal['NombreSucursal'];?>" <?php if(strcmp($row_Sucursal['NombreSucursal'],$_GET['Sucursal'])==0){ echo "selected=\"selected\"";}?>><?php echo $row_Sucursal['NombreSucursal'];?></option>
									 <?php }
									 }?>
								  </select>
								</div>
							</div>
							<div class="form-group col-lg-3">
								<label class="form-label">Visualizaci??n</label>
								<div class="input-group">
									<label class="switcher">
										<input type="checkbox" class="switcher-input" id="chkDatesAboveResources" checked="checked">
										<span class="switcher-indicator">
										<span class="switcher-yes"></span>
										<span class="switcher-no"></span>
										</span>
										<span class="switcher-label">Mostrar fechas arriba de los t??cnicos</span>
									</label>
								</div>
							</div>
						</div>
						<div class="form-row">
							<div class="form-group col-lg-3">
								<label class="form-label">T??cnicos</label>
								 <div class="select2-success">
								  <select name="Recursos[]" id="Recursos" class="select2 form-control" multiple style="width: 100%" data-placeholder="(TODOS)">
									  <?php
									  if(isset($_GET['Sede'])){$j=0; 
									  while($row_Recursos=sqlsrv_fetch_array($SQL_Recursos)){?>
											<option value="<?php echo $row_Recursos['ID_Empleado'];?>" <?php if((isset($_GET['Recursos'][$j])&&($_GET['Recursos'][$j]!=""))&&(strcmp($row_Recursos['ID_Empleado'],$_GET['Recursos'][$j])==0)){ echo "selected=\"selected\"";$j++;}?>><?php echo $row_Recursos['NombreEmpleado'];?></option>
									  <?php }}?>
								  </select>
								</div>
							</div>
							<div class="form-group col-lg-3">
								<label class="form-label">&nbsp;</label>
								<button id="btnRefrescar" type="button" onClick="RefresarCalendario();" class="btn btn-info mt-4" <?php if($sw==0){echo "disabled";}?>><i class="fas fa-sync"></i> Refrescar</button>
							</div>
							<div class="form-group col-lg-6">
								<label class="form-label">Referencia de colores</label>
								<div class="input-group">
									 <?php
									  while($row_TiposEstadoServ=sqlsrv_fetch_array($SQL_TiposEstadoServ)){?>
											<div class="d-inline"><i class="fas fa-square-full mr-2 ml-2" style="color: <?php echo $row_TiposEstadoServ['ColorEstadoServicio'];?>;"></i> <?php echo $row_TiposEstadoServ['DE_TipoEstadoServicio'];?></div>									
									  <?php }?>
								</div>															
							</div>
						</div>						
						<input type="hidden" id="IdEvento" name="IdEvento" value="<?php if($sw==1){echo $row_Evento['IdEvento'];}?>" />
					</form>
				</div>
			</div>
		</div>	
		<div class="row">
			<div id="dvOT" class="card col-lg-2" style="max-height: 1110px; min-height: auto;">
				<div class="alert alert-primary mt-lg-3 text-center" role="alert">
				  <strong>Lista de OT pendientes (<span id="CantOT"><?php echo isset($Num_OT) ? number_format($Num_OT) : 0;?></span> resultados)</strong>
				</div>
				<?php if($sw==1){?>
				<div id="accordion1" class="sticky-top">
					<div class="card mb-2">
						<div class="card-header bg-primary text-white">							
							<a class="d-flex justify-content-between text-white" data-toggle="collapse" aria-expanded="false" href="#accordion1-1">
								<span class='pr-2'><i class="fas fa-filter"></i> Mostrar filtros</span>
								<div class="collapse-icon"></div>
							</a>
						</div>
						<div id="accordion1-1" class="collapse" data-parent="#accordion1">
							<div class="card-body">
								<form action="" method="get" class="form-horizontal" id="frmOT">
									<div class="form-row">
										<div class="form-group col-12">
											<button id="btnFiltrarOT" type="button" class="btn btn-success btn-block" onClick="FiltrarOT();"><i class="fas fa-filter"></i> Aplicar filtro</button>
										</div>
									</div>
									<div class="form-row">
										<div class="form-group col-12">
											<label class="form-label">Llamada de servicio</label>
											 <div class="select2-success">
											  <select name="LlamadaServicio[]" id="LlamadaServicio" class="select2OT form-control" multiple style="width: 100%" data-placeholder="(TODOS)">
												   <?php
													  $j=0; 
												  while($row_LlamadaServicio=sqlsrv_fetch_array($SQL_LlamadaServicio)){?>
														<option value="<?php echo $row_LlamadaServicio['DocNum'];?>" <?php if((isset($_GET['LlamadaServicio'][$j])&&($_GET['LlamadaServicio'][$j]!=""))&&(strcmp($row_LlamadaServicio['DocNum'],$_GET['LlamadaServicio'][$j])==0)){ echo "selected=\"selected\"";$j++;}?>><?php echo $row_LlamadaServicio['DocNum'];?></option>
												  <?php }?>												  
											  </select>
											</div>
										</div>
									</div>
									<div class="form-row">
										<div class="form-group col-12">
											<label class="form-label">Serie llamada</label>
											 <div class="select2-success">
											  <select name="Series[]" id="Series" class="select2OT form-control" multiple style="width: 100%" data-placeholder="(TODOS)">
												 <?php
													  $j=0; 
												  while($row_Series=sqlsrv_fetch_array($SQL_Series)){?>
														<option value="<?php echo $row_Series['IdSeries'];?>" <?php if((isset($_GET['Series'][$j])&&($_GET['Series'][$j]!=""))&&(strcmp($row_Series['IdSeries'],$_GET['Series'][$j])==0)){ echo "selected=\"selected\"";$j++;}?>><?php echo $row_Series['DeSeries'];?></option>
												  <?php }?>	
											  </select>
											</div>
										</div>
									</div>
									<div class="form-row">
										<div class="form-group col-12">
											<label class="form-label">Cliente</label>
											 <div class="select2-success">
											  <select name="ClienteLlamada[]" id="ClienteLlamada" class="select2OT form-control" multiple style="width: 100%" data-placeholder="(TODOS)">
												 <?php
													  $j=0; 
												  while($row_Cliente=sqlsrv_fetch_array($SQL_Cliente)){?>
														<option value="<?php echo $row_Cliente['ID_CodigoCliente'];?>" <?php if((isset($_GET['ClienteLlamada'][$j])&&($_GET['ClienteLlamada'][$j]!=""))&&(strcmp($row_Cliente['ID_CodigoCliente'],$_GET['ClienteLlamada'][$j])==0)){ echo "selected=\"selected\"";$j++;}?>><?php echo $row_Cliente['NombreClienteLlamada'];?></option>
												  <?php }?>			
											  </select>
											</div>
										</div>
									</div>
									<div class="form-row">
										<div class="form-group col-12">
											<label class="form-label">Sucursal cliente</label>
											 <div class="select2-success">
											  <select name="SucursalCliente[]" id="SucursalCliente" class="select2OT form-control" multiple style="width: 100%" data-placeholder="(TODOS)">
												 <?php
													  $j=0; 
												  while($row_SucursalCliente=sqlsrv_fetch_array($SQL_SucursalCliente)){?>
														<option value="<?php echo $row_SucursalCliente['NombreSucursal'];?>" <?php if((isset($_GET['SucursalCliente'][$j])&&($_GET['SucursalCliente'][$j]!=""))&&(strcmp($row_SucursalCliente['NombreSucursal'],$_GET['SucursalCliente'][$j])==0)){ echo "selected=\"selected\"";$j++;}?>><?php echo $row_SucursalCliente['NombreSucursal'];?></option>
												  <?php }?>			
											  </select>
											</div>
										</div>
									</div>
									<div class="form-row">
										<div class="form-group col-12">
											<label class="form-label">Servicios</label>
											 <div class="select2-success">
											  <select name="Servicios[]" id="Servicios" class="select2OT form-control" multiple style="width: 100%" data-placeholder="(TODOS)">
												 <?php
													  $j=0; 
												  while($row_Servicios=sqlsrv_fetch_array($SQL_Servicios)){?>
														<option value="<?php echo $row_Servicios['Servicios'];?>" <?php if((isset($_GET['Servicios'][$j])&&($_GET['Servicios'][$j]!=""))&&(strcmp($row_Servicios['Servicios'],$_GET['Servicios'][$j])==0)){ echo "selected=\"selected\"";$j++;}?>><?php echo $row_Servicios['Servicios'];?></option>
												  <?php }?>	
											  </select>
											</div>
										</div>
									</div>
									<div class="form-row">
										<div class="form-group col-12">
											<label class="form-label">??reas</label>
											 <div class="select2-success">
											  <select name="Areas[]" id="Areas" class="select2OT form-control" multiple style="width: 100%" data-placeholder="(TODOS)">
												 <?php
													  $j=0; 
												  while($row_Areas=sqlsrv_fetch_array($SQL_Areas)){?>
														<option value="<?php echo $row_Areas['Areas'];?>" <?php if((isset($_GET['Areas'][$j])&&($_GET['Areas'][$j]!=""))&&(strcmp($row_Areas['Areas'],$_GET['Areas'][$j])==0)){ echo "selected=\"selected\"";$j++;}?>><?php echo $row_Areas['Areas'];?></option>
												  <?php }?>	
											  </select>
											</div>
										</div>
									</div>
									<div class="form-row">
										<div class="form-group col-12">
											<label class="form-label">Articulo llamada</label>
											 <div class="select2-success">
											  <select name="ArticuloLlamada[]" id="ArticuloLlamada" class="select2OT form-control" multiple style="width: 100%" data-placeholder="(TODOS)">
												 <?php
													  $j=0; 
												  while($row_ArticuloLlamada=sqlsrv_fetch_array($SQL_ArticuloLlamada)){?>
														<option value="<?php echo $row_ArticuloLlamada['IdArticuloLlamada'];?>" <?php if((isset($_GET['ArticuloLlamada'][$j])&&($_GET['ArticuloLlamada'][$j]!=""))&&(strcmp($row_ArticuloLlamada['IdArticuloLlamada'],$_GET['ArticuloLlamada'][$j])==0)){ echo "selected=\"selected\"";$j++;}?>><?php echo $row_ArticuloLlamada['DeArticuloLlamada'];?></option>
												  <?php }?>	
											  </select>
											</div>
										</div>
									</div>
									<div class="form-row">
										<div class="form-group col-12">
											<label class="form-label">Tipo llamada</label>
											 <div class="select2-success">
											  <select name="TipoLlamada[]" id="TipoLlamada" class="select2OT form-control" multiple style="width: 100%" data-placeholder="(TODOS)">
												  <?php
													  $j=0; 
												  while($row_TipoLlamada=sqlsrv_fetch_array($SQL_TipoLlamada)){?>
														<option value="<?php echo $row_TipoLlamada['IdTipoLlamada'];?>" <?php if((isset($_GET['TipoLlamada'][$j])&&($_GET['TipoLlamada'][$j]!=""))&&(strcmp($row_TipoLlamada['IdTipoLlamada'],$_GET['TipoLlamada'][$j])==0)){ echo "selected=\"selected\"";$j++;}?>><?php echo $row_TipoLlamada['DeTipoLlamada'];?></option>
												  <?php }?>
											  </select>
											</div>
										</div>
									</div>
									<div class="form-row">
										<div class="form-group col-12">
											<label class="form-label">Ciudad</label>
											 <div class="select2-success">
											  <select name="Ciudad[]" id="Ciudad" class="select2OT form-control" multiple style="width: 100%" data-placeholder="(TODOS)">
												 <?php
													  $j=0; 
												  while($row_Ciudad=sqlsrv_fetch_array($SQL_Ciudad)){?>
														<option value="<?php echo $row_Ciudad['CiudadLlamada'];?>" <?php if((isset($_GET['Ciudad'][$j])&&($_GET['Ciudad'][$j]!=""))&&(strcmp($row_Ciudad['CiudadLlamada'],$_GET['Ciudad'][$j])==0)){ echo "selected=\"selected\"";$j++;}?>><?php echo $row_Ciudad['CiudadLlamada'];?></option>
												  <?php }?>	
											  </select>
											</div>
										</div>
									</div>
									<div class="form-row">
										<div class="form-group col-12">
											<label class="form-label">Fecha llamada</label>
											<input name="FechaInicioOT" type="text" class="form-control" id="FechaInicioOT" value="" placeholder="YYYY-MM-DD">
											hasta
											<input name="FechaFinalOT" type="text" class="form-control" id="FechaFinalOT" value="" placeholder="YYYY-MM-DD">
										</div>
									</div>
								</form>	
							</div>
						</div>
					</div>
				</div>
				<?php }?>
				<div id="dvResult">
				<?php if($sw==1){
				while($row_OT=sqlsrv_fetch_array($SQL_OT)){ ?>
					<div class="card card-body mt-lg-3 bg-light border-primary <?php if($row_OT['Validacion']=="OK"){echo "item-drag";}?>" style="min-height: 14rem;" data-title="<?php echo $row_OT['Etiqueta'];?>" data-docnum="<?php echo $row_OT['DocNum'];?>" data-info="<?php echo $row_OT['DeTipoLlamada'];?>" data-validacion="<?php echo $row_OT['Validacion'];?>">
						<h5 class="card-title"><a href="llamada_servicio.php?id=<?php echo base64_encode($row_OT['ID_LlamadaServicio']);?>&tl=1" target="_blank" title="Consultar Llamada de servicio" class="btn-xs btn-success fas fa-search"></a> <?php echo $row_OT['DocNum'];?></h5>
						<h6 class="card-subtitle mb-2 text-muted"><?php echo $row_OT['DeTipoLlamada'];?></h6>
						<p class="card-text mb-0 small text-primary"><?php echo $row_OT['DeArticuloLlamada'];?></p>
						<p class="card-text mb-0 small"><strong><?php echo $row_OT['NombreClienteLlamada'];?></strong></p>
						<p class="card-text mb-0 small"><span class="font-weight-bold">Sucursal:</span> <?php echo $row_OT['NombreSucursal'];?></p>
						<p class="card-text mb-0 small"><span class="font-weight-bold">Ciudad:</span> <?php echo $row_OT['CiudadLlamada'];?></p>
						<p class="card-text mb-0 small"><span class="font-weight-bold">Fecha:</span> <?php echo $row_OT['FechaLlamada']->format('Y-m-d');?></p>
						<p class="card-text mb-0 small"><span class="font-weight-bold">Servicios:</span> <?php echo $row_OT['Servicios'];?></p>
						<p class="card-text mb-0 small"><span class="font-weight-bold">??reas:</span> <?php echo substr($row_OT['Areas'],0,150);?></p>
						<p class="card-text mb-0 small"><span class="font-weight-bold">Validaci??n:</span> <span class="<?php if($row_OT['Validacion']!="OK"){echo "text-danger";}else{echo "text-success";}?>"><?php echo $row_OT['Validacion'];?></span></p>
					</div>
				<?php }
				}?>
				</div>
			</div>		
			<div class="card card-body col-lg-10">
				<div id="dv_calendar"><?php require_once('programacion_calendario.php');?></div>
			</div>
		</div>
    </div>
	<?php require('includes/pie.php');?>
<script>
var calendar;
	
$(document).ready(function() {
	$("#frmProgramacion").validate({
	 submitHandler: function(form){
		 blockUI()
		 form.submit();
		}
	});
	$(".select2").select2();

	$(".select2OT").select2({
		dropdownParent: $('#accordion1-1')
	});	

	$('#FechaInicial').flatpickr({
		 dateFormat: "Y-m-d",
		 allowInput: true
	 });

	$('#FechaFinal').flatpickr({
		 dateFormat: "Y-m-d",
		 allowInput: true
	 });

	$('#FechaInicioOT').flatpickr({
		 dateFormat: "Y-m-d",
		 allowInput: true
	 });

	$('#FechaFinalOT').flatpickr({
		 dateFormat: "Y-m-d",
		 allowInput: true
	 });

	$(function() {
	  new PerfectScrollbar(document.getElementById('dvOT'));
	});


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
				var value = $("#NombreCliente").getSelectedItemData().CodigoCliente;
				$("#Cliente").val(value).trigger("change");
			}
		}
	};

	$("#NombreCliente").easyAutocomplete(options);
	
	if(window.sessionStorage.getItem('DateAboveResources')==="false"){
		$("#chkDatesAboveResources").prop("checked", false);
	}else{
		$("#chkDatesAboveResources").prop("checked", true);
	}
	
	$("#chkDatesAboveResources").change(function(){
		if($('#chkDatesAboveResources').prop('checked')){
			window.sessionStorage.setItem('DateAboveResources',true)
			RefresarCalendario()
		}else{
			window.sessionStorage.setItem('DateAboveResources',false)
			RefresarCalendario()
		}	
	});

});	

</script>
<script>
function RefresarCalendario(){
	blockUI();
	var Tecnicos=$("#Recursos").val();
	$.ajax({
		type: "POST",
		url: "programacion_calendario.php?type=1&pSede=<?php echo $Sede;?>&pTecnicos="+Tecnicos+"&pIdEvento=<?php if($sw==1){echo $row_Evento['IdEvento'];}?>&sw=<?php echo $sw;?>&fchinicial=<?php echo $FechaInicial;?>",
		success: function(response){
			$('#dv_calendar').html(response);
			blockUI(false);
		}
	});
}

function EjecutarProceso(){
	blockUI();
	var Evento=document.getElementById("IdEvento").value;
	$.ajax({
		url:"ajx_ejecutar_json.php",
		data:{type:5,IdEvento:Evento},
		dataType:'json',
		success: function(data){
//			if(data.Estado==1){
//				
//			}
			blockUI(false);
			$("#btnGuardar").prop('disabled', true);
			$("#btnPendientes").prop('disabled', true);
			$.ajax({
				type: "POST",
//				async: false,
				url: "programacion_rutas_result.php?idEvento="+Base64.encode(Evento)+"&msg="+data.Mensaje+"&estado="+data.Estado,
				success: function(response){
					$('#ContenidoModal').html(response);
					$('#ModalAct').modal("show");
				}
			});
//			Swal.fire({
//				title: data.Title,
//				text: data.Mensaje,
//				icon:  data.Icon,
//			}).then(function(){
//				
//			});
			
		}
	});	
}

function VerificarPendientes(){
	blockUI();
	var Evento=document.getElementById("IdEvento").value;
	$.ajax({
		type: "POST",
		url: "programacion_rutas_result.php?idEvento="+Base64.encode(Evento),
		success: function(response){
			$('#ContenidoModal').html(response);
			blockUI(false);
			$('#ModalAct').modal("show");
		}
	});
}
	
function FiltrarOT(){
	blockUI();
	$("#accordion1-1").collapse('hide');
	var Evento=document.getElementById("IdEvento").value;
	var LlamadaServicio=$("#LlamadaServicio").val();
	var Series=$("#Series").val();
	var Cliente=$("#ClienteLlamada").val();
	var SucursalCliente=$("#SucursalCliente").val();
	var Servicios=$("#Servicios").val();
	var Areas=$("#Areas").val();
	var ArticuloLlamada=$("#ArticuloLlamada").val();
	var TipoLlamada=$("#TipoLlamada").val();
	var Ciudad=$("#Ciudad").val();
	var FechaInicioOT=$("#FechaInicioOT").val();
	var FechaFinalOT=$("#FechaFinalOT").val();
//	var Cliente=document.getElementById("Cliente").value;
	
	$.ajax({
		type: "POST",
		url: "programacion_rutas_OT.php?idEvento="+Evento+"&DocNum="+LlamadaServicio+"&Series="+Series+"&SucursalCliente="+btoa(SucursalCliente)+"&Servicios="+Servicios+"&Areas="+Areas+"&Articulo="+ArticuloLlamada+"&TipoLlamada="+TipoLlamada+"&Ciudad="+Ciudad+"&FechaInicio="+FechaInicioOT+"&FechaFinal="+FechaFinalOT+"&Cliente="+Cliente,
		success: function(response){
			$('#dvResult').html(response);
			$("#dvOT").scrollTop(0);
			$("html,body").scrollTop(0);
			blockUI(false);
		}
	});
}
</script>
</html>
<?php sqlsrv_close($conexion);?>