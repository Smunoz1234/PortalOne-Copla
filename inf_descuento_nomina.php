<?php require_once("includes/conexion.php");
PermitirAcceso(1207);
$sw=0;
$Empleado="";
$Area="";
$CentroCosto="";
$Sucursal="";

//Empleados
$SQL_Empleado=Seleccionar('uvw_Sap_tbl_EmpleadosSN','*','','NombreEmpleado');

//Normas de reparto (centros de costos)
$SQL_ControCosto=Seleccionar('uvw_Sap_tbl_DimensionesReparto','*','DimCode=1');

//Normas de reparto (Unidad negocio)
$SQL_UnidadNegocio=Seleccionar('uvw_Sap_tbl_DimensionesReparto','*','DimCode=2');

//Normas de reparto (Sucursal)
$SQL_Sucursal=Seleccionar('uvw_Sap_tbl_DimensionesReparto','*','DimCode=3');

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
}else{
	$FechaFinal=date('Y-m-d');
}

//Filtros

if(isset($_GET['Empleado'])&&$_GET['Empleado']!=""){
	$Empleado=$_GET['Empleado'];
	$sw=1;
}

if(isset($_GET['CentroCosto'])&&$_GET['CentroCosto']!=""){
	$CentroCosto=$_GET['CentroCosto'];
	$sw=1;
}

if(isset($_GET['Area'])&&$_GET['Area']!=""){
	$Area=$_GET['Area'];
	$sw=1;
}

if(isset($_GET['Sucursal'])&&$_GET['Sucursal']!=""){
	$Sucursal=$_GET['Sucursal'];
	$sw=1;
}

if($sw==1){
	$ParamCons=array(
		"'".FormatoFecha($FechaInicial)."'",
		"'".FormatoFecha($FechaFinal)."'",
		"'".$Empleado."'",
		"'".$Area."'",
		"'".$CentroCosto."'",
		"'".$Sucursal."'"
	);
	$SQL=EjecutarSP('usp_Inf_DescuentoNominaEPP',$ParamCons);
}
?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include_once("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Informe descuento de nómina EPP | <?php echo NOMBRE_PORTAL;?></title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<script type="text/javascript">
	$(document).ready(function() {
		
	});
</script>
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
                    <h2>Informe descuento de nómina EPP</h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="index1.php">Inicio</a>
                        </li>
                        <li>
                            <a href="#">Inventario</a>
                        </li>
                        <li>
                            <a href="#">Informes</a>
                        </li>
                        <li class="active">
                            <strong>Informe descuento de nómina EPP</strong>
                        </li>
                    </ol>
                </div>
            </div>
         <div class="wrapper wrapper-content">
             <div class="row">
				<div class="col-lg-12">
			    <div class="ibox-content">
					 <?php include("includes/spinner.php"); ?>
				  <form action="inf_descuento_nomina.php" method="get" id="formBuscar" class="form-horizontal">
					    <div class="form-group">
							<label class="col-xs-12"><h3 class="bg-success p-xs b-r-sm"><i class="fa fa-filter"></i> Datos para filtrar</h3></label>
						</div>
						<div class="form-group">
							<label class="col-lg-1 control-label">Fechas</label>
							<div class="col-lg-3">
								<div class="input-daterange input-group" id="datepicker">
									<input name="FechaInicial" type="text" class="input-sm form-control" id="FechaInicial" placeholder="Fecha inicial" value="<?php echo $FechaInicial;?>"/>
									<span class="input-group-addon">hasta</span>
									<input name="FechaFinal" type="text" class="input-sm form-control" id="FechaFinal" placeholder="Fecha final" value="<?php echo $FechaFinal;?>" />
								</div>
							</div>
							<label class="col-lg-1 control-label">Empleado</label>
							<div class="col-lg-3">
								<select name="Empleado" class="form-control m-b select2" id="Empleado">
										<option value="">(Todos)</option>
								  <?php while($row_Empleado=sqlsrv_fetch_array($SQL_Empleado)){?>
										<option value="<?php echo $row_Empleado['ID_Empleado'];?>" <?php if((isset($_GET['Empleado']))&&(strcmp($row_Empleado['ID_Empleado'],$_GET['Empleado'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Empleado['NombreEmpleado'];?></option>
								  <?php }?>
								</select>
							</div>
						</div>
					  	<div class="form-group">
							<label class="col-lg-1 control-label">Centro de costo</label>
							<div class="col-lg-3">
								<select name="CentroCosto" class="form-control m-b" id="CentroCosto">
									<option value="">(Todos)</option>
								  <?php while($row_UnidadNegocio=sqlsrv_fetch_array($SQL_UnidadNegocio)){?>
											<option value="<?php echo $row_UnidadNegocio['OcrCode'];?>" <?php if((isset($_GET['CentroCosto']))&&(strcmp($row_UnidadNegocio['OcrCode'],$_GET['CentroCosto'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_UnidadNegocio['OcrName'];?></option>
									<?php 	}?>
								</select>
							</div>
							<label class="col-lg-1 control-label">Área</label>
							<div class="col-lg-3">
								<select name="Area" class="form-control m-b" id="Area">
									<option value="">(Todos)</option>
								  <?php while($row_ControCosto=sqlsrv_fetch_array($SQL_ControCosto)){?>
										<option value="<?php echo $row_ControCosto['OcrCode'];?>" <?php if((isset($_GET['Area']))&&(strcmp($row_ControCosto['OcrCode'],$_GET['Area'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_ControCosto['OcrName'];?></option>
								  <?php }?>
								</select>
							</div>
							<label class="col-lg-1 control-label">Sucursal</label>
							<div class="col-lg-3">
								<select name="Sucursal" class="form-control m-b" id="Sucursal">
									<option value="">(Todos)</option>
								  <?php while($row_Sucursal=sqlsrv_fetch_array($SQL_Sucursal)){?>
											<option value="<?php echo $row_Sucursal['OcrCode'];?>" <?php if(isset($_GET['Sucursal'])&&(strcmp($row_Sucursal['OcrCode'],$_GET['Sucursal'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Sucursal['OcrName'];?></option>
									<?php }?>
								</select>
							</div>
						</div>
					  	<div class="form-group">
							<div class="col-lg-12">
								<button type="submit" class="btn btn-outline btn-success pull-right"><i class="fa fa-search"></i> Buscar</button>
							</div>
						</div>
					  	<?php if($sw==1){?>
					  	<div class="form-group">
							<div class="col-lg-10 col-md-10">
								<a href="exportar_excel.php?exp=10&Cons=<?php echo base64_encode(implode(",",$ParamCons));?>&sp=<?php echo base64_encode('usp_Inf_DescuentoNominaEPP');?>">
									<img src="css/exp_excel.png" width="50" height="30" alt="Exportar a Excel" title="Exportar a Excel"/>
								</a>
							</div>
						</div>
					  	<?php }?>
				 </form>
			</div>
			</div>
		  </div>
         <br>
			 <?php //echo $Cons;?>
          <div class="row">
           <div class="col-lg-12">
			    <div class="ibox-content">
					 <?php include("includes/spinner.php"); ?>
			<div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover dataTables-example" >
                    <thead>
                    <tr>
                        <th>Documento</th>
						<th>Empleado</th>
						<th>Valor insumos</th>						
						<th>Fecha inicio cuota</th>
						<th>Valor cuota</th>
						<th>Observaciones</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
						if($sw==1){
						while($row=sqlsrv_fetch_array($SQL)){ ?>
							<tr class="gradeX">
								<td><?php echo $row['NumeroDocumento'];?></td>
								<td><?php echo $row['NombreEmpleado'];?></td>
								<td><?php echo number_format($row['SaldoInicial'],2);?></td>
								<td><?php echo $row['FechaInicioCuota'];?></td>
								<td><?php echo number_format($row['ValorCuota'],2);?></td>
								<td><?php echo $row['Observaciones'];?></td>
							</tr>
					<?php }
						}?>
                    </tbody>
                    </table>
              </div>
			</div>
			 </div> 
          </div>
        </div>
        <!-- InstanceEndEditable -->
        <?php include_once("includes/footer.php"); ?>

    </div>
</div>
<?php include_once("includes/pie.php"); ?>
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
			 $(".select2").select2();
			 $('#FechaInicial').datepicker({
                todayBtn: "linked",
                keyboardNavigation: false,
                forceParse: false,
                calendarWeeks: true,
                autoclose: true,
				format: 'yyyy-mm-dd',
				todayHighlight: true,
            });
			 $('#FechaFinal').datepicker({
                todayBtn: "linked",
                keyboardNavigation: false,
                forceParse: false,
                calendarWeeks: true,
                autoclose: true,
				format: 'yyyy-mm-dd',
				todayHighlight: true,
            }); 
			
			$('.chosen-select').chosen({width: "100%"});
			
            $('.dataTables-example').DataTable({
                pageLength: 25,
                responsive: false,
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

    </script>
<!-- InstanceEndEditable -->
</body>

<!-- InstanceEnd --></html>
<?php sqlsrv_close($conexion);?>