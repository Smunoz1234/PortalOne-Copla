<?php
include_once("includes/definicion.php");
if (!isset($_SESSION)) {
  session_start();
}
if (isset($_SESSION['User'])&&$_SESSION['User']!="") {
	header('Location:index1.php');
	exit();
}
session_destroy();
if(isset($_POST['User'])||isset($_POST['Password'])){
	if(($_POST['User']=="")||($_POST['Password'])==""){
			//header('Location:index1.php');
			$log=0;
		}else{
			require_once("includes/conect_srv.php");
			require_once("includes/LSiqml.php");			
			$User=LSiqml($_POST['User']);
			$Pass=LSiqml($_POST['Password']);
					
			$Consulta="EXEC sp_ValidarUsuario '".$User."', '".md5($Pass)."'";
			//echo $Consulta;
			//exit();
			$SQL=sqlsrv_query($conexion,$Consulta,array(),array( "Scrollable" => 'Buffered' ));
			if($SQL){
				$Num=sqlsrv_num_rows($SQL);
				if($Num>0){
					$row=sqlsrv_fetch_array($SQL);
					session_start();
					$_SESSION['BD']=$_POST['BaseDatos'];
					$_SESSION['User']=strtoupper($row['Usuario']);
					$_SESSION['CodUser']=$row['ID_Usuario'];
					$_SESSION['NomUser']=$row['NombreUsuario'];
					$_SESSION['EmailUser']=$row['Email'];
					$_SESSION['Perfil']=$row['ID_PerfilUsuario'];
					$_SESSION['NomPerfil']=$row['PerfilUsuario'];
					$_SESSION['CambioClave']=$row['CambioClave'];
					$_SESSION['TimeOut']=$row['TimeOut'];
					$_SESSION['SetCookie']=$row['SetCookie'];
					if($row['CambioClave']==1){
						//echo "Ingreso al cambio";
						header('Location:login_cambio_clave.php');
					}else{
						$ConsUpdUltIng="Update tbl_Usuarios set FechaUltIngreso=GETDATE() Where ID_Usuario='".$_SESSION['CodUser']."'";
						if(sqlsrv_query($conexion,$ConsUpdUltIng)){
							sqlsrv_close($conexion);
							//echo "Ingreso al Index";
							header('Location:index1.php');
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
<?php include_once("includes/cabecera.php"); ?>
<title><?php echo NOMBRE_PORTAL;?> | Iniciar sesi&oacute;n</title>
</head>

<body class="gray-bg">

    <div class="middle-box text-center loginscreen animated fadeInDown">
        <div>
            <div>

                <h1 class="logo-name"><img src="img/img_logo.png" alt="" width="300" height="95"/></h1>

            </div>
            <h3>Bienvenido a <?php echo NOMBRE_PORTAL;?></h3>
			<p>En este portal pude gestionar fácilmente su información en tiempo real.<br>
            </p>
            <p><strong>Ingrese sus credenciales</strong></p>
            <form name="frmLogin" id="frmLogin" class="m-t" role="form" action="login.php" method="post" enctype="application/x-www-form-urlencoded">
                <div class="form-group">
                   	<select name="BaseDatos" id="BaseDatos" class="form-control">
						<option value="PortalClientes">PortalClientes</option>
              			<option value="PortalClientes_Pruebas">PortalClientes - Pruebas y capacitación</option>
               		</select>
                </div>
                 <div class="form-group">
                    <input name="User" type="text" autofocus required="" class="form-control" id="User" placeholder="Usuario" maxlength="50">
                </div>
                <div class="form-group">
                    <input name="Password" type="password" required="" class="form-control" id="Password" placeholder="Contraseña" maxlength="50">
                </div>
                <button type="submit" class="btn btn-primary block full-width m-b">Acceder <i class="fa fa-sign-in"></i></button>

                <a href="recordar_clave.php"><small>Recordar contrase&ntilde;a</small></a>
            </form>
            <p class="m-t"> <small>Todos los derechos reservados &copy; 2017 <br> <?php echo NOMBRE_EMPRESA;?></small> </p>
        </div>
    </div>
<?php if(isset($_POST['data'])&&$_POST['data']=="OK"){?>
<script>
	$(document).ready(function(){
		toastr.success('¡Su contraseña ha sido modificada!','Felicidades');
	});
</script>
<?php }?>
<script>	
	 $(document).ready(function(){		
		  $("#frmLogin").validate();
	});
</script>
<?php include_once("includes/pie.php"); ?>
</body>

</html>
