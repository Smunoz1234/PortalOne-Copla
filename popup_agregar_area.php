<?php 
if((isset($_GET['cardcode']))||(isset($_POST['cardcode']))){
	require_once("includes/conexion.php");
	if((isset($_GET['cardcode']))&&($_GET['cardcode']!="")){
		$CardCode=base64_decode($_GET['cardcode']);
		//$Sucursal=base64_decode($_GET['suc']);

		$SQL_CLiente=Seleccionar('uvw_Sap_tbl_Clientes','CodigoCliente, NombreCliente',"CodigoCliente='".$CardCode."'");
		$row_Cliente=sqlsrv_fetch_array($SQL_CLiente);
		
		//Anillos de áreas
		$SQL_Anillos=Seleccionar('uvw_tbl_Areas_Anillos','*','','NombreAnillo');
	}elseif(isset($_POST['MMInsert'])){
		$ParamInsArea=array(
			"'".strtoupper($_POST['Area'])."'",
			"'".$_POST['cardcode']."'",
			"'".$_POST['Anillo']."'",
			"'".$_SESSION['CodUser']."'",
			"1"
		);
		$SQL_InsArea=EjecutarSP('sp_tbl_Areas_Clientes',$ParamInsArea,101);
		if($SQL_InsArea){
			$row_NewId=sqlsrv_fetch_array($SQL_InsArea);
			$IdArea=$row_NewId[0];
			$DeArea=$row_NewId[1];
			?>
			<script>
				var selArea =  window.opener.document.getElementById("Area");
				var option =  window.opener.document.createElement("option");
				option.value = <?php echo $IdArea; ?>;
				option.text = "<?php echo $DeArea; ?>";
				selArea.add(option);
				selArea.value= <?php echo $IdArea; ?>;
				window.close();
			</script>
			<?php
		}
	}else{
		exit();
	}	
?>
<!doctype html>
<html>
<head>
<?php include_once("includes/cabecera.php"); ?>
<title><?php echo NOMBRE_PORTAL;?> | Agregar área</title>
</head>

<body>
	<div class="ibox-content">
		<?php include("includes/spinner.php"); ?>
		<div class="row"> 
			<div class="col-lg-12">
				<form action="popup_agregar_area.php" method="post" class="form-horizontal" id="FrmAgregar">
					<div class="form-group">
						<label class="col-xs-12"><h3 class="bg-muted p-xs b-r-sm"><i class="fa fa-dot-circle-o"></i> Agregar área en sucursal del cliente</h3></label>
					</div>
					<div class="form-group">
						<label class="col-12"><span class="pull-right">Cliente</span></label>
						<div class="col-12">
							<p><?php echo $row_Cliente['NombreCliente'];?></p>
						</div>
					</div>
					<div class="form-group">
						<label class="col-12 control-label">Área nueva</label>
						<div class="col-12">
							<input autocomplete="off" autofocus name="Area" type="text" required="required" class="form-control" id="Area" maxlength="50" placeholder="Ingrese el nombre de la nueva área">
						</div>
					</div>	
					<div class="form-group">
						<label class="col-12 control-label">Anillo</label>
						<div class="col-12">
							<select name="Anillo" class="form-control m-b" required="required" id="Anillo">
									<option value="">Seleccione...</option>
							  <?php while($row_Anillos=sqlsrv_fetch_array($SQL_Anillos)){?>
									<option value="<?php echo $row_Anillos['IdAnillo'];?>" style="color: <?php echo $row_Anillos['Color'];?>"><?php echo $row_Anillos['NombreAnillo'];?></option>
							  <?php }?>
							</select>
						</div>
					</div>	
					<div class="form-group pull-right">
						<button class="btn btn-primary" form="FrmAgregar" type="submit" id="Agregar"><i class="fa fa-check"></i> Agregar área</button> 
					</div>
					<input type="hidden" id="cardcode" name="cardcode" value="<?php echo $CardCode; ?>" />
					<input type="hidden" id="MMInsert" name="MMInsert" value="1" />
				</form>
			</div>
		</div>
	</div>

<script>
	 $(document).ready(function(){
		 $("#FrmAgregar").validate({
			 submitHandler: function(form){
				 $('.ibox-content').toggleClass('sk-loading');
				 form.submit();
				}
			});
	 });
</script>
</body>
</html>
<?php 
	sqlsrv_close( $conexion );
}?>