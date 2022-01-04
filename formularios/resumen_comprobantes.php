<?php

require_once('../ApiFacturacion/ado/clsVenta.php');
require_once('../ApiFacturacion/ado/clsEmisor.php');

$objVenta = new clsVenta();
$objEmisor = new clsEmisor();

$listado = $objEmisor->consultarListaEmisores();

$listadoBoletas = $objVenta->listarComprobantePorTipo('03');
?>

<form id="frmResumen" name="frmResumen" submit='return false'>
    <div class="col-md-12">
        <div class="form-group">
            <label for="">Envío Resumen Diario</label>
            </br>
            <label for="">Facturar por:</label>
            <select class="form-control" id="idemisor" name="idemisor">
                <?php while($fila = $listado->fetch(PDO::FETCH_NAMED)){ ?>
                    <option value="<?php echo $fila['id'];?>"><?php echo $fila['razon_social'];?></option>
                <?php } ?>
            </select>
        </div>
    </div>

    <input type="hidden" name="accion" value="ENVIO_RESUMEN">
    <input type="hidden" name="ids" id="ids" value="0">

    <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th>*</th>
                <th>ID</th>
                <th>FECHA</th>
                <th>SERIE</th>
                <th>CORRELATIVO</th>
            </tr>
        </thead>
        <tbody>
            <?php while($fila = $listadoBoletas->fetch(PDO::FETCH_NAMED)){ ?>
                <tr>
                    <td><input type="checkbox" name="documento[]" value="<?php echo $fila['id'];?>" />
                    </td>
                    <td><?php echo $fila['id'];?></td>
                    <td><?php echo $fila['fecha_emision'];?></td>
                    <td><?php echo $fila['serie'];?></td>
                    <td><?php echo $fila['correlativo'];?></td>
                </tr>
                <?php } ?>
        </tbody>
    </table>
</form>

<div align="right" class="col-md-12">
	<button class="btn btn-primary" type="button" onclick="EnviarResumenComprobantes()">Enviar Comprobantes</button>
</div>
<div id="divResultado">
	
</div>
<script>
	function EnviarResumenComprobantes(){
	  	var datax = $("#frmResumen").serializeArray();

		$.ajax({
	      method: "POST",
	      url: 'apifacturacion/controlador/controlador.php',
	      data: datax
	  	})
	  	.done(function( html ) {
	        $("#divResultado").html(html);
	  	}); 		
	}
</script>