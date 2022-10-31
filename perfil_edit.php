<?php require_once("includes/conexion.php");
PermitirAcceso(203);
$Cons="Select * From uvw_tbl_PerfilesUsuarios Where ID_PerfilUsuario=".base64_decode($_GET['id']);
$SQL=sqlsrv_query($conexion,$Cons);
$row=sqlsrv_fetch_array($SQL);

$Cons_Permisos="Select * From uvw_tbl_NombresPermisosPerfiles";
$SQL_Permisos=sqlsrv_query($conexion,$Cons_Permisos);

$Cons_RelPermiso="Select * From uvw_tbl_PermisosPerfiles Where ID_PerfilUsuario='".base64_decode($_GET['id'])."'";
$SQL_RelPermiso=sqlsrv_query($conexion,$Cons_RelPermiso);
$PermisosPerfil=array();
$i=0;
while($row_RelPermiso=sqlsrv_fetch_array($SQL_RelPermiso)){
	$PermisosPerfil[$i]=$row_RelPermiso['ID_Permiso'];
	$i++;
}
?>
<!DOCTYPE html>
<html><!-- InstanceBegin template="/Templates/PlantillaPrincipal.dwt.php" codeOutsideHTMLIsLocked="false" -->

<head>
<?php include_once("includes/cabecera.php"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Editar perfil | <?php echo NOMBRE_PORTAL;?></title>
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
                    <h2>Editar perfil</h2>
                    <ol class="breadcrumb">
                        <li>
                            <a href="index1.php">Inicio</a>
                        </li>
                        <li>
                            <a href="#">Administraci&oacute;n</a>
                        </li>
                        <li>
                            <a href="gestionar_perfiles.php">Gestionar perfiles</a>
                        </li>
                        <li class="active">
                            <strong>Editar perfil</strong>
                        </li>
                    </ol>
                </div>
            </div>
           
         <div class="wrapper wrapper-content">
          <div class="row">
           <div class="col-lg-12">
              <form action="registro.php" method="post" class="form-horizontal" id="EditarPerfil">
				<div class="ibox-content">  
					<div class="form-group">
						<label class="col-lg-1 control-label">Nombre perfil</label>
						<div class="col-lg-3"><input name="NombrePerfil" type="text" required="required" class="form-control" id="NombrePerfil" maxlength="100" value="<?php echo $row['PerfilUsuario'];?>"><input type="hidden" name="ID_PerfilUsuario" id="ID_PerfilUsuario" value="<?php echo $row['ID_PerfilUsuario'];?>"></div>
					</div>
					<div class="form-group">
						<div class="col-lg-9">
							<button class="btn btn-warning" type="submit" id="Actualizar"><i class="fa fa-refresh"></i>&nbsp;Actualizar</button>  
							<a href="gestionar_perfiles.php" class="btn btn-outline btn-default"><i class="fa fa-arrow-circle-o-left"></i> Regresar</a>
						</div>
					</div>
				</div>
				<br>
				<div class="form-group">
					<div class="col-lg-5"><h4>Seleccionar permisos para este perfil</h4></div>
		  		</div>
				 <div class="ibox-content">  
		  		<div class="form-group">
		  			<div class="col-lg-10">
		  			<div class="table-responsive">
						<table class="table table-bordered">
							<thead>
							<tr>
								<th>Seleccionar</th>
								<th>Funci&oacute;n</th>
								<th>Descripci&oacute;n</th>
							</tr>
							</thead>
							<tbody>
						<?php while($row_Permisos=sqlsrv_fetch_array($SQL_Permisos)){
							if($row_Permisos['ID_Padre']==0){ ?>
								<tr class="warning">
									<td colspan="3"><strong><?php echo $row_Permisos['NombreFuncion'];?></strong></td>
								</tr>
						<?php
								$Cons_Padre="Select * From uvw_tbl_NombresPermisosPerfiles Where ID_Padre='".$row_Permisos['ID_Permiso']."'";
								$SQL_Padre=sqlsrv_query($conexion,$Cons_Padre);
								while($row_Padre=sqlsrv_fetch_array($SQL_Padre)){
									if(strlen($row_Padre['ID_Permiso'])==2){ ?>
										<tr class="info">
											<td colspan="3"><strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $row_Padre['NombreFuncion'];?></strong></td>
										</tr>								
						<?php		
										$Cons_Hijo="Select * From uvw_tbl_NombresPermisosPerfiles Where ID_Padre='".$row_Padre['ID_Permiso']."'";
										$SQL_Hijo=sqlsrv_query($conexion,$Cons_Hijo);
										while($row_Hijo=sqlsrv_fetch_array($SQL_Hijo)){ ?>
											<tr>
												<td>
													<div class="switch">
														<div class="onoffswitch">
															<input name="Permiso[]" type="checkbox" class="onoffswitch-checkbox" id="<?php echo $row_Hijo['ID_Permiso'];?>" value="<?php echo $row_Hijo['ID_Permiso'];?>" <?php if(in_array($row_Hijo['ID_Permiso'],$PermisosPerfil)){ echo "checked";}?>>
															<label class="onoffswitch-label" for="<?php echo $row_Hijo['ID_Permiso'];?>">
																<span class="onoffswitch-inner"></span>
																<span class="onoffswitch-switch"></span>
															</label>
														</div>
													</div>
												</td>
												<td><?php echo $row_Hijo['NombreFuncion'];?></td>
												<td><?php echo $row_Hijo['Descripcion'];?></td>
											</tr>
											<?php
										}
									}else{
										?>
											<tr>
												<td>
													<div class="switch">
														<div class="onoffswitch">
															<input name="Permiso[]" type="checkbox" class="onoffswitch-checkbox" id="<?php echo $row_Padre['ID_Permiso'];?>" value="<?php echo $row_Padre['ID_Permiso'];?>" <?php if(in_array($row_Padre['ID_Permiso'],$PermisosPerfil)){ echo "checked";}?>>
															<label class="onoffswitch-label" for="<?php echo $row_Padre['ID_Permiso'];?>">
																<span class="onoffswitch-inner"></span>
																<span class="onoffswitch-switch"></span>
															</label>
														</div>
													</div>
												</td>
												<td><?php echo $row_Padre['NombreFuncion'];?></td>
												<td><?php echo $row_Padre['Descripcion'];?></td>
											</tr>
											<?php
									}
								}
							}
						}
					?>
							</tbody>
						</table>
						</div>
					</div>
				  </div>
				  </div>
				 <input type="hidden" id="P" name="P" value="8" />
			  </form>
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
		 $("#AgregarPerfil").validate();
	});
</script>
<!-- InstanceEndEditable -->
</body>

<!-- InstanceEnd --></html>
<?php sqlsrv_close($conexion);?>