<?php 
require_once("includes/conexion.php");
//require_once("includes/conexion_hn.php");
PermitirAcceso(315);

$sw=0;
$Filtro="";

//Sucursales
$ParamSucursal=array(
	"'".$_SESSION['CodUser']."'"
);
$SQL_Suc=EjecutarSP('sp_ConsultarSucursalesUsuario',$ParamSucursal);

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

//Filtros
$Cliente="";
$Sucursal="";
$Sede="";
$Validacion="0";
if(isset($_GET['Cliente'])&&$_GET['Cliente']!=""){
	$Cliente=$_GET['Cliente'];
	$sw=1;
}

if(isset($_GET['Sucursal'])&&$_GET['Sucursal']!=""){
	$Sucursal=$_GET['Sucursal'];
	$sw=1;
}

if(isset($_GET['Sede'])&&$_GET['Sede']!=""){
	$Sede=$_GET['Sede'];
	$sw=1;
}


if(isset($_GET['Validacion'])&&$_GET['Validacion']!=""){
	$Validacion=$_GET['Validacion'];
	$sw=1;
}

if($sw==1){
	//Ejecutar SP
	$ParamCons=array(
		"'".FormatoFecha($FechaInicial)."'",
		"'".FormatoFecha($FechaFinal)."'",
		"'".$Cliente."'",
		"'".$Sucursal."'",
		"'".$Sede."'",
		"'".$Validacion."'",
		"'".$_SESSION['CodUser']."'"
	);
	$SQL=EjecutarSP('usp_Programacion_Clientes',$ParamCons);
	
}
//echo $Cons;
?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include_once("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Programación de clientes | <?php echo NOMBRE_PORTAL;?></title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<script type="text/javascript">
	$(document).ready(function() {
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
                    <h2>Programación de clientes</h2>
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
                            <strong>Programación de clientes</strong>
                        </li>
                    </ol>
                </div>
            </div>
         <div class="wrapper wrapper-content">
             <div class="row">
				<div class="col-lg-12">
			    <div class="ibox-content">
					 <?php include("includes/spinner.php"); ?>
				  <form action="programacion_clientes.php" method="get" id="formBuscar" class="form-horizontal">
						<div class="form-group">
							<label class="col-xs-12"><h3 class="bg-muted p-xs b-r-sm"><i class="fa fa-filter"></i> Datos para filtrar</h3></label>
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
							<label class="col-lg-1 control-label">Cliente</label>
							<div class="col-lg-3">
								<input name="Cliente" type="hidden" id="Cliente" value="<?php if(isset($_GET['Cliente'])&&($_GET['Cliente']!="")){ echo $_GET['Cliente'];}?>">
								<input name="NombreCliente" type="text" class="form-control" id="NombreCliente" placeholder="Ingrese para buscar..." value="<?php if(isset($_GET['NombreCliente'])&&($_GET['NombreCliente']!="")){ echo $_GET['NombreCliente'];}?>">
							</div>
							<label class="col-lg-1 control-label">Sucursal cliente</label>
							<div class="col-lg-3">
							 <select id="Sucursal" name="Sucursal" class="form-control select2">
								<option value="">(Todos)</option>
								<?php 
								 if($sw_suc==1){//Cuando se ha seleccionado una opción
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
								 }elseif($sw_suc==2){//Cuando no se ha seleccionado todavia, al entrar a la pagina
									  while($row_Sucursal=sqlsrv_fetch_array($SQL_Sucursal)){?>
										<option value="<?php echo $row_Sucursal['NombreSucursal'];?>"><?php echo $row_Sucursal['NombreSucursal'];?></option>
								<?php }
								 }?>
							</select>
							</div>
						</div>
						<div class="form-group">
							<label class="col-lg-1 control-label">Validación</label>
							<div class="col-lg-3">
								<select name="Validacion" class="form-control m-b" id="Validacion">
										<option value="">(Todos)</option>
										<option value="1" <?php if(isset($_GET['Validacion'])&&($_GET['Validacion']=="1")){ echo "selected=\"selected\"";}?>>Si tiene OT</option>
										<option value="2" <?php if(isset($_GET['Validacion'])&&($_GET['Validacion']=="2")){ echo "selected=\"selected\"";}?>>No tiene OT</option>
								</select>
							</div>
							<label class="col-lg-1 control-label">Sede</label>
							<div class="col-lg-3">
								<select name="Sede" class="form-control select2" id="Sede">
									<option value="">(Todos)</option>
									 <?php while($row_Suc=sqlsrv_fetch_array($SQL_Suc)){?>
											<option value="<?php echo $row_Suc['DeSucursal'];?>" <?php if((isset($_GET['Sede'])&&($_GET['Sede']!=""))&&(strcmp($row_Suc['DeSucursal'],$_GET['Sede'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_Suc['DeSucursal'];?></option>
									 <?php }?>
								</select>
							</div>
							<div class="col-lg-4 pull-right">
								<button type="submit" class="btn btn-outline btn-success pull-right"><i class="fa fa-search"></i> Buscar</button>
							</div>							
						</div>
					  <?php if($sw==1){?>
					  	<div class="form-group">
							<div class="col-lg-10 col-md-10">
								<a href="exportar_excel.php?exp=9&Cons=1&Cliente=<?php echo base64_encode($Cliente);?>&Sucursal=<?php echo base64_encode($Sucursal);?>&Sede=<?php echo base64_encode($Sede);?>&Validacion=<?php echo base64_encode($Validacion);?>">
									<img src="css/exp_excel.png" width="50" height="30" alt="Exportar a Excel" title="Exportar a Excel"/>
								</a>
							</div>						
						</div>
					  <?php }?>
				 </form>
			</div>
			</div>
		  </div>
		<?php if($sw==1){?>
        <br>
        <div class="row">
           <div class="col-lg-12">
			    <div class="ibox-content">
					 <?php include("includes/spinner.php"); ?>
					<div class="table-responsive">
						<table class="table table-striped table-bordered table-hover dataTables-example" >
							<thead>
								<tr>
									<th>Nombre cliente</th>
									<th>Sucursal cliente</th>
									<th>Código articulo LMT</th>
									<th>Nombre articulo LMT</th>
									<th>Periodo</th>   
									<th>Sede</th>
									<th>OT</th>
									<th>Servicio</th>
									<th>Validación</th>
								</tr>
							</thead>
							<tbody>
								<?php while($row=sqlsrv_fetch_array($SQL)){ ?>
									<tr class="gradeX">
										<td><?php echo $row['DeCliente'];?></td>
										<td><?php echo $row['IdSucursalCliente'];?></td>										
										<td><a href="articulos.php?id=<?php echo base64_encode($row['IdArticuloLMT']);?>&return=<?php echo base64_encode($_SERVER['QUERY_STRING']);?>&pag=<?php echo base64_encode('cronograma_clientes.php');?>&tl=1" target="_blank"><?php echo $row['IdArticuloLMT'];?></a></td>								
										<td><?php echo $row['NombreArticuloLMT'];?></td>
										<td><?php if($row['Periodo']!=""){echo $row['Periodo']->format('Y-m-d');}?></td>
										<td><?php echo $row['Sede'];?></td>
										<td><?php if($row['IdLlamadaServicio']!=0){?><a href="llamada_servicio.php?id=<?php echo base64_encode($row['ID_Llamada']);?>&return=<?php echo base64_encode($_SERVER['QUERY_STRING']);?>&pag=<?php echo base64_encode('cronograma_clientes.php');?>&tl=1" target="_blank"><?php echo $row['IdLlamadaServicio'];?></a><?php }else{echo "--";}?></td>	
										<td><?php echo $row['ServiciosLlamadas'];?></td>
										<td><?php echo $row['Validacion'];?></td>
									</tr>
								<?php }?>
							</tbody>
						</table>							
					</div>
				</div>
			 </div> 
          </div>
		<?php }?>
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
			$(".select2").select2();
			
			$('.i-checks').iCheck({
				 checkboxClass: 'icheckbox_square-green',
				 radioClass: 'iradio_square-green',
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
			
            $('.dataTables-example').DataTable({
                pageLength: 10,
				order: [[ 0, "desc" ]],
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