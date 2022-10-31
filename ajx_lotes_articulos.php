<?php 
if(isset($_GET['id'])&&($_GET['id']!="")){
	require_once("includes/conexion.php");
	
	$edit=$_GET['edit'];
	$objtype=$_GET['objtype'];
	$basetype=isset($_GET['basetype']) ? $_GET['basetype'] : "";
	$sentido=isset($_GET['sentido']) ? $_GET['sentido'] : "out";
	
	if($edit==1){//Creando
		
		//Consultar los lotes que se sacaron
		if($sentido=="in"){
			if($objtype==16){//Devolucion de venta
				$Parametros=array(
					"'".$basetype."'",
					"'".$_GET['base_entry']."'",
					"'".$_GET['baseline']."'",
					"'".$_GET['id']."'"
				);
				$SQL=EjecutarSP('sp_ConsultarLotesDocSAP',$Parametros);
				$TotalLotEnt=SumarTotalLotesEntregar($_GET['id'], $_GET['linenum'], $_GET['whscode'], $_GET['cardcode'], $objtype, $_GET['usuario']);
			}
		}else{//Consultar los lotes del articulo
			$Parametros=array(
				"'".$_GET['id']."'",
				"'".$_GET['whscode']."'"
			);
			$SQL=EjecutarSP('sp_ConsultarInventarioLotes',$Parametros);
			$TotalLotEnt=SumarTotalLotesEntregar($_GET['id'], $_GET['linenum'], $_GET['whscode'], $_GET['cardcode'], $objtype, $_GET['usuario']);
		}		
	}else{//Consultando
		$Parametros=array(
			"'".$objtype."'",
			"'".$_GET['docentry']."'",
			"'".$_GET['linenum']."'",
			"'".$_GET['id']."'"
		);
		$SQL=EjecutarSP('sp_ConsultarLotesDocSAP',$Parametros);
	}
	
?>
<!doctype html>
<html>
<head>
<style>
	.iboxedit{
		padding: 10px !important;	
	}
	body{
		background-color: #ffffff;
	}
	.form-control{
		width: auto;
		height: 28px;
	}
	.txtYellow{
		background-color: #FAF9C3;
	}
	<?php if($edit==1){?>
	.tableedit > tbody > tr > td{
		padding-left: 8px !important;
		vertical-align: middle;
		padding-top: 1px !important;
		padding-bottom: 1px !important;
	}
	<?php }else{?>
	.tableedit > tbody > tr > td{
		padding-left: 8px !important;
		vertical-align: middle;
	}
	<?php }?>
</style>
<?php if($edit==1){?>
<script>
function ActualizarDatos(idlote,sysnumber, fechavenc){//Actualizar datos asincronicamente
	$.ajax({
		type: "GET",
		url: "includes/procedimientos.php?type=11&edit=<?php echo $edit;?>&objtype=<?php echo $objtype;?>&linenum=<?php echo $_GET['linenum'];?>&itemcode=<?php echo $_GET['id'];?>&itemname=<?php echo $_GET['itemname'];?>&und=<?php echo $_GET['und'];?>&whscode=<?php echo $_GET['whscode'];?>&distnumber="+idlote+"&sysnumber="+sysnumber+"&fechavenc="+fechavenc+"&cant="+document.getElementById("ItemCode"+idlote).value+"&cardcode=<?php echo $_GET['cardcode'];?>&usuario=<?php echo $_GET['usuario'];?>",
		success: function(response){
			if(response!="Error"){
				document.getElementById('TimeAct').innerHTML="<strong>Actualizado:</strong> "+response;
				CalcularTotal(idlote,sysnumber,fechavenc);
			}
		}
	});
}
function CalcularTotal(idlote,sysnumber, fechavenc){
	$.ajax({
		type: "GET",
		url: "includes/procedimientos.php?type=12&edit=<?php echo $edit;?>&objtype=<?php echo $objtype;?>&linenum=<?php echo $_GET['linenum'];?>&itemcode=<?php echo $_GET['id'];?>&whscode=<?php echo $_GET['whscode'];?>&cardcode=<?php echo $_GET['cardcode'];?>&usuario=<?php echo $_GET['usuario'];?>",
		success: function(response){
			if(response!="Error"){
				var TotalEnt=response.replace(/,/g, '');
				var CantSalida='<?php echo $_GET['cant'];?>';
				if(parseFloat(TotalEnt)>parseFloat(CantSalida)){
					swal({
						title: "¡Lo sentimos!",
						text: "La cantidad total es mayor a la cantidad a entregar.",
						type: "error",
						confirmButtonText: "OK"
					});
					document.getElementById("ItemCode"+idlote).value='0';
					ActualizarDatos(idlote, sysnumber, fechavenc);
				}
				document.getElementById('TotalLotEnt').innerHTML=response;
			}
		}
	});
}
</script>
<?php }?>
</head>

<body>
	<div class="ibox-content iboxedit">
		<?php include("includes/spinner.php"); ?>
		<div class="row"> 
			<div class="col-lg-12">
				<form action="" method="post" class="form-horizontal" id="FrmLotes">
					<?php if($edit==1){?>
					<div class="form-group">
						<label class="col-xs-12">
							<h3 class="bg-success p-xs b-r-sm"><i class="fa fa-tasks"></i> Lotes disponibles: <?php echo base64_decode($_GET['itemname'])." (".$_GET['id'].")";?></h3>
						</label>
					</div>
					<?php if($sentido=="in"&&$basetype==""){?>
					<div class="form-group">
						<label class="col-lg-1 control-label">Lotes actuales</label>
						<div class="col-lg-4">
							<label class="checkbox-inline i-checks"><input name="chkOcultarLotes" id="chkOcultarLotes" type="checkbox" value="1"> Ocultar lotes del sistema</label>
						</div>
					</div>
					<?php }?>
					<div class="form-group">
						<div class="col-xs-12">
							<div id="TimeAct"  class="pull-right"></div>
						</div>
					</div>
					<table width="100%" class="table table-bordered tableedit">
						<thead>
							<tr>
								<th>Lote</th>
								<th>Cantidad disponible</th>
								<th>Fecha de vencimiento</th>
								<th>Número de sistema</th>
								<th>Cantidad asignada</th>
							</tr>
						</thead>
						<tbody>
						<?php 
						while($row=sqlsrv_fetch_array($SQL)){
							//Consultar si hay datos ingresados en los lotes
							$Parametros=array(
								"'".$_GET['id']."'",
								"'".$_GET['linenum']."'",
								"'".$_GET['whscode']."'",
								"'".$row['IdLote']."'",
								"'".$_GET['cardcode']."'",
								"'".$objtype."'",
								"'".$_SESSION['CodUser']."'"
							);
							$SQL_DtAct=EjecutarSP('sp_ConsultarLotesDatos',$Parametros);
							$row_DtAct=sqlsrv_fetch_array($SQL_DtAct);
						?>
						<tr>
							<td><?php echo $row['IdLote'];?></td>
							<td><?php echo number_format($row['Cantidad'],0);?></td>
							<td><?php echo $row['FechaVenciLote'];?></td>
							<td><?php echo $row['IdSysNumber'];?></td>
							<td><input type="text" id="ItemCode<?php echo $row['IdLote'];?>" name="ItemCode[]" class="form-control txtYellow" onKeyUp="revisaCadena(this);" onKeyPress="return justNumbers(event,this.value);" onChange="VerificarCant('<?php echo $row['IdLote'];?>','<?php echo $row['IdSysNumber'];?>','<?php echo number_format($row['Cantidad'],0);?>','<?php echo $row['FechaVenciLote'];?>');" value="<?php echo number_format($row_DtAct['Cantidad'],0);?>"></td>
						</tr>
						<?php }?>
						<?php if($sentido=="in"&&$basetype==""){?>
						<tr>
							<td><input type="text" id="IdLote_0" name="IdLote[]" class="form-control txtYellow"></td>
							<td><input type="text" id="Cantidad_0" name="Cantidad[]" class="form-control txtYellow"></td>
							<td><input type="text" id="FechaVenciLote_0" name="FechaVenciLote[]" class="form-control txtYellow" data-mask="9999-99-99"></td>
							<td>&nbsp;</td>
							<td><input type="text" id="ItemCode<?php echo $row['IdLote'];?>" name="ItemCode[]" class="form-control txtYellow" onKeyUp="revisaCadena(this);" onKeyPress="return justNumbers(event,this.value);" value="<?php echo number_format($row_DtAct['Cantidad'],0);?>"></td>
						</tr>
							<button type="button" id="btnCliente<?php echo $Cont;?>" class="btn btn-success btn-xs" onClick="addField(this);"><i class="fa fa-plus"></i> Añadir otro</button>
						<?php }?>
						</tbody>
					</table>
					<div class="col-xs-11">
						<h3 class="text-success pull-right"><strong>Total a entregar: </strong></h3>						
					</div>
					<div class="col-xs-1">
						<h3 class="text-danger"><strong id="TotalLotEnt"><?php echo $TotalLotEnt;?></strong></h3>
					</div>
					<?php }else{?>
						<div class="form-group">
							<label class="col-xs-12">
								<h3 class="bg-success p-xs b-r-sm"><i class="fa fa-tasks"></i> Lotes: <?php echo base64_decode($_GET['itemname'])." (".$_GET['id'].")";?></h3>
							</label>
						</div>
						<table width="100%" class="table table-bordered tableedit">
						<thead>
							<tr>
								<th>Lote</th>
								<th>Unidad</th>
								<th>Fecha de vencimiento</th>
								<th>Número de sistema</th>
								<th>Cantidad entregada</th>
							</tr>
						</thead>
						<tbody>
						<?php 
						while($row=sqlsrv_fetch_array($SQL)){
						?>
						<tr>
							<td><?php echo $row['IdLote'];?></td>
							<td><?php echo $row['UndMedida'];?></td>
							<td><?php echo $row['FechaVenciLote'];?></td>
							<td><?php echo $row['IdSysNumber'];?></td>
							<td><?php echo number_format($row['Cantidad']);?></td>
						</tr>
						<?php }?>
						</tbody>
					</table>
					<?php }?>
				</form>
			</div>
		</div>
	</div>
<?php if($edit==1){?>
<script>
function VerificarCant(id, sysnumber, cant_actual, fechavenc){
	var CantIngresada=document.getElementById('ItemCode'+id);	
	if(parseFloat(CantIngresada.value)>0){
		var CantLote=cant_actual.replace(/,/g, '');
		if(parseFloat(CantIngresada.value) > parseFloat(CantLote)){
			CantIngresada.value='0';
			swal({
				title: "¡Error!",
				text: "No puede ingresar una cantidad mayor a la cantidad del lote.",
				type: "error",
				confirmButtonText: "OK"
			});			
		}
	}else if(parseFloat(CantIngresada.value)<0){
		CantIngresada.value='0';
		swal({
			title: "¡Error!",
			text: "No puede ingresar una cantidad negativa.",
			type: "error",
			confirmButtonText: "OK"
		});	
	}
		ActualizarDatos(id, sysnumber, fechavenc);
}
</script>
<?php }?>
<script>
	 $(document).ready(function(){
		  $('.i-checks').iCheck({
			 checkboxClass: 'icheckbox_square-green',
             radioClass: 'iradio_square-green',
          });
		 /*$("#FrmLotes").validate({
			 submitHandler: function(form){
				 $('.ibox-content').toggleClass('sk-loading');
				 form.submit();
				}
			});*/
	 });
</script>
</body>
</html>
<?php 
	sqlsrv_close($conexion);
}?>