<?php require("includes/conexion.php");
PermitirAcceso(307);
//CALENDARIO DE COPLA GROUP SAS

$Cons_Tec="EXEC sp_ConsultarTecnicos '".$_SESSION['CodUser']."'";
$SQL_Tec=sqlsrv_query($conexion,$Cons_Tec);
//echo "Rows affected: ".sqlsrv_rows_affected($SQL_Tec)."<br />";
//$NextTec=sqlsrv_next_result($SQL_Tec);
$Cons="";
$Next="";//Siguiente ID del tecnico a mostrar
$Nombre="";//Para mostrar el nombre del tecnico
if(isset($_GET['id'])&&$_GET['id']!=""){
	while($row=sqlsrv_fetch_array($SQL_Tec)){
		if($row['ID_Tecnico']==base64_decode($_GET['id'])){
			break;
		}
	}
	$Cons="EXEC sp_ConsultarDatosCalendarioTecnico '".base64_decode($_GET['id'])."'";
	$Nombre=$row['NombreTecnico'];
	$row = sqlsrv_fetch_array($SQL_Tec);
	if($row['ID_Tecnico']!=""){
		$Next=$row['ID_Tecnico'];	
	}else{
		$SQL_Tec=sqlsrv_query($conexion,$Cons_Tec);
		$row=sqlsrv_fetch_array($SQL_Tec);
		$Next=$row['ID_Tecnico'];
	}	
}else{
	$row = sqlsrv_fetch_array($SQL_Tec);
	$Cons="EXEC sp_ConsultarDatosCalendarioTecnico '".$row['ID_Tecnico']."'";
	$Nombre=$row['NombreTecnico'];
	$row = sqlsrv_fetch_array($SQL_Tec);
	$Next=$row['ID_Tecnico'];
}
$SQL=sqlsrv_query($conexion,$Cons);
?>
<!DOCTYPE html>
<html>

<head>
<?php include("includes/cabecera.php"); ?>

<title><?php echo NOMBRE_PORTAL;?> | Calendario de t&eacute;cnicos</title>
<script>
var myVar = setInterval(RecargarTecnico, <?php echo (ObtenerVariable("TempRefresh")*1000);?>);

function RecargarTecnico() {
   window.location='calendario_actividades_tecnico_ajx.php?id=<?php echo base64_encode($Next); ?>';
}
</script>
<script type="text/javascript">
	$(document).ready(function() {//Cargar los almacenes dependiendo del proyecto
		$("#ClienteActividad").change(function(){
			$.ajax({
				type: "POST",
				url: "ajx_cbo_sucursales_clientes_simple.php?CardCode="+document.getElementById('ClienteActividad').value,
				success: function(response){
					$('#Sucursal').html(response).fadeIn();
				}
			});
		});
	});
</script>

</head>

<body class="gray-bg">
    <div class="lockscreen">
      <div class="row">
				<div class="col-lg-12">
			    <div class="ibox-content">
				  <form action="calendario_actividades_tecnico_ajx.php" method="post" id="formFiltro" class="form-horizontal">
					  	<div class="form-group">
							<div class="col-lg-12"><h1><strong>T&eacute;cnico:</strong> <?php echo $Nombre; ?></h1></div>
						</div>
				 </form>
			</div>
			</div>
		  </div>
			<br>
			<div class="row">
				<div class="col-lg-12">
					<div class="ibox-content">
						<div class="table-responsive">
							<div id="calendar"></div>
						</div>
					</div>
				</div> 
			</div>
    </div>
<?php include("includes/pie.php"); ?>
<script>

    $(document).ready(function() {
		$(".select2").select2();
		
        /* initialize the calendar
         -----------------------------------------------------------------*/
        $('#calendar').fullCalendar({
            header: {
                left: 'prev,next today',
                center: 'title',
                right: 'month,agendaWeek,agendaDay,listWeek'
            },
			defaultView: 'agendaWeek',
            editable: false,
			timeFormat: 'hh:mm a',
			eventRender: function(event, element){
				element.qtip({
					content: {
						title: event.subtitle,
						text: event.description
					},
					position: {
						target: 'mouse',
						adjust: { x: 5, y: 5 }
					}
				});
			},
            events: [
			<?php 
				while($row=sqlsrv_fetch_array($SQL)){
					if($row['TodoDia']==1){$AllDay="true";}else{$AllDay="false";}
					echo "{
						id: ".$row['ID_Actividad'].",
						title:'".$row['EtiquetaActividad']."',
						subtitle:'".$row['DE_AsuntoActividad']."',
						description:'".LSiqmlObs($row['ComentariosActividad'])."',
						start: '".$row['FechaHoraInicioActividad']."',
						end: '".$row['FechaHoraFinActividad']."',
						allDay: ".$AllDay.",
						textColor: '#ffffff',
						backgroundColor: '".$row['ColorPrioridadActividad']."',
						borderColor: '".$row['ColorPrioridadActividad']."'
					},";
				}
			?>
            ]		
        });
    });
</script>

</html>
<?php sqlsrv_close($conexion);?>
