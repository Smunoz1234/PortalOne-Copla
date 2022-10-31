<?php require_once("includes/conexion.php");
PermitirAcceso(1207);
$sw=0;
$Empleado="";
$Area="";
$CentroCosto="";
$Sucursal="";
$Articulo="";
$TipoEntrega="";
$AnioEntrega="";
$DescontEntrega="";

//Empleados
$SQL_Empleado=Seleccionar('uvw_Sap_tbl_EmpleadosSN','*','','NombreEmpleado');

//Normas de reparto (centros de costos)
$SQL_ControCosto=Seleccionar('uvw_Sap_tbl_DimensionesReparto','*','DimCode=1');

//Normas de reparto (Unidad negocio)
$SQL_UnidadNegocio=Seleccionar('uvw_Sap_tbl_DimensionesReparto','*','DimCode=2');

//Normas de reparto (Sucursal)
$SQL_Sucursal=Seleccionar('uvw_Sap_tbl_DimensionesReparto','*','DimCode=3');

//Articulo
$SQL_Articulo=Seleccionar('uvw_Sap_tbl_ArticulosEPP','*','','ItemName');

//Tipo entrega
$SQL_TipoEntrega=Seleccionar('uvw_Sap_tbl_TipoEntrega','*','','DeTipoEntrega');

//Año entrega
$SQL_AnioEntrega=Seleccionar('uvw_Sap_tbl_TipoEntregaAnio','*','','DeAnioEntrega');

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

if(isset($_GET['AnioEntrega'])&&$_GET['AnioEntrega']!=""){
	$AnioEntrega=$_GET['AnioEntrega'];
	$sw=1;
}

if(isset($_GET['EntregaDescont'])&&$_GET['EntregaDescont']!=""){
	$DescontEntrega=$_GET['EntregaDescont'];
	$sw=1;
}

if(isset($_GET['Articulo'])&&$_GET['Articulo']!=""){
	$Articulo=$_GET['Articulo'];
	$sw=1;
}

if(isset($_GET['TipoEntrega'])&&$_GET['TipoEntrega']!=""){
	$TipoEntrega=$_GET['TipoEntrega'];
	$sw=1;
}

if($sw==1){
	$ParamCons=array(
		"'".FormatoFecha($FechaInicial)."'",
		"'".FormatoFecha($FechaFinal)."'",
		"'".$Empleado."'",
		"'".$Area."'",
		"'".$CentroCosto."'",
		"'".$Sucursal."'",
		"'".$Articulo."'",
		"'".$TipoEntrega."'",
		"'".$AnioEntrega."'",
		"'".$DescontEntrega."'"
	);
	$SQL=EjecutarSP('usp_Inf_SeguimientoEntregaEPP',$ParamCons);
}
?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include_once("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Informe seguimiento de entrega EPP | <?php echo NOMBRE_PORTAL;?></title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<script type="text/javascript">
	$(document).ready(function() {
		$("#TipoEntrega").change(function(){
			$('.ibox-content').toggleClass('sk-loading',true);
			var TipoEnt=document.getElementById('TipoEntrega').value;
			var AnioEntrega=document.getElementById('AnioEntrega');	
			var EntregaDescont=document.getElementById('EntregaDescont');
			
			if(TipoEnt==2||TipoEnt==3||TipoEnt==4){
				document.getElementById('dv_AnioEnt').style.display='block';
				document.getElementById('dv_Descont').style.display='none';
				EntregaDescont.value="";
			}else if(TipoEnt==6){
				document.getElementById('dv_AnioEnt').style.display='none';
				document.getElementById('dv_Descont').style.display='block';
				AnioEntrega.value="";
			}else{
				document.getElementById('dv_AnioEnt').style.display='none';
				document.getElementById('dv_Descont').style.display='none';
				AnioEntrega.value="";
				EntregaDescont.value="";
			}	
			$('.ibox-content').toggleClass('sk-loading',false);
		});
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
                    <h2>Informe seguimiento de entrega EPP</h2>
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
                            <strong>Informe seguimiento de entrega EPP</strong>
                        </li>
                    </ol>
                </div>
            </div>
         <div class="wrapper wrapper-content">
             <div class="row">
				<div class="col-lg-12">
			    <div class="ibox-content">
					 <?php include("includes/spinner.php"); ?>
				  <form action="inf_entrega_epp.php" method="get" id="formBuscar" class="form-horizontal">
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
							<label class="col-lg-1 control-label">Articulo</label>
							<div class="col-lg-3">
								<select name="Articulo" class="form-control m-b select2" id="Articulo">
									<option value="">(Todos)</option>
								  <?php while($row_Articulo=sqlsrv_fetch_array($SQL_Articulo)){?>
											<option value="<?php echo $row_Articulo['ItemCode'];?>" <?php if((isset($_GET['Articulo']))&&(strcmp($row_Articulo['ItemCode'],$_GET['Articulo'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Articulo['ItemName']." (".$row_Articulo['ItemCode'].")";?></option>
									<?php 	}?>
								</select>
							</div>
						</div>
					  	<div class="form-group">
							<label class="col-lg-1 control-label">Centro de costo</label>
							<div class="col-lg-2">
								<select name="CentroCosto" class="form-control m-b" id="CentroCosto">
									<option value="">(Todos)</option>
								  <?php while($row_UnidadNegocio=sqlsrv_fetch_array($SQL_UnidadNegocio)){?>
											<option value="<?php echo $row_UnidadNegocio['OcrCode'];?>" <?php if((isset($_GET['CentroCosto']))&&(strcmp($row_UnidadNegocio['OcrCode'],$_GET['CentroCosto'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_UnidadNegocio['OcrName'];?></option>
									<?php 	}?>
								</select>
							</div>
							<label class="col-lg-1 control-label">Área</label>
							<div class="col-lg-2">
								<select name="Area" class="form-control m-b" id="Area">
									<option value="">(Todos)</option>
								  <?php while($row_ControCosto=sqlsrv_fetch_array($SQL_ControCosto)){?>
										<option value="<?php echo $row_ControCosto['OcrCode'];?>" <?php if((isset($_GET['Area']))&&(strcmp($row_ControCosto['OcrCode'],$_GET['Area'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_ControCosto['OcrName'];?></option>
								  <?php }?>
								</select>
							</div>
							<label class="col-lg-1 control-label">Sucursal</label>
							<div class="col-lg-2">
								<select name="Sucursal" class="form-control m-b" id="Sucursal">
									<option value="">(Todos)</option>
								  <?php while($row_Sucursal=sqlsrv_fetch_array($SQL_Sucursal)){?>
											<option value="<?php echo $row_Sucursal['OcrCode'];?>" <?php if(isset($_GET['Sucursal'])&&(strcmp($row_Sucursal['OcrCode'],$_GET['Sucursal'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Sucursal['OcrName'];?></option>
									<?php }?>
								</select>
							</div>							
						</div>
					  	<div class="form-group">
						  	<label class="col-lg-1 control-label">Tipo entrega</label>
							<div class="col-lg-2">
								<select name="TipoEntrega" class="form-control m-b" id="TipoEntrega">
									<option value="">(Todos)</option>
								  <?php while($row_TipoEntrega=sqlsrv_fetch_array($SQL_TipoEntrega)){?>
										<option value="<?php echo $row_TipoEntrega['IdTipoEntrega'];?>" <?php if((isset($_GET['TipoEntrega']))&&(strcmp($row_TipoEntrega['IdTipoEntrega'],$_GET['TipoEntrega'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_TipoEntrega['DeTipoEntrega'];?></option>
								  <?php }?>
								</select>
							</div>
						  	<div id="dv_AnioEnt" style="display: none;">
								<label class="col-lg-1 control-label">Año entrega</label>
								<div class="col-lg-2">
									<select name="AnioEntrega" class="form-control m-b" id="AnioEntrega">
										<option value="">(Todos)</option>
									<?php while($row_AnioEntrega=sqlsrv_fetch_array($SQL_AnioEntrega)){?>
											<option value="<?php echo $row_AnioEntrega['IdAnioEntrega'];?>" <?php if((isset($_GET['AnioEntrega']))&&(strcmp($row_AnioEntrega['IdAnioEntrega'],$_GET['AnioEntrega'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_AnioEntrega['DeAnioEntrega'];?></option>
									<?php }?>
									</select>
								</div>
							</div>
							<div id="dv_Descont" style="display: none;">
								<label class="col-lg-1 control-label">Entrega descontable</label>
								<div class="col-lg-2">
									<select name="EntregaDescont" class="form-control m-b" id="EntregaDescont">
										<option value="">(Todos)</option>
										<option value="NO" <?php if((isset($_GET['EntregaDescont']))&&($_GET['EntregaDescont']=="NO")){ echo "selected=\"selected\"";}?>>NO</option>
										<option value="SI" <?php if((isset($_GET['EntregaDescont']))&&($_GET['EntregaDescont']=="SI")){ echo "selected=\"selected\"";}?>>SI</option>
									</select>
								</div>
							</div>
							<div class="col-lg">
								<button type="submit" class="btn btn-outline btn-success pull-right"><i class="fa fa-search"></i> Buscar</button>
							</div>
						</div>
					  	<?php if($sw==1){?>
					  	<div class="form-group">
							<div class="col-lg-10 col-md-10">
								<a href="exportar_excel.php?exp=10&Cons=<?php echo base64_encode(implode(",",$ParamCons));?>&sp=<?php echo base64_encode('usp_Inf_SeguimientoEntregaEPP');?>">
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
                        <th>Empleado</th>
						<th>Cargo</th>
						<th>Sucursal</th>
						<th>Tipo entrega</th>
						<th>Núm. Solicitud</th>
						<th>Núm. Entrega</th>
						<th>Fecha entrega</th>						
						<th>Código articulo</th>
						<th>Nombre articulo</th>
						<th>Cant. Solicitada</th>
						<th>Cant. Preparada</th>
						<th>Cant. Entregada</th>
						<th>Cant. Pendiente</th>
						<th>Unidad</th>
						<th>Estado</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
						if($sw==1){
						while($row=sqlsrv_fetch_array($SQL)){ ?>
							<tr class="gradeX">
								<td><?php echo $row['NombreEmpleado'];?></td>
								<td><?php echo $row['EmpCargo'];?></td>
								<td><?php echo $row['Sucursal'];?></td>
								<td><?php echo $row['DeTipoEntrega'];?></td>
								<td><?php if($row['No_Traslado']!=""){?><a href="solicitud_salida.php?id=<?php echo base64_encode($row['NoInterno_Traslado']);?>&tl=1" target="_blank"><?php echo $row['No_Traslado'];?></a><?php }?></td>								
								<td><?php if($row['No_Entrega']!=""){?><a href="salida_inventario.php?id=<?php echo base64_encode($row['NoInterno_Entrega']);?>&tl=1" target="_blank"><?php echo $row['No_Entrega'];?></a><?php }?></td>								
								<td><?php if($row['Fecha_SalidaTraslado']!=""){echo $row['Fecha_SalidaTraslado']->format('Y-m-d');}?></td>
								<td><?php echo $row['ItemCode'];?></td>
								<td><?php echo $row['Dscription'];?></td>
								<td><?php echo number_format($row['CantSolicitada'],2);?></td>
								<td><?php echo number_format($row['CantPreparada'],2);?></td>
								<td><?php echo number_format($row['CantEntregada'],2);?></td>
								<td><?php echo number_format($row['CantPendiente'],2);?></td>
								<td><?php echo $row['Unidad'];?></td>
								<td><span <?php if($row['Estado_Salida_Traslado']=='ABIERTO'){echo "class='label label-info'";}elseif($row['Estado_Salida_Traslado']=='PENDIENTE'){echo "class='label label-warning'";}else{echo "class='label label-danger'";}?>><?php echo $row['Estado_Salida_Traslado'];?></span></td>
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

			<?php if(isset($_GET['TipoEntrega'])){?>
			$('#TipoEntrega').trigger('change');
			<?php } ?>
			
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