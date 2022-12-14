<?php
require_once( "includes/conexion.php" );

if(isset($_POST['P'])&&($_POST['P']!="")){
	$Param=array(
		"'".$_POST['UsuarioOrigen']."'",
		"'".base64_decode($_POST['UsuarioDestino'])."'",
		"'".$_SESSION['CodUser']."'"
	);
	$SQL=EjecutarSP('sp_CopiarParametrosUsuarios',$Param);
	
	if($SQL){
		header('Location:usuarios.php?id='.$_POST['UsuarioDestino'].'&tl=1&a='.base64_encode('OK_CopyUser'));
	}
}

$SQL_ListaUsuarios=Seleccionar('uvw_tbl_Usuarios','ID_Usuario, Usuario, NombreUsuario','','NombreUsuario');

if(isset($_GET['id'])&&$_GET['id']!=""){
  $IdUsuario = base64_decode($_GET['id']);
}else{
  $IdUsuario = "";
}


?>
<style>
	.select2-container{ 
		width: 100% !important; 
	}
</style>
<div class="ibox-content">
	<?php include("includes/spinner.php"); ?>
	 <form action="us_copiar_usuario.php" method="post" class="form-horizontal" id="CopiarUsuario">				 
	<div class="form-group">
	<label class="col-lg-3 control-label">Traer datos desde este usuario</label>
	<div class="col-lg-6">
		<select name="UsuarioOrigen" class="form-control chosen-select" id="UsuarioOrigen">
			<option value="">Seleccione...</option>
		  <?php while($row_ListaUsuarios=sqlsrv_fetch_array($SQL_ListaUsuarios)){?>
				<option value="<?php echo $row_ListaUsuarios['ID_Usuario'];?>"><?php echo $row_ListaUsuarios['NombreUsuario']." (".$row_ListaUsuarios['Usuario'].")";?></option>
		  <?php }?>
		</select>
	</div>
	<div class="col-lg-3">
		<button class="btn btn-primary" type="submit" id="Copiar"><i class="fa fa-check"></i> Copiar datos</button>
	</div>
	</div>
	<input type="hidden" id="P" name="P" value="Copy" />
	<input type="hidden" id="UsuarioDestino" name="UsuarioDestino" value="<?php echo $_GET['id'];?>" />
</form>
</div>

<script>
	 $(document).ready(function(){
		  $("#CopiarUsuario").validate({
			 submitHandler: function(form){
				 $('.ibox-content').toggleClass('sk-loading');
				 form.submit();
				}
			});
		 $('.chosen-select').chosen({width: "100%"});
	
	});
</script>