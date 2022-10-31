<?php 
if(isset($_GET['id'])&$_GET['id']!=""){
require_once("includes/conexion.php");
PermitirAcceso(601);

	$ConsOC="SELECT * FROM uvw_Sap_tbl_OrdenesDeCompra WHERE DocEntry='".base64_decode($_GET['id'])."'";
	$SQLOC=sqlsrv_query($conexion,$ConsOC);
	$rowOC=sqlsrv_fetch_array($SQLOC);
		
	$Consulta="Select * From uvw_Sap_tbl_OrdenesDeCompraDetalle Where DocEntry='".base64_decode($_GET['id'])."'";
	$SQL=sqlsrv_query($conexion,$Consulta);
	
	$SubTotal=0;
	
?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include_once("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php echo NOMBRE_PORTAL;?> | Detalles ordenes de compra</title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
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
                    <h2>Detalle orden de compra</h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="index1.php">Inicio</a>
                        </li>
                        <li>
                            <a href="#">Proveedores</a>
                        </li>
						<li>
                            <a href="#">Documentos</a>
                        </li>
						<li>
                            <a href="#">Ordenes de compra</a>
                        </li>
                        <li class="active">
                            <strong>Detalle orden de compra</strong>
                        </li>
                    </ol>
                </div>
            </div>
         <div class="wrapper wrapper-content">
			<div class="row">
				<div class="col-lg-12">
					<div class="ibox-content">
						 <h3>Orden de compra: <?php echo $rowOC['DocNum'];?></h3>
					</div>
				</div>
			</div>
		  <br>
          <div class="row">
           <div class="col-lg-12">
			    <div class="ibox-content">
			<div class="table-responsive">
                    <table class="table">
						<thead>
							<tr>
								<th>C&oacute;digo</th>
								<th>Descripci&oacute;n</th>
								<th>Comentarios</th>
								<th>Unidad</th>
								<th>Cantidad ordenada</th>
								<th>Cantidad pendiente</th>
								<th>Precio</th>
								<th>% Descuento</th>
								<th>Total</th>
							</tr>
						</thead>
						<tbody>
						   <?php while($row=sqlsrv_fetch_array($SQL)){?>
							<tr <?php if($row['LineStatus']=='C'){ echo "class='disabled'";}?>>
								<td><?php echo $row['ItemCode'];?></td>
								<td><?php echo utf8_encode($row['ItemName']);?></td>
								<td><?php echo utf8_encode($row['FreeTxt']);?></td>
								<td><?php echo utf8_encode($row['NombreUnidadMedida']);?></td>
								<td align="right"><?php echo number_format($row['CantidadOrdenada'],2);?></td>
								<td align="right"><?php echo number_format($row['OpenQty'],2);?></td>
								<td align="right"><?php echo number_format($row['Price'],2);?></td>
								<td align="right"><?php echo number_format($row['DiscPrcnt'],2);?></td>
								<td align="right"><?php echo number_format($row['LineTotal'],2);?></td>
							</tr>
						<?php $SubTotal=$SubTotal+$row['LineTotal'];}?>
							<tr>
								<td colspan="8" align="right">SUBTOTAL</td>
								<td align="right"><?php echo number_format($SubTotal,2);?></td>
							</tr>
							<tr>
								<td colspan="8" align="right">IVA</td>
								<td align="right"><?php echo number_format($rowOC['VatSum'],2);?></td>
							</tr>
							<tr>
								<td colspan="8" align="right"><strong>TOTAL</strong></td>
								<td align="right"><strong><?php echo number_format($rowOC['DocTotal'],2);?></strong></td>
							</tr>
							<tr>
								<td colspan="9"><strong>Comentarios</strong></td>
							</tr>
							<tr>
								<td colspan="9"><?php echo utf8_encode($rowOC['Comments']);?></td>
							</tr>
						</tbody>
					</table>
              </div>
			</div>
			 </div> 
          </div>
		  <br>
		 <div class="row">
			<div class="col-lg-12">
				<div class="ibox-content">
					<a href="ordenes_compra.php" class="btn btn-outline btn-default"><i class="fa fa-arrow-circle-o-left"></i> Regresar</a>
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
            $('.dataTables-example').DataTable({
                pageLength: 25,
                responsive: true,
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
<?php sqlsrv_close($conexion);}?>