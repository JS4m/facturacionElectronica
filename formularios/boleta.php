<?php
require_once("../apifacturacion/ado/clsEmisor.php");
require_once("../apifacturacion/ado/clsCompartido.php");

$objEmisor = new clsEmisor();
$listado = $objEmisor->consultarListaEmisores();

$objCompartido = new clsCompartido();
$monedas = $objCompartido->listarMonedas();

$comprobantes = $objCompartido->listarComprobantesCodigo('03');

$documentos = $objCompartido->listarTipoDocumento();

?>

<section class="content">
    <div class="container-fluid">
    <div class="row">
        <div class="col-12">
        <!-- Default box -->
            <div class="card card-info">
                <div class="card-header">
                <h3 class="card-title"> <i class="fas fa-shopping-cart"></i> BOLETA ELECTRONICA</h3>

                </div>
                <div class="card-body">
                
                <form id="frmVenta" name="frmVenta" submit="return false">
                    <div class="col-12">
                        <div class="row">	
                            <div class="col-4">
                                <div class="form-group">
                                    <label>Facturar Por</label>
                                    <select class="form-control" id="idemisor" name="idemisor">
                                        <?php while($fila = $listado->fetch(PDO::FETCH_NAMED)){ ?>
                                            <option value="<?php echo $fila['id'];?>"><?php echo $fila['razon_social'];?></option>
                                        <?php } ?>
                                    </select>
                                    <input type="hidden" name="accion" id="accion" value="GUARDAR_VENTA">
                                </div>
                                <div class="form-group">
                                    <label>Fecha</label>
                                    <input class="form-control" type="date" name="fecha_emision" id="fecha_emision" value="<?php echo date('Y-m-d');?>" />
                                </div>
                                <div class="form-group">
                                    <label>Moneda</label>
                                    <select class="form-control" type="date" name="moneda" id="moneda">
                                        <?php while($fila = $monedas->fetch(PDO::FETCH_NAMED)){ ?>
                                            <option value="<?php echo $fila['codigo'];?>"><?php echo $fila['descripcion'];?></option>
                                        <?php } ?>
                                    </select>
                                </div>



                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    <label>Tipo Comp.</label>
                                    <select class="form-control" name="tipocomp" id="tipocomp" onchange="ConsultarSerie()">
                                        <?php while($fila = $comprobantes->fetch(PDO::FETCH_NAMED)){ ?>
                                            <option value="<?php echo $fila['codigo'];?>"><?php echo $fila['descripcion'];?></option>
                                        <?php } ?>
                                    </select>
                                </div>	
                                <div class="form-group">
                                    <label>Serie</label>
                                    <select class="form-control" type="date" name="idserie" id="idserie" onchange="ConsultarCorrelativo()">
                                        
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Correlativo</label>
                                    <input class="form-control" type="number" name="correlativo" id="correlativo" />
                                </div>
                                

                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    <label>Tipo Doc.</label>
                                    <select class="form-control" name="tipodoc" id="tipodoc">
                                        <?php while($fila = $documentos->fetch(PDO::FETCH_NAMED)){ ?>
                                            <option value="<?php echo $fila['codigo'];?>"><?php echo $fila['descripcion'];?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Nro. Doc</label>
                                    <div class="input-group">
                                        <input class="form-control" type="text" name="nrodoc" id="nrodoc" />
                                        <div class="input-group-addon">
                                            <button type="button" class="btn btn-default" onclick="ObtenerDatosEmpresa()"><li class="fa fa-search"></li></button>	
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Nombre/Raz. Social</label>
                                    <input class="form-control" type="text" name="razon_social" id="razon_social" />
                                </div>
                                <div class="form-group">
                                    <label>Dirección</label>
                                    <input class="form-control" type="text" name="direccion" id="direccion" />

                                    <input type="hidden" id="forma_pago" name="forma_pago" value="Contado">

                                </div>  						
                            </div>


                            <div class="col-6">
                                <div class="input-group">
                                        <input class="form-control" type="text" name="producto" id="producto" placeholder="producto..." />
                                        <div class="input-group-addon">
                                            <button type="button" class="btn btn-default" onclick="BuscarProducto()"><li class="fa fa-search"></li></button>	
                                        </div>
                                    </div>
                                <div class="col-12">
                                    <table class="table table-bordered table-hover table-sm">
                                        <thead>
                                            <th>Cod</th>
                                            <th>Nombre</th>
                                            <th>Prec.</th>
                                            <th width="100">Cant.</th>
                                            <th>
                                            <button type="button" class="btn btn-info">  +</button> </th>
                                        </thead>
                                        <tbody id="div_productos">
                                            
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="col-12" id="div_carrito">
                                </div>
                                <div>
                                    <button type="button" class="btn btn-primary" onclick="GuardarVenta()"><i class="fa fa-save"></i> Guardar</button> 
                                    <button type="button" class="btn btn-danger" onclick="CancelarVenta();"> <i class="far fa-trash-alt"></i> Cancelar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                </div>
                <!-- /.card-body -->
                <div class="card-footer">
                
                </div>
                <!-- /.card-footer-->
            </div>
        <!-- /.card -->
        </div>
    </div>
    </div>
</section>

<script>
   $('#tipocomp').val('03'); //asignando el valor por defecto de Factura

   function ConsultarSerie(){
      $.ajax({
          method: "POST",
          url: 'apifacturacion/controlador/controlador.php',
          data: {
          	  "accion": "LISTAR_SERIES",
              "tipocomp": $("#tipocomp").val()
            }
      })
      .done(function( text ) {
            json = JSON.parse(text);            
            series = json.series;
            options = '';
            for(i=0;i<series.length;i++){
            	options = options + '<option value="'+series[i].id+'">'+series[i].serie+'</option>';
            }
            $("#idserie").html(options);
            ConsultarCorrelativo();
      });
  }


  ConsultarSerie();

  function ConsultarCorrelativo(){
      $.ajax({
          method: "POST",
          url: 'apifacturacion/controlador/controlador.php',
          data: {
          	  "accion": "OBTENER_CORRELATIVO",
              "idserie": $("#idserie").val()
            }
      })
      .done(function( correlativo ) {
            $("#correlativo").val(correlativo);
      });
  }

  function ObtenerDatosEmpresa(){
  		tipodoc = $("#tipodoc").val();
  		if(tipodoc == 1){
  			ObtenerDatosDni();
  		}else if(tipodoc == 6){
  			ObtenerDatosRuc();
  		}
  }

  function ObtenerDatosDni(){
      $.ajax({
          method: "POST",
          url: 'apifacturacion/controlador/controlador.php',
          data: {
              "accion": "CONSULTA_DNI",
              "dni": $("#nrodoc").val()
            }
      })
      .done(function( text ) {
            json = JSON.parse(text);
            $('#razon_social').val(json.result.Nombre+ ' '+json.result.Paterno+' '+json.result.Materno);
            $('#direccion').val('');
      });  		
  }

  function ObtenerDatosRuc(){
      $.ajax({
          method: "POST",
          url: 'apifacturacion/controlador/controlador.php',
          data: {
              "accion": "CONSULTA_RUC",
              "ruc": $("#nrodoc").val()
            }
      })
      .done(function( jsonx ) {
            json = JSON.parse(jsonx);
            $('#razon_social').val(json.result.razon_social);
            $('#direccion').val('');
      }); 
  }

  function BuscarProducto(){
      $.ajax({
          method: "POST",
          url: 'apifacturacion/controlador/controlador.php',
          data: {
          	  "accion": "BUSCAR_PRODUCTO",
              "filtro": $("#producto").val()
            }
      })
      .done(function( resultado ) {
            json = JSON.parse(resultado);            
            productos = json.productos;
            listado = '';
            for(i=0;i<productos.length;i++){
            	listado = listado + '<tr><td>'+productos[i].codigo+'</td><td>'+productos[i].nombre+'</td><td>'+productos[i].precio+'</td><td><input class="form-control input-sm" id="txtCantidad'+productos[i].codigo+'" value="1" type="number" min="1" /></td><td><button type="button" class="btn btn-primary btn-sm" onclick="AgregarCarrito('+productos[i].codigo+')"> + </button></td></tr>';
            }
            $("#div_productos").html(listado);
      });
  }

  function AgregarCarrito(codigo){
      $.ajax({
          method: "POST",
          url: 'apifacturacion/controlador/controlador.php',
          data: {
          	  "accion": "ADD_PRODUCTO",
              "codigo": codigo,
              "cantidad": $("#txtCantidad"+codigo).val()
            }
      })
      .done(function( html ) {
            $("#div_carrito").html(html);
      });  		
  }

  function CancelarVenta(codigo){
      $.ajax({
          method: "POST",
          url: 'apifacturacion/controlador/controlador.php',
          data: {
          	  "accion": "CANCELAR_CARRITO"
            }
      })
      .done(function( html ) {
            $("#div_carrito").html(html);
      });  		
  }

  function GuardarVenta(){
  	var datax = $("#frmVenta").serializeArray();

	$.ajax({
      method: "POST",
      url: 'apifacturacion/controlador/controlador.php',
      data: datax
      
  	})
  	.done(function( html ) {
        $("#div_carrito").html(html);
  	}); 

  }

</script>