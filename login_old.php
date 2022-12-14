<?php
include("includes/definicion.php");
if (!isset($_SESSION)) {
  session_start();
}
if (isset($_SESSION['User'])&&$_SESSION['User']!="") {
	header('Location:index1.php');
	exit();
}
session_destroy();
$log=1;
if(isset($_POST['User'])||isset($_POST['Password'])){
	if(($_POST['User']=="")||($_POST['Password'])==""){
			//header('Location:index1.php');
			$log=0;
		}else{
			require("includes/conect_srv.php");
			require("includes/LSiqml.php");			
			$User=LSiqmlLogin($_POST['User']);
			$Pass=LSiqmlLogin($_POST['Password']);
					
			$Consulta="EXEC sp_ValidarUsuario '".$User."', '".md5($Pass)."'";
			//echo $Consulta;
			//exit();
			$SQL=sqlsrv_query($conexion,$Consulta,array(),array( "Scrollable" => 'Buffered' ));
			if($SQL){
				$Num=sqlsrv_num_rows($SQL);
				if($Num>0){
					$row=sqlsrv_fetch_array($SQL);
					session_start();
					$_SESSION['BD']=$database;//Del archivo conect
					$_SESSION['User']=strtoupper($row['Usuario']);
					$_SESSION['CodUser']=$row['ID_Usuario'];
					$_SESSION['NomUser']=$row['NombreUsuario'];
					$_SESSION['EmailUser']=$row['Email'];
					$_SESSION['Perfil']=$row['ID_PerfilUsuario'];
					$_SESSION['NomPerfil']=$row['PerfilUsuario'];
					$_SESSION['CambioClave']=$row['CambioClave'];
					$_SESSION['TimeOut']=$row['TimeOut'];
					$_SESSION['CodigoSAP']=$row['CodigoSAP'];
					$_SESSION['IdCardCode']=$row['IdCardCode'];
					$_SESSION['CodigoEmpVentas']=$row['IdEmpVentas'];
					$_SESSION['SetCookie']=$row['SetCookie'];
					$_SESSION['CodigoSAPProv']=$row['CodigoSAPProv'];
					$_SESSION['NIT']=$row['NIT'];
					$_SESSION['Sucursal']=$row['BranchName'];
					$_SESSION['CodSucursal']=$row['Branch'];
					$_SESSION['Dpto']=$row['DeptName'];
					$_SESSION['CodDpto']=$row['Dept'];
					$_SESSION['Ext']=$row['Extension'];
					if($row['CambioClave']==1){
						//echo "Ingreso al cambio";
						header('Location:login_cambio_clave.php');
					}else{
						$ConsUpdUltIng="Update tbl_Usuarios set FechaUltIngreso=GETDATE() Where ID_Usuario='".$_SESSION['CodUser']."'";
						if(sqlsrv_query($conexion,$ConsUpdUltIng)){
							sqlsrv_close($conexion);
							if(isset($_POST['return_url'])&&$_POST['return_url']!=""){
								header('Location:'.base64_decode($_POST['return_url']));
							}else{
								header('Location:index1.php');
							}
						}else{
							sqlsrv_close($conexion);
							echo "Error de ingreso. Fecha invalida.";
							}
					}					
				}else{
					$log=0;
					sqlsrv_close($conexion);
				}
			}else{
				$log=0;
				sqlsrv_close($conexion);
			}
		}
	}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<title><?php echo NOMBRE_PORTAL;?> | Iniciar sesi&oacute;n</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="x-ua-compatible" content="IE=edge,chrome=1">
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
<link rel="shortcut icon" href="css/favicon.png" />
<link href="https://fonts.googleapis.com/css?family=Roboto:300,300i,400,400i,500,500i,700,700i,900" rel="stylesheet">
<link rel="stylesheet" href="css/bootstrap.css" class="theme-settings-bootstrap-css">
<link rel="stylesheet" href="css/appwork.css" class="theme-settings-appwork-css">
<link rel="stylesheet" href="css/theme-corporate.css" class="theme-settings-theme-css">
<link rel="stylesheet" href="css/uikit.css">
<link rel="stylesheet" href="css/authentication.css">
<link rel="stylesheet" href="css/toastr.css">
<script src="js/jquery-3.1.1.min.js"></script>
<script src="js/bootstrap.js"></script>
<script src="js/toastr.js"></script>
<script src="js/plugins/validate/jquery.validate.min.js"></script>
<style>
#myVideo {
    position: fixed;
    right: 0;
    bottom: 0;
    min-width: 100%;
    min-height: 100%;
}
</style>
<script>
	document.oncontextmenu = function(){return false;}
</script>
</head>

<body>
  <div class="page-loader">
    <div class="bg-primary"></div>
  </div>

  <!-- Content -->

  <div class="authentication-wrapper authentication-3">
    <div class="authentication-inner">

      <!-- Side container -->
      <!-- Do not display the container on extra small, small and medium screens -->
      <div class="d-none d-lg-flex col-lg-8 align-items-center ui-bg-cover ui-bg-overlay-container p-5">
        <div class="ui-bg-overlay bg-dark opacity-50"></div>
		<video autoplay muted loop id="myVideo">
		  <source src="img/vid_background.mp4" type="video/mp4">
		  Tu navegador debe soportar HTML5
		</video>
        <!-- Text -->
        <div class="w-100 text-white px-5">
          <h1 class="display-2 font-weight-bolder mb-4">BIENVENIDO
            <br>Portal One</h1>
          <div class="text-large font-weight-light">
            Sistema de gesti&oacute;n y control de documentos, creaci&oacute;n de documentos de marketing para SAP Business One, gestor de tareas y portal de proveedores. Todo en un mismo lugar.
          </div>
        </div>
        <!-- /.Text -->
      </div>
      <!-- / Side container -->

      <!-- Form container -->
      <div class="d-flex col-lg-4 align-items-center bg-white p-5">
        <!-- Inner container -->
        <!-- Have to add `.d-flex` to control width via `.col-*` classes -->
        <div class="d-flex col-sm-7 col-md-5 col-lg-12 px-0 px-xl-4 mx-auto">
          <div class="w-100">

            <!-- Logo -->
			   <div class="d-flex justify-content-center align-items-center">
				<img src="img/img_logo.png" alt="Logo" />
			  </div>
			   <div class="d-flex justify-content-center align-items-center">
				<img src="img/img_logo_cliente.png" alt="Cliente" height="90" width="230" />
			  </div>
            <!-- / Logo -->

            <h4 class="text-center text-lighter font-weight-normal mt-5 mb-0">Inicio de sesi&oacute;n</h4>

            <!-- Form -->
			<form name="frmLogin" id="frmLogin" class="my-5" role="form" action="login.php" method="post" enctype="application/x-www-form-urlencoded">
					<div class="form-group">
						<select name="BaseDatos" id="BaseDatos" class="form-control">
							<option value="<?php echo BDPRO; ?>"><?php echo BDPRO; ?></option>
							<?php if(BDPRUEBAS!=""){?>
							<option value="<?php echo BDPRUEBAS; ?>"><?php echo BDPRUEBAS; ?></option>
							<?php }?>
						</select>
					</div>
					 <div class="form-group">
						<label class="form-label">Usuario</label>
						<input name="User" type="text" autofocus required="" class="form-control" id="User" maxlength="50">
					</div>
					<div class="form-group">
						<label class="form-label d-flex justify-content-between align-items-end">
						  <div>Contrase&ntilde;a</div>
						  <a href="recordar_clave.php" class="d-block small">&iquest;Olvidaste tu contrase&ntilde;a?</a>
						</label>
						<input name="Password" type="password" required="" class="form-control" id="Password" maxlength="50">
					</div>
					<div class="d-flex justify-content-between align-items-center m-0">
						<label class="custom-control custom-checkbox m-0">
						  <input type="checkbox" class="custom-control-input">
						  <span class="custom-control-label">Recuerdame en este equipo</span>
						</label>
						<button type="submit" class="btn btn-primary">Ingresar</button>
				  </div>
				  <input type="hidden" id="return_url" name="return_url" value="<?php if(isset($_GET['return_url'])){echo $_GET['return_url'];}?>" />
				</form>
            <!-- / Form -->
			  <div class="text-light text-center small mt-4">
                <?php include("includes/copyright.php"); ?>
              </div>
          </div>
        </div>
      </div>
      <!-- / Form container -->

    </div>
  </div>
<?php if(isset($_POST['data'])&&$_POST['data']=="OK"){?>
<script>
	$(document).ready(function(){
		toastr.success('??Su contrase??a ha sido modificada!','Felicidades');
	});
</script>
<?php }?>
<?php if($log==0){?>
<script>
	$(document).ready(function(){
		toastr.error('Por favor compruebe su Usuario y Contrase??a.','Error de ingreso');
	});
</script>
<?php }?>
<script>	
	 $(document).ready(function(){		
		  $("#frmLogin").validate();
	});
</script>
<?php include("includes/pie.php"); ?>
</body>

</html>