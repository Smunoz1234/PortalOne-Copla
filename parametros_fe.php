<?php require_once("includes/conexion.php");
//require_once("includes/conexion_hn.php");
if(PermitirAcceso(1501))

$sw_ext=0;//Sw que permite saber si la ventana esta abierta en modo pop-up. Si es así, no cargo el menú ni el menú superior.
$sw_error=0;//Sw para saber si ha ocurrido un error al crear o actualizar un articulo.
$edit=1;

if(isset($_GET['id'])&&($_GET['id']!="")){
	$IdItemCode=base64_decode($_GET['id']);
}
	
if(isset($_GET['ext'])&&($_GET['ext']==1)){
	$sw_ext=1;//Se está abriendo como pop-up
}elseif(isset($_POST['ext'])&&($_POST['ext']==1)){
	$sw_ext=1;//Se está abriendo como pop-up
}else{
	$sw_ext=0;
}

if(isset($_POST['P'])&&($_POST['P']!="")){//Insertar o actualizar articulo
	try{
		$Type=2;//Ejecutar actualizar en el SP
		
		if($_POST['ID']==""){
			$Type=1;
		}
		
		if(isset($_POST['chkEnvioComprobantes'])&&($_POST['chkEnvioComprobantes']==1)){
			$chkEnvioComprobantes=1;
		}else{
			$chkEnvioComprobantes=0;
		}
					
		$ParamFE=array(
			"'".$_POST['ID']."'",
			"'".$_POST['ProvTecnologico']."'",
			"'".$_POST['CodPais']."'",
			"'".$_POST['TipoAmbiente']."'",
			"'".$_POST['TipoEsquema']."'",
			"'".$_POST['RutaPrueba']."'",
			"'".$_POST['RutaProd']."'",
			"'".$_POST['RutaArchivos']."'",
			$chkEnvioComprobantes,
			"'".$_POST['URLServicio']."'",
			"'".$_POST['Usuario']."'",
			"'".$_POST['Password']."'",
			"'".$_POST['Contrato']."'",
			"'".$_POST['XWho']."'",
			"'".$_POST['VerificarPersona']."'",
			"'".$_POST['ExcluirDescuento']."'",
			"'".$_POST['EnviarRepGrafica']."'",
			"'".$_POST['EnviarAdjuntos']."'",
			"'".$_SESSION['CodUser']."'",
			$Type
		);
		$SQL_FE=EjecutarSP('sp_tbl_FacturacionElectronica_Parametros',$ParamFE,$_POST['P']);
		if($SQL_FE){
			header('Location:parametros_fe.php?a='.base64_encode("OK_FEUpd"));
		}else{
			$sw_error=1;
			$msg_error="Error al actualizar la información";
		}						
	}catch (Exception $e) {
		$sw_error=1;
		//echo 'Excepcion capturada 2: ',  $e->getMessage(), "\n";
	}	
}

if($edit==1){//Editar parametros	

	//Datos
	$SQL=Seleccionar('uvw_tbl_FacturacionElectronica_Parametros','*');
	$row=sqlsrv_fetch_array($SQL);
		
}
if($sw_error==1){//Si ocurre un error
	
	//Datos
	$SQL=Seleccionar('uvw_tbl_FacturacionElectronica_Parametros','*');
	$row=sqlsrv_fetch_array($SQL);
		
}

?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Parámetros Facturación Electrónica | <?php echo NOMBRE_PORTAL;?></title>
<?php 
if(isset($_GET['a'])&&($_GET['a']==base64_encode("OK_FEUpd"))){
	echo "<script>
		$(document).ready(function() {
			swal({
                title: '¡Listo!',
                text: 'Datos actualizados exitosamente.',
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
                text: '".LSiqmlObs($msg_error)."',
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
                    <h2>Parámetros Facturación Electrónica</h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="index1.php">Inicio</a>
                        </li>
                        <li>
                            <a href="#">Administración</a>
                        </li>
                        <li class="active">
                            <strong>Parámetros Facturación Electrónica</strong>
                        </li>
                    </ol>
                </div>
            </div>
           
         <div class="wrapper wrapper-content">
			 <form action="parametros_fe.php" method="post" class="form-horizontal" enctype="multipart/form-data" id="FrmFE">
			 <div class="row">
				<div class="col-lg-12">   		
					<div class="ibox-content">
						<?php include("includes/spinner.php"); ?>
						<div class="form-group">
							<div class="col-lg-4">
								<?php 
									$return="index1.php";
								?>
								<button class="btn btn-warning" type="submit" id="Actualizar"><i class="fa fa-refresh"></i> Actualizar</button>
								<a href="<?php echo $return;?>" class="alkin btn btn-outline btn-default"><i class="fa fa-arrow-circle-o-left"></i> Regresar</a>
							</div>
							<div class="col-lg-4"></div>
							<div class="col-lg-2">
								<div class="form-group border">
									<div class="p-xs">
										<label class="text-muted">Última actualización</label>
										<div class="font-bold"><?php if($edit==1){echo $row['NombreUsuarioActualizacion'];}?></div>
									</div>
								</div>
							</div>
							<div class="col-lg-2">
								<div class="form-group border">
									<div class="p-xs">
										<label class="text-muted">Fecha</label>
										<div class="font-bold"><?php if($edit==1){if($row['FechaActualizacion']!=""){echo $row['FechaActualizacion']->format('Y-m-d');}}?></div>
									</div>
								</div>
							</div>
						</div>
						<input type="hidden" id="P" name="P" value="56" />
						<input type="hidden" id="ID" name="ID" value="<?php if($edit==1){echo $row['ID'];}?>" />
						<input type="hidden" id="return" name="return" value="<?php echo base64_encode($return);?>" />
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
								<li class="active"><a data-toggle="tab" href="#tabSN-1"><i class="fa fa-database"></i> Parámetros</a></li>
								<li><a data-toggle="tab" href="#tabSN-2"><i class="fa fa-user"></i> Proveedor tecnológico</a></li>
							</ul>
						   <div class="tab-content">
							   <div id="tabSN-1" class="tab-pane active">
								   <br>
								   <div class="form-group">
										<label class="col-lg-1 control-label">Proveedor técnologico</label>
										<div class="col-lg-3">
											<select name="ProvTecnologico" class="form-control m-b" id="ProvTecnologico" required>
												<option value="Facture">Facture SAS</option>
											</select>
										</div>
									</div>
									<div class="form-group">
										<label class="col-lg-1 control-label">Código pais</label>
										<div class="col-lg-3">
											<input name="CodPais" autofocus="autofocus" type="text" required class="form-control" id="CodPais" value="<?php if($edit==1){echo $row['CodPais'];}?>">
										</div>
										<label class="col-lg-1 control-label">Tipo ambiente</label>
										<div class="col-lg-3">
											<select name="TipoAmbiente" class="form-control m-b" id="TipoAmbiente" required>
												<option value="1" <?php if(($edit==1)&&($row['TipoAmbiente']=="1")){ echo "selected=\"selected\"";}?>>Producción</option>
												<option value="2" <?php if(($edit==1)&&($row['TipoAmbiente']=="2")){ echo "selected=\"selected\"";}?>>Pruebas</option>
											</select>
										</div>
										<label class="col-lg-1 control-label">Tipo esquema</label>
										<div class="col-lg-3">
											<select name="TipoEsquema" class="form-control m-b" id="TipoEsquema" required>
												<option value="1" <?php if(($edit==1)&&($row['TipoEsquema']=="1")){ echo "selected=\"selected\"";}?>>Online</option>
												<option value="2" <?php if(($edit==1)&&($row['TipoEsquema']=="2")){ echo "selected=\"selected\"";}?>>Offline</option>
											</select>
										</div>
									</div>
									<div class="form-group">
										<label class="col-lg-1 control-label">Ruta de prueba</label>
										<div class="col-lg-5">
											<input type="text" class="form-control" name="RutaPrueba" id="RutaPrueba" required value="<?php if($edit==1){ echo $row['RutaPrueba'];}?>">
										</div>
									</div>
									<div class="form-group">
										<label class="col-lg-1 control-label">Ruta de producción</label>
										<div class="col-lg-5">
											<input type="text" class="form-control" name="RutaProd" id="RutaProd" value="<?php if($edit==1){echo $row['RutaProd'];}?>">
										</div>
									</div>
								   <div class="form-group">
										<label class="col-lg-1 control-label">Ruta de archivos FE y XML</label>
										<div class="col-lg-5">
											<input type="text" class="form-control" name="RutaArchivos" id="RutaArchivos" value="<?php if($edit==1){echo $row['RutaArchivos'];}?>">
										</div>
									</div>
									<div class="form-group">
										<label class="col-lg-1 control-label">Comprobantes</label>
										<div class="col-lg-6">
											<label class="checkbox-inline i-checks"><input name="chkEnvioComprobantes" id="chkEnvioComprobantes" type="checkbox" value="1" <?php if($edit==1){if($row['EnvioComprobantes']==1){echo "checked=\"checked\"";}}?>> Generar comprobantes electrónicos automáticamente</label>
										</div>
									</div>
							   </div>
							   <div id="tabSN-2" class="tab-pane">
								<div class="panel-body">
									<div class="form-group">
										<label class="col-lg-1 control-label">URL servicio</label>
										<div class="col-lg-5">
											<input type="text" class="form-control" name="URLServicio" id="URLServicio" value="<?php if($edit==1){echo $row['URLServicio'];}?>">
										</div>
									</div>
									<div class="form-group">
										<label class="col-lg-1 control-label">Usuario</label>
										<div class="col-lg-2">
											<input type="text" class="form-control" name="Usuario" id="Usuario" value="<?php if($edit==1){echo $row['Usuario'];}?>">
										</div>
										<label class="col-lg-1 control-label">Contraseña</label>
										<div class="col-lg-2">
											<input type="text" class="form-control" name="Password" id="Password" value="<?php if($edit==1){echo $row['Password'];}?>">
										</div>
									</div>
									<div class="form-group">
										<label class="col-lg-1 control-label">Contrato</label>
										<div class="col-lg-2">
											<input type="text" class="form-control" name="Contrato" id="Contrato" value="<?php if($edit==1){echo $row['Contrato'];}?>">
										</div>
										<label class="col-lg-1 control-label">XWho</label>
										<div class="col-lg-2">
											<input type="text" class="form-control" name="XWho" id="XWho" value="<?php if($edit==1){echo $row['XWho'];}?>">
										</div>
									</div>
									<div class="form-group">
										<label class="col-lg-1 control-label">Verificar persona de contacto</label>
										<div class="col-lg-2">
											<select name="VerificarPersona" class="form-control m-b" id="VerificarPersona" required>
												<option value="SI" <?php if(($edit==1)&&($row['VerificarPersona']=="SI")){ echo "selected=\"selected\"";}?>>SI</option>
												<option value="NO" <?php if(($edit==1)&&($row['VerificarPersona']=="NO")){ echo "selected=\"selected\"";}?>>NO</option>
											</select>
										</div>
										<label class="col-lg-1 control-label">Excluir descuento linea</label>
										<div class="col-lg-2">
											<select name="ExcluirDescuento" class="form-control m-b" id="ExcluirDescuento" required>
												<option value="SI" <?php if(($edit==1)&&($row['ExcluirDescuento']=="SI")){ echo "selected=\"selected\"";}?>>SI</option>
												<option value="NO" <?php if(($edit==1)&&($row['ExcluirDescuento']=="NO")){ echo "selected=\"selected\"";}?>>NO</option>
											</select>
										</div>
									</div>
									<div class="form-group">
										<label class="col-lg-1 control-label">Enviar representación gráfica</label>
										<div class="col-lg-2">
											<select name="EnviarRepGrafica" class="form-control m-b" id="EnviarRepGrafica" required>
												<option value="SI" <?php if(($edit==1)&&($row['EnviarRepGrafica']=="SI")){ echo "selected=\"selected\"";}?>>SI</option>
												<option value="NO" <?php if(($edit==1)&&($row['EnviarRepGrafica']=="NO")){ echo "selected=\"selected\"";}?>>NO</option>
											</select>
										</div>
										<label class="col-lg-1 control-label">Enviar adjuntos</label>
										<div class="col-lg-2">
											<select name="EnviarAdjuntos" class="form-control m-b" id="EnviarAdjuntos" required>
												<option value="SI" <?php if(($edit==1)&&($row['EnviarAdjuntos']=="SI")){ echo "selected=\"selected\"";}?>>SI</option>
												<option value="NO" <?php if(($edit==1)&&($row['EnviarAdjuntos']=="NO")){ echo "selected=\"selected\"";}?>>NO</option>
											</select>
										</div>
									</div>
								</div>										   
							   </div>							  
						   </div>
						 </div>
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
								<li class="active"><a data-toggle="tab" href="#tabSerie-1"><i class="fa fa-list"></i> Series</a></li>
							</ul>
						   <div class="tab-content">
							   <div id="tabSerie-1" class="tab-pane active">
								  <iframe id="DataGrid" name="DataGrid" style="border: 0;" width="100%" height="300" src="detalle_parametros_fe.php"></iframe>	
							   </div>					  
						   </div>
						 </div>
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
	 $("#FrmFE ").validate({
		 submitHandler: function(form){
			 $('.ibox-content').toggleClass('sk-loading');
			 form.submit();	 
		}
	});
   $(".alkin").on('click', function(){
	   $('.ibox-content').toggleClass('sk-loading');
	});
	 
	$(".select2").select2();
	 
 	$('.i-checks').iCheck({
		checkboxClass: 'icheckbox_square-green',
		radioClass: 'iradio_square-green',
	});
	 
	$(".btn_del").each(function (el){
		$(this).bind("click",delRow);
	});
	 
 });
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