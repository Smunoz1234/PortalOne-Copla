<?php  
require_once("includes/conexion.php");
if(isset($_GET['id'])&&$_GET['id']!=""){
	$CodCliente=base64_decode($_GET['id']);
}else{
	$CodCliente="";
}
//Llamadas de servicios
$SQL_Llamadas=Seleccionar('uvw_Sap_tbl_LlamadasServicios','
	 "ID_LlamadaServicio",
	 "DocNum",
	 "ID_CodigoCliente",
	 "NombreClienteLlamada",
	 "IdContactoLLamada",
	 "NombreContactoLlamada",
	 "TelefonoContactoLlamada",
	 "CorreoContactoLlamada",
	 "AsuntoLlamada",
	 "IdTipoProblemaLlamada",
	 "DeTipoProblemaLlamada",
	 "IdSubTipoProblemaLlamada",
	 "DeSubTipoProblemaLlamada",
	 "IdTipoLlamada",
	 "DeTipoLlamada",
	 "IdEstadoLlamada",
	 "DeEstadoLlamada",
	 "NombreSucursal",
	 "FechaCreacionLLamada",
	 "FechaHoraCreacionLLamada"',"[ID_CodigoCliente]='".$CodCliente."'");
?>
<div class="form-group">
	<div class="col-lg-12">
		<div class="table-responsive">
		<table class="table table-striped table-bordered table-hover dataTables2" >
		<thead>
		<tr>
			<th>Ticket</th>
			<th>Asunto</th>
			<th>Tipo problema</th>
			<th>SubTipo problema</th>
			<th>Sucursal</th>
			<th>Contacto</th>                        
			<th>Fecha creación</th>
			<th>Estado</th>
			<th>Acciones</th>
		</tr>
		</thead>
		<tbody>
		<?php while($row_Llamadas=sql_fetch_array($SQL_Llamadas)){ ?>
			 <tr>
				<td><?php echo $row_Llamadas['DocNum'];?></td>
				<td><?php echo utf8_encode($row_Llamadas['AsuntoLlamada']);?></td>
				<td><?php echo $row_Llamadas['DeTipoProblemaLlamada'];?></td>
				<td><?php echo $row_Llamadas['DeSubTipoProblemaLlamada'];?></td>
				<td><?php echo $row_Llamadas['NombreSucursal'];?></td>
				<td><?php echo utf8_encode($row_Llamadas['NombreContactoLlamada']);?></td>							
				<td><?php echo $row_Llamadas['FechaHoraCreacionLLamada']->format('Y-m-d H:i');?></td>
				<td <?php if($row_Llamadas['IdEstadoLlamada']=='-3'){echo "class='text-success'";}elseif($row_Llamadas['IdEstadoLlamada']=='-2'){echo "class='text-warning'";}else{echo "class='text-danger'";}?>><?php echo $row_Llamadas['DeEstadoLlamada'];?></td>
				<td><a href="llamada_servicio.php?id=<?php echo base64_encode($row_Llamadas['ID_LlamadaServicio']);?>&tl=1&return=<?php echo base64_encode($_SERVER['QUERY_STRING']);?>&pag=<?php echo base64_encode('socios_negocios.php');?>" class="btn btn-link btn-xs" target="_blank"><i class="fa fa-folder-open-o"></i> Abrir</a></td>
			</tr>
		<?php }?>
		</tbody>
		</table>
	</div>
	</div>
	</div>	
<script>
 $(document).ready(function(){
	$('.dataTables2').DataTable({
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