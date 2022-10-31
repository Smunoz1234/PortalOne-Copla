<?php require_once("includes/conexion.php");
PermitirAcceso(1502);

$sw=0;

//Clientes
/*if(PermitirFuncion(205)){
	$SQL_Cliente=Seleccionar("uvw_Sap_tbl_Clientes","CodigoCliente, NombreCliente","",'NombreCliente');	
}else{
	$Where="ID_Usuario = ".$_SESSION['CodUser'];
	$SQL_Cliente=Seleccionar("uvw_tbl_ClienteUsuario","CodigoCliente, NombreCliente",$Where);	
}*/

//Tipos de documento
$SQL_TipoDocumento=Seleccionar('uvw_tbl_ObjetosSAP','*',"IdTipoDocumento IN (13,14)");

//Estado cliente
$SQL_EstadoCliente=Seleccionar('uvw_tbl_FacturacionElectronica_EstadoCliente','*');


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
$Filtro="";//Filtro
if(isset($_GET['TipoActividad'])&&$_GET['TipoActividad']!=""){
	$Filtro.=" and ID_TipoActividad='".$_GET['TipoActividad']."'";
	$sw=1;
}
if(isset($_GET['EstadoActividad'])&&$_GET['EstadoActividad']!=""){
	$Filtro.=" and IdEstadoActividad='".$_GET['EstadoActividad']."'";
	$sw=1;
}
if(isset($_GET['TipoTarea'])&&$_GET['TipoTarea']!=""){
	$Filtro.=" and TipoTarea='".$_GET['TipoTarea']."'";
	$sw=1;
}
if(isset($_GET['Cliente'])){
	if($_GET['Cliente']!=""){//Si se selecciono el cliente
		$Filtro.=" and ID_CodigoCliente='".$_GET['Cliente']."'";
		$sw=1;	
	}else{
		if(!PermitirFuncion(205)){
			$Where="ID_Usuario = ".$_SESSION['CodUser'];
			$SQL_Cliente=Seleccionar("uvw_tbl_ClienteUsuario","CodigoCliente, NombreCliente",$Where);
			$k=0;
			$FiltroCliente="";
			while($row_Cliente=sqlsrv_fetch_array($SQL_Cliente)){
				//Clientes
				$WhereCliente[$k]="ID_CodigoCliente='".$row_Cliente['CodigoCliente']."'";
				$FiltroCliente=implode(" OR ",$WhereCliente);
				
				$k++;
			}
			if($FiltroCliente!=""){
				$Filtro.=" and (".$FiltroCliente.")";
			}/*else{
				$Filtro.=" and (ID_CodigoCliente='".$FiltroCliente."')";
			}*/
			
			$Where="ID_Usuario = ".$_SESSION['CodUser'];
			$SQL_Cliente=Seleccionar("uvw_tbl_ClienteUsuario","CodigoCliente, NombreCliente",$Where);	
		}
	}
}else{//Si no se selecciono el cliente
		if(!PermitirFuncion(205)){
			$Where="ID_Usuario = ".$_SESSION['CodUser'];
			$SQL_Cliente=Seleccionar("uvw_tbl_ClienteUsuario","CodigoCliente, NombreCliente",$Where);
			$k=0;
			$FiltroCliente="";
			while($row_Cliente=sqlsrv_fetch_array($SQL_Cliente)){
				//Clientes
				$WhereCliente[$k]="ID_CodigoCliente='".$row_Cliente['CodigoCliente']."'";
				$FiltroCliente=implode(" OR ",$WhereCliente);
				//$FiltroSuc=implode(" OR ",$WhereSuc);		
				$k++;
			}
			if($FiltroCliente!=""){
				$Filtro.=" and (".$FiltroCliente.")";
			}/*else{
				$Filtro.=" and (ID_CodigoCliente='".$FiltroCliente."')";
			}*/
			
			//Recargar consultas para los combos
			$Where="ID_Usuario = ".$_SESSION['CodUser'];
			$SQL_Cliente=Seleccionar("uvw_tbl_ClienteUsuario","CodigoCliente, NombreCliente",$Where);
			
		}
	}


if(isset($_GET['BuscarDato'])&&$_GET['BuscarDato']!=""){
	$Filtro.=" and (NombreContacto LIKE '%".$_GET['BuscarDato']."%' OR NombreSucursal LIKE '%".$_GET['BuscarDato']."%' OR ComentariosActividad LIKE '%".$_GET['BuscarDato']."%' OR NotasActividad LIKE '%".$_GET['BuscarDato']."%' OR DE_AsuntoActividad LIKE '%".$_GET['BuscarDato']."%' OR TituloActividad LIKE '%".$_GET['BuscarDato']."%' OR ID_Actividad LIKE '%".$_GET['BuscarDato']."%' OR NombreCliente LIKE '%".$_GET['BuscarDato']."%')";
	$sw=1;
}

if($sw==1){
	$Cons="Select * From uvw_Sap_tbl_Actividades Where (FechaHoraInicioActividad Between '".FormatoFecha($FechaInicial,"00:00:00")."' and '".FormatoFecha($FechaFinal,"23:59:59")."') $Filtro ORDER BY ID_Actividad DESC";
	$SQL=sqlsrv_query($conexion,$Cons);
}
?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Comprobantes electrónicos | <?php echo NOMBRE_PORTAL;?></title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->

<script type="text/javascript">
	$(document).ready(function() {
		$("#NombreCliente").change(function(){
			var NomCliente=document.getElementById("NombreCliente");
			var Cliente=document.getElementById("Cliente");
			if(NomCliente.value==""){
				Cliente.value="";
			}	
		});
	});
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
                    <h2>Comprobantes electrónicos</h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="index1.php">Inicio</a>
                        </li>
                        <li>
                            <a href="#">Facturación electrónica</a>
                        </li>
                        <li class="active">
                            <strong>Comprobantes electrónicos</strong>
                        </li>
                    </ol>
                </div>
            </div>
         <div class="wrapper wrapper-content">
             <div class="row">
				<div class="col-lg-12">
			    <div class="ibox-content">
					 <?php include("includes/spinner.php"); ?>
				  <form action="comprobantes_fe.php" method="get" id="formBuscar" class="form-horizontal">
						<div class="form-group">
							<label class="col-lg-1 control-label">Fechas</label>
							<div class="col-lg-3">
								<div class="input-daterange input-group" id="datepicker">
									<input name="FechaInicial" type="text" class="input-sm form-control" id="FechaInicial" placeholder="Fecha inicial" value="<?php echo $FechaInicial;?>"/>
									<span class="input-group-addon">hasta</span>
									<input name="FechaFinal" type="text" class="input-sm form-control" id="FechaFinal" placeholder="Fecha final" value="<?php echo $FechaFinal;?>" />
								</div>
							</div>
							<label class="col-lg-1 control-label">Estado DIAN</label>
							<div class="col-lg-2">
								<select name="EstadoDIAN" class="form-control m-b" id="EstadoDIAN">
										<option value="">(Todos)</option>
								  <?php while($row_TipoActividad=sqlsrv_fetch_array($SQL_TipoActividad)){?>
										<option value="<?php echo $row_TipoActividad['ID_TipoActividad'];?>" <?php if((isset($_GET['TipoActividad']))&&(strcmp($row_TipoActividad['ID_TipoActividad'],$_GET['TipoActividad'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_TipoActividad['DE_TipoActividad'];?></option>
								  <?php }?>
								</select>
               	  			</div>
							<label class="col-lg-1 control-label">Estado cliente</label>
							<div class="col-lg-2">
								<select name="EstadoCliente" class="form-control m-b" id="EstadoCliente">
									<option value="" selected="selected">(Todos)</option>
								<?php while($row_EstadoCliente=sqlsrv_fetch_array($SQL_EstadoCliente)){?>
										<option value="<?php echo $row_EstadoCliente['IdEstadoCliente'];?>" <?php if((isset($_GET['EstadoCliente']))&&(strcmp($row_EstadoCliente['IdEstadoCliente'],$_GET['EstadoCliente'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_EstadoCliente['DeEstadoCliente'];?></option>
								  <?php }?>
								</select>
							</div>
						</div>
					  	<div class="form-group">
							<label class="col-lg-1 control-label">Cliente</label>
							<div class="col-lg-3">
								<input name="Cliente" type="hidden" id="Cliente" value="<?php if(isset($_GET['Cliente'])&&($_GET['Cliente']!="")){ echo $_GET['Cliente'];}?>">
								<input name="NombreCliente" type="text" class="form-control" id="NombreCliente" placeholder="Para TODOS, dejar vacio..." value="<?php if(isset($_GET['NombreCliente'])&&($_GET['NombreCliente']!="")){ echo $_GET['NombreCliente'];}?>">
							</div>
							<label class="col-lg-1 control-label">Tipo documento</label>
							<div class="col-lg-2">
								<select name="TipoDocumento" class="form-control m-b" id="TipoDocumento">
										<option value="">(Todos)</option>
								  <?php while($row_TipoDocumento=sqlsrv_fetch_array($SQL_TipoDocumento)){?>
										<option value="<?php echo $row_TipoDocumento['IdTipoDocumento'];?>" <?php if((isset($_GET['TipoDocumento']))&&(strcmp($row_TipoDocumento['IdTipoDocumento'],$_GET['TipoDocumento'])==0)){ echo "selected=\"selected\"";}?>><?php echo $row_TipoDocumento['DeTipoDocumento'];?></option>
								  <?php }?>
								</select>
							</div>
							<label class="col-lg-1 control-label">Serie</label>
							<div class="col-lg-2">
								<select name="EstadoActividad" class="form-control m-b" id="EstadoActividad">
										<option value="">(Todos)</option>
								</select>
							</div>
						</div>
					 	<div class="form-group">
							<label class="col-lg-1 control-label">Ver procesados</label>
							<div class="col-lg-2">
								<select name="EstadoActividad" class="form-control m-b" id="EstadoActividad">
									<option value="NO">NO</option>
									<option value="SI">SI</option>
								</select>
							</div>
							<div class="col-lg-1"></div>
							<label class="col-lg-1 control-label">Buscar dato</label>
							<div class="col-lg-3">
								<input name="BuscarDato" type="text" class="form-control" id="BuscarDato" maxlength="100" value="<?php if(isset($_GET['BuscarDato'])&&($_GET['BuscarDato']!="")){ echo $_GET['BuscarDato'];}?>">
							</div>
							<div class="col-lg-4">
								<button type="submit" class="btn btn-outline btn-success pull-right"><i class="fa fa-search"></i> Buscar</button>
							</div>
						</div>
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
						<th>Número documento</th>
						<th>Tipo documento</th>
						<th>Serie</th>
						<th>Fecha documento</th>                                         
                        <th>Cliente</th>
                        <th>Correo</th>
						<th>Estado DIAN</th>
						<th>Estado cliente</th>
						<th>Estado documento</th>
						<th>UUID</th>
						<th>Mensaje proveedor</th>
						<th>Fecha creación</th>
						<th>Acciones</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if($sw==1){
						while($row=sqlsrv_fetch_array($SQL)){
						?>
						 <tr class="gradeX tooltip-demo">
							<td><?php echo $row['ID_Actividad'];?></td>
							<td><?php echo $row['DeAsignadoPor'];?></td>
							<td><?php if($row['NombreEmpleado']!=""){echo $row['NombreEmpleado'];}else{echo "(Sin asignar)";}?></td>
							<td><?php echo $row['TituloActividad'];?></td>						
							<td><?php echo $row['NombreCliente'];?></td>
							<td><?php echo $row['NombreSucursal'];?></td>
							<td><?php if($row['FechaHoraInicioActividad']!=""){ echo $row['FechaHoraInicioActividad']->format('Y-m-d H:s');}else{?><p class="text-muted">--</p><?php }?></td>
							<td><?php if($row['FechaHoraFinActividad']!=""){ echo $row['FechaHoraFinActividad']->format('Y-m-d H:s');}else{?><p class="text-muted">--</p><?php }?></td>
							<td><p class='<?php echo $DVenc[0];?>'><?php echo $DVenc[1];?></p></td>
							<td><?php if($row['ID_OrdenServicioActividad']!=0){?><a href="llamada_servicio.php?id=<?php echo base64_encode($row['ID_LlamadaServicio']);?>&return=<?php echo base64_encode($_SERVER['QUERY_STRING']);?>&pag=<?php echo base64_encode('gestionar_actividades.php');?>&tl=1"><?php echo $row['ID_OrdenServicioActividad'];?></a><?php }else{echo "--";}?></td>							
							<td <?php if($row['IdEstadoActividad']=='N'){echo "class='text-success'";}else{echo "class='text-danger'";}?>><?php echo $row['DeEstadoActividad'];?></td>
							<td><a href="actividad.php?id=<?php echo base64_encode($row['ID_Actividad']);?>&return=<?php echo base64_encode($_SERVER['QUERY_STRING']);?>&pag=<?php echo base64_encode('gestionar_actividades.php');?>&tl=1" class="alkin btn btn-link btn-xs"><i class="fa fa-folder-open-o"></i> Abrir</a></td>
							<td><?php if($row['Metodo']==0){?><i class="fa fa-check-circle text-info" title="Sincronizado con SAP"></i><?php }else{?><i class="fa fa-times-circle text-danger" title="Error de sincronización con SAP"></i><?php }?></td>
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