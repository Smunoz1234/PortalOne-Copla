<?php require_once("includes/conexion.php");
PermitirAcceso(311);

$sw=0;

if(isset($_GET['Anno'])&&($_GET['Anno']!="")){
	$Anno=$_GET['Anno'];
	$sw=1;
}else{
	$Anno=date('Y');
}

//Sucursal
$ParamSucursal=array(
	"'".$_SESSION['CodUser']."'"
);
$SQL_Sucursal=EjecutarSP('sp_ConsultarSucursalesUsuario',$ParamSucursal);

if(isset($_GET['Sucursal'])&&$_GET['Sucursal']!=""){
	//Serie de llamada
	$ParamSerieOT=array(
		"'".$_GET['Sucursal']."'",
		"'191'"
	);
	$SQL_SeriesOT=EjecutarSP('sp_ConsultarSeriesSucursales',$ParamSerieOT);

	$ParamSerieOV=array(
		"'".$_GET['Sucursal']."'",
		"'17'"
	);
	$SQL_SeriesOV=EjecutarSP('sp_ConsultarSeriesSucursales',$ParamSerieOV);
}


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
	$FechaFinal=date('Y-m-d');
}

if($sw==1){
	$Param=array(
		$Anno,
		"'".FormatoFecha($FechaInicial)."'",
		"'".FormatoFecha($FechaFinal)."'",
		"'".$_GET['Sucursal']."'",
		"'".$_GET['Cliente']."'",
		"'".strtolower($_SESSION['User'])."'",
		"'".$_GET['ValidarOT']."'"
	);
	$SQL=EjecutarSP('usp_tbl_CreacionProgramaOrdenesServicio',$Param);
	$row=sqlsrv_fetch_array($SQL);
}
?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Creación de OT en lote | <?php echo NOMBRE_PORTAL;?></title>
	<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<style>
.select2-container{ width: 100% !important; }
</style>
<script type="text/javascript">
	$(document).ready(function() {
		$("#NombreCliente").change(function(){
			var NomCliente=document.getElementById("NombreCliente");
			var Cliente=document.getElementById("Cliente");
			if(NomCliente.value==""){
				Cliente.value="";
			}	
		});	
		
		$("#Sucursal").change(function(){
			$('.ibox-content').toggleClass('sk-loading',true);
			var Sucursal=document.getElementById('Sucursal').value;
			$.ajax({
				type: "POST",
				url: "ajx_cbo_select.php?type=26&id="+Sucursal+"&tdoc=191",
				success: function(response){
					$('#SeriesOT').html(response).fadeIn();
					$('.ibox-content').toggleClass('sk-loading',false);
				}
			});	
			$.ajax({
				type: "POST",
				url: "ajx_cbo_select.php?type=26&id="+Sucursal+"&tdoc=17",
				success: function(response){
					$('#SeriesOV').html(response).fadeIn();
					$('.ibox-content').toggleClass('sk-loading',false);
				}
			});	
		});
		
	});	
</script>
<script>

function Validar(Tipo){
	swal({
		title: "¿Está seguro que desea ejecutar el proceso?",
		text: "Se crearán los documentos en lote",
		type: "info",
		showCancelButton: true,
		closeOnConfirm: true,
		confirmButtonText: "Si, confirmo",
		cancelButtonText: "No"
	},
	function(isConfirm){
		if(isConfirm){
			$('.ibox-content').toggleClass('sk-loading',true);
			$.ajax({
				url:"ajx_buscar_datos_json.php",
				data:{
					type:28,
					doc:Tipo
				},
				dataType:'json',
				success: function(data){
					if(data.Result==1){
						$('.ibox-content').toggleClass('sk-loading',false);
						swal({
							title: data.Msg,
							//text: "Se crearán los documentos en lote",
							type: "warning",
							showCancelButton: true,
							closeOnConfirm: true,
							confirmButtonText: "Si, confirmo",
							cancelButtonText: "No"
						},
						function(isConfirm){
							if(isConfirm){
								EjecutarProceso(Tipo);								
							}
						});
					}else if(data.Result==0){
						EjecutarProceso(Tipo);
					}		
				}
			});
		}
	});	
}

function EjecutarProceso(Tipo){
	$('.ibox-content').toggleClass('sk-loading',true);
	var Evento = document.getElementById("IdEvento").value;
	var FechaInicial = document.getElementById("FechaInicial").value;
	var FechaFinal = document.getElementById("FechaFinal").value;
	var Anno = document.getElementById("Anno").value;
	var Cliente = document.getElementById("Cliente").value;
	var Sucursal = document.getElementById("Sucursal").value;
	var SeriesOT = document.getElementById("SeriesOT").value;
	var SeriesOV = document.getElementById("SeriesOV").value;
	var DGDetalle = document.getElementById("DGDetalle");	
	
	$.ajax({
		url:"ajx_ejecutar_json.php",
		data:{
			type:3,
			Evento:Evento,
			FechaInicial:FechaInicial,
			FechaFinal:FechaFinal,
			Anno:Anno,
			Cliente:Cliente,
			Sucursal:Sucursal,
			SeriesOT:SeriesOT,
			SeriesOV:SeriesOV,
			Tipo:Tipo
		},
		dataType:'json',
		success: function(data){
			if(data.Estado==1){
				$("#UltEjecucion").html(MostrarFechaHora());				
				DGDetalle.src="detalle_creacion_ot_lote.php";				
			}
			swal({
				title: data.Title,
				text: data.Mensaje,
				type: data.Icon,
			});
			$('.ibox-content').toggleClass('sk-loading',false);
		}
	});
	
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
                    <h2>Creación de OT en lote</h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="index1.php">Inicio</a>
                        </li>
                        <li>
                            <a href="#">Servicios</a>
                        </li>
						 <li>
                            <a href="#">Asistentes</a>
                        </li>
                        <li class="active">
                            <strong>Creación de OT en lote</strong>
                        </li>
                    </ol>
                </div>
            </div>
         <div class="wrapper wrapper-content">
             <div class="row">
				<div class="col-lg-12">
			    <div class="ibox-content">
					 <?php include("includes/spinner.php"); ?>
				  <form action="creacion_ot_lote.php" method="get" id="formBuscar" class="form-horizontal">
						<div class="form-group">
							 <div class="form-group">
								<label class="col-xs-12"><h3 class="bg-muted p-xs b-r-sm"><i class="fa fa-filter"></i> Datos para filtrar</h3></label>
							  </div>
							<label class="col-lg-1 control-label">Fechas</label>
							<div class="col-lg-3">
								<div class="input-daterange input-group" id="datepicker">
									<input name="FechaInicial" type="text" class="input-sm form-control" id="FechaInicial" placeholder="Fecha inicial" value="<?php echo $FechaInicial;?>"/>
									<span class="input-group-addon">hasta</span>
									<input name="FechaFinal" type="text" class="input-sm form-control" id="FechaFinal" placeholder="Fecha final" value="<?php echo $FechaFinal;?>" />
								</div>
							</div>
							<label class="col-lg-1 control-label">Cliente</label>
							<div class="col-lg-3">
								<input name="Cliente" type="hidden" id="Cliente" value="<?php if(isset($_GET['Cliente'])&&($_GET['Cliente']!="")){ echo $_GET['Cliente'];}?>">
								<input name="NombreCliente" type="text" class="form-control" id="NombreCliente" placeholder="Ingrese para buscar..." value="<?php if(isset($_GET['NombreCliente'])&&($_GET['NombreCliente']!="")){ echo $_GET['NombreCliente'];}?>" required>
							</div>
							<label class="col-lg-1 control-label">Año</label>
							<div class="col-lg-2">
								<select name="Anno" required class="form-control" id="Anno">
									<option value="2019" <?php if((isset($Anno))&&(strcmp(2019,$Anno)==0)){ echo "selected=\"selected\"";}?>>2019</option>
									<option value="2020" <?php if((isset($Anno))&&(strcmp(2020,$Anno)==0)){ echo "selected=\"selected\"";}?>>2020</option>
									<option value="2021" <?php if((isset($Anno))&&(strcmp(2021,$Anno)==0)){ echo "selected=\"selected\"";}?>>2021</option>
								</select>
							</div>						
						</div>
					 	<div class="form-group">
							<label class="col-lg-1 control-label">Sucursal</label>
							<div class="col-lg-3">
							<select name="Sucursal" class="form-control" id="Sucursal" required>
								<option value="">Seleccione...</option>
							  <?php while($row_Sucursal=sqlsrv_fetch_array($SQL_Sucursal)){?>
									<option value="<?php echo $row_Sucursal['IdSucursal'];?>" <?php if(isset($_GET['Sucursal'])&&(strcmp($row_Sucursal['IdSucursal'],$_GET['Sucursal'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Sucursal['DeSucursal'];?></option>
								<?php }?>
							</select>
							</div>	
							<label class="col-lg-1 control-label">Serie OT</label>
							<div class="col-lg-3">
								<select name="SeriesOT" class="form-control" id="SeriesOT" required>
										<option value="">Seleccione...</option>
								  <?php if($sw==1){
											while($row_SeriesOT=sqlsrv_fetch_array($SQL_SeriesOT)){?>
											<option value="<?php echo $row_SeriesOT['IdSeries'];?>" <?php if((isset($_GET['SeriesOT']))&&(strcmp($row_SeriesOT['IdSeries'],$_GET['SeriesOT'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_SeriesOT['DeSeries'];?></option>
								  <?php 	}
										}?>
								</select>
							</div>
							<label class="col-lg-1 control-label">Serie OV</label>
							<div class="col-lg-2">
								<select name="SeriesOV" class="form-control" id="SeriesOV" required>
										<option value="">Seleccione...</option>
								  <?php while($row_SeriesOV=sqlsrv_fetch_array($SQL_SeriesOV)){?>
										<option value="<?php echo $row_SeriesOV['IdSeries'];?>" <?php if((isset($_GET['SeriesOV']))&&(strcmp($row_SeriesOV['IdSeries'],$_GET['SeriesOV'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_SeriesOV['DeSeries'];?></option>
								  <?php }?>
								</select>
							</div>
						</div>
					  	<div class="form-group">
							<label class="col-lg-1 control-label">Validación de OT</label>
							<div class="col-lg-3">
								<select name="ValidarOT" class="form-control" id="ValidarOT">
									<option value="0" <?php if((isset($_GET['ValidarOT']))&&(strcmp(0,$_GET['ValidarOT'])==0)){ echo "selected=\"selected\"";}?>>Mostrar todas</option>
									<option value="1" <?php if((isset($_GET['ValidarOT']))&&(strcmp(1,$_GET['ValidarOT'])==0)){ echo "selected=\"selected\"";}?>>Mostrar registros sin OT</option>
									<option value="2" <?php if((isset($_GET['ValidarOT']))&&(strcmp(2,$_GET['ValidarOT'])==0)){ echo "selected=\"selected\"";}?>>Mostrar registros con OT, pero sin OV</option>
								</select>
							</div>
							<div class="col-lg-8">
								<button type="submit" class="btn btn-outline btn-success pull-right"><i class="fa fa-search"></i> Buscar</button>
							</div>
						</div>
				 </form>
			</div>
			</div>
		  </div>
         <br>
		<?php if($sw==1){?>	 
		 <div class="row">
           <div class="col-lg-12">
			    <div class="ibox-content">
					 <?php include("includes/spinner.php"); ?>
					<div class="row">
						<div class="col-lg-3">
							<button class="btn btn-primary btn-lg" type="button" id="CrearLlamadas" onClick="Validar('1');"><i class="fa fa-play-circle"></i> 1. Crear llamadas de servicio</button>
						</div>
						<div class="col-lg-5">
							<button class="btn btn-success btn-lg" type="button" id="CrearOrdenes" onClick="Validar('2');"><i class="fa fa-play-circle"></i> 2. Crear Ordenes de venta</button>
							<input type="hidden" id="IdEvento" value="<?php if(isset($row['IdEvento'])){echo $row['IdEvento'];}?>" />
						</div>				
						<div class="col-lg-2">
							<div class="form-group border">
								<div class="p-xs">
									<label class="text-muted">Última validación</label>
									<div class="font-bold"><?php echo date('Y-m-d H:i');?></div>
								</div>
							</div>
						</div>
						<div class="col-lg-2">
							<div class="form-group border">
								<div class="p-xs">
									<label class="text-muted">Última ejecución</label>
									<div id="UltEjecucion" class="font-bold">&nbsp;</div>
								</div>
							</div>
						</div>
					</div>
					<div class="tabs-container">  
						<ul class="nav nav-tabs">
							<li class="active"><a data-toggle="tab" href="#tab-1"><i class="fa fa-list"></i> Contenido</a></li>
							<li><span class="TimeAct"><div id="TimeAct">&nbsp;</div></span></li>
						</ul>
						<div class="tab-content">
							<div id="tab-1" class="tab-pane active">
								<iframe id="DGDetalle" name="DGDetalle" style="border: 0;" width="100%" height="700" src="detalle_creacion_ot_lote.php"></iframe>
							</div>
						</div>					
					</div>
				</div>
			</div>			
          </div>	
		 <?php }?>
        </div>
        <!-- InstanceEndEditable -->
        <?php include("includes/footer.php"); ?>

    </div>
</div>
<?php include("includes/pie.php"); ?>
<!-- InstanceBeginEditable name="EditRegion4" -->
 <script>
        $(document).ready(function(){
			$("#formBuscar").validate({
			 submitHandler: function(form){
				 $('.ibox-content').toggleClass('sk-loading');
				 form.submit();
				}
			});
			 $(".alkin").on('click', function(){
					$('.ibox-content').toggleClass('sk-loading');
				});
			 $('#FechaInicial').datepicker({
                todayBtn: "linked",
                keyboardNavigation: false,
                forceParse: false,
                calendarWeeks: true,
                autoclose: true,
				format: 'yyyy-mm-dd'
            });
			 $('#FechaFinal').datepicker({
                todayBtn: "linked",
                keyboardNavigation: false,
                forceParse: false,
                calendarWeeks: true,
                autoclose: true,
				format: 'yyyy-mm-dd'
            }); 
			
			$('.chosen-select').chosen({width: "100%"});
			
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
			
            $('.dataTables-example').DataTable({
                pageLength: 25,
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