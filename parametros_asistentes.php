<?php 
require_once("includes/conexion.php");
PermitirAcceso(215);

//Rutas
$SQL_Rutas=Seleccionar("tbl_Parametros_Asistentes","*","TipoAsistente=1");

//Creacion de OT
$SQL_CreacionOT=Seleccionar("tbl_Parametros_Asistentes","*","TipoAsistente=2");

//OT de mantenimiento
$SQL_MantOT=Seleccionar("tbl_Parametros_Asistentes","*","TipoAsistente=3");

$sw_error=0;

//Insertar datos
if(isset($_POST['P'])&&($_POST['P']!="")){
	try{	
		$SQL_Campos=Seleccionar("tbl_Parametros_Asistentes","*");
		while($row_Campos=sqlsrv_fetch_array($SQL_Campos)){
			$Param=array(
				"'".$row_Campos['NombreCampo']."'",
				"'".$_POST[$row_Campos['NombreCampo']]."'",
				"'".$_SESSION['CodUser']."'"
			);

			$SQL_Param=EjecutarSP("sp_tbl_Parametros_Asistentes",$Param);

			if(!$SQL_Param){
				$sw_error=1;
				$msg_error="Error al actualizar la información";
			}
		}
		
		if($sw_error==0){
			header('Location:parametros_asistentes.php?a='.base64_encode("OK_PRUpd"));
		}
	}catch (Exception $e) {
		$sw_error=1;
		$msg_error=$e->getMessage();
	}	
	
}

?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include_once("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Parámetros asistentes | <?php echo NOMBRE_PORTAL;?></title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<?php 
if(isset($_GET['a'])&&($_GET['a']==base64_encode("OK_PRUpd"))){
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
</head>

<body>

<div id="wrapper">

    <?php include_once("includes/menu.php"); ?>

    <div id="page-wrapper" class="gray-bg">
        <?php include_once("includes/menu_superior.php"); ?>
        <!-- InstanceBeginEditable name="Contenido" -->
        <div class="row wrapper border-bottom white-bg page-heading">
                <div class="col-sm-8">
                    <h2>Parámetros asistentes</h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="index1.php">Inicio</a>
                        </li>
						<li>
                            <a href="#">Administración</a>
                        </li>
                        <li class="active">
                            <strong>Parámetros asistentes</strong>
                        </li>
                    </ol>
                </div>
            </div>
            <?php  //echo $Cons;?>
         <div class="wrapper wrapper-content">
			 <form action="parametros_asistentes.php" method="post" id="frmParam" class="form-horizontal">			 
			 <div class="row">
				<div class="col-lg-12">   		
					<div class="ibox-content">
						<?php include("includes/spinner.php"); ?>
						<div class="form-group">
							<div class="col-lg-2">
								<button class="btn btn-primary" type="submit" id="Guardar"><i class="fa fa-check"></i> Guardar datos</button>  
							</div>
						</div>
					  	<input type="hidden" id="P" name="P" value="frmParam" />
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
								<li class="active"><a data-toggle="tab" href="#tab-1"><i class="fa fa-calendar"></i> Rutero</a></li>
								<li><a data-toggle="tab" href="#tab-2"><i class="fa fa-tasks"></i> Creación de OT</a></li>
								<li><a data-toggle="tab" href="#tab-3"><i class="fa fa-wrench"></i> OT para mantenimiento</a></li>								
							</ul>
							<div class="tab-content">
								<div id="tab-1" class="tab-pane active">
									<br>
									<div class="form-group">
										<label class="col-lg-12"><h3 class="bg-muted p-xs b-r-sm"><i class="fa fa-check-square-o"></i> Lista de parámetros de rutero</h3></label>
									</div>
									<?php
										while($row_Rutas=sqlsrv_fetch_array($SQL_Rutas)){?>
										<div class="form-group">
											<label class="col-lg-2 control-label"><?php echo $row_Rutas['Label'];?></label>
											<div class="col-lg-3">
												<input name="<?php echo $row_Rutas['NombreCampo'];?>" type="text" class="form-control" id="<?php echo $row_Rutas['NombreCampo'];?>" maxlength="100" value="<?php echo $row_Rutas['Valor'];?>">
											</div>
										</div>
									<?php }?>
								</div>
								<div id="tab-2" class="tab-pane">
									<br>
									<div class="form-group">
										<label class="col-lg-12"><h3 class="bg-muted p-xs b-r-sm"><i class="fa fa-check-square-o"></i> Lista de parámetros de creación de OT</h3></label>
									</div>
									<?php
										while($row_CreacionOT=sqlsrv_fetch_array($SQL_CreacionOT)){?>
										<div class="form-group">
											<label class="col-lg-2 control-label"><?php echo $row_CreacionOT['Label'];?></label>
											<div class="col-lg-3">
												<input name="<?php echo $row_CreacionOT['NombreCampo'];?>" type="text" class="form-control" id="<?php echo $row_CreacionOT['NombreCampo'];?>" maxlength="100" value="<?php echo $row_CreacionOT['Valor'];?>">
											</div>
										</div>					  					   
									<?php }?>
								</div>
								<div id="tab-3" class="tab-pane">
									<br>
									<div class="form-group">
										<label class="col-lg-12"><h3 class="bg-muted p-xs b-r-sm"><i class="fa fa-check-square-o"></i> Lista de parámetros de OT de mantenimiento</h3></label>
									</div>
									<?php
										while($row_MantOT=sqlsrv_fetch_array($SQL_MantOT)){?>
										<div class="form-group">
											<label class="col-lg-2 control-label"><?php echo $row_MantOT['Label'];?></label>
											<div class="col-lg-3">
												<input name="<?php echo $row_MantOT['NombreCampo'];?>" type="text" class="form-control" id="<?php echo $row_MantOT['NombreCampo'];?>" maxlength="100" value="<?php echo $row_MantOT['Valor'];?>">
											</div>
										</div>					  					   
									<?php }?>
								</div>
							</div>
						 </div>
					</div>
          		</div>
			 </div>
		</form>	 
        </div>
        <!-- InstanceEndEditable -->
        <?php include_once("includes/footer.php"); ?>

    </div>
</div>
<?php include_once("includes/pie.php"); ?>
<!-- InstanceBeginEditable name="EditRegion4" -->
 <script>
        $(document).ready(function(){
			$("#frmParam").validate({
				 submitHandler: function(form){
					 $('.ibox-content').toggleClass('sk-loading');
					 form.submit();	 
				}
			});
			$(".select2").select2();
			$('.i-checks').iCheck({
				 checkboxClass: 'icheckbox_square-green',
				 radioClass: 'iradio_square-green',
			  });			
        });
    </script>
<!-- InstanceEndEditable -->
</body>

<!-- InstanceEnd --></html>
<?php sqlsrv_close($conexion);?>