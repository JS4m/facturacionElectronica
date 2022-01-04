<?php

//importar las librerias de PDF y QR
define('FPDF_FONTPATH', 'font/');
require_once('fpdf/fpdf.php');
require_once('phpqrcode/qrlib.php');

//importar las librerias de acceso a datos
require_once('ado/clsCliente.php');
require_once('ado/clsCompartido.php');
require_once('ado/clsEmisor.php');
require_once('ado/clsVenta.php');

//otras librerias
require_once('cantidad_en_letras.php');

//crear los objetos de AD
$objCliente = new clsCliente();
$objCompartido = new clsCompartido();
$objVenta = new clsVenta();
$objEmisor = new clsEmisor();

//obtener el ID de la venta
$id = $_GET['id'];

$venta = $objVenta->obtenerComprobanteId($id);
$venta = $venta->fetch(PDO::FETCH_NAMED);

$detalle = $objVenta->listarDetalleComprobanteId($id);

$emisor = $objEmisor->obtenerEmisor($venta['idemisor']);
$emisor = $emisor->fetch(PDO::FETCH_NAMED);

$tipo_comprobante = $objCompartido->obtenerComprobante($venta['tipocomp']);
$tipo_comprobante = $tipo_comprobante->fetch(PDO::FETCH_NAMED);

$cliente = $objCliente->consultarClientePorCodigo($venta['codcliente']);
$cliente = $cliente->fetch(PDO::FETCH_NAMED);


//Crear el PDF
$pdf = new FPDF();
$pdf->AddPage('P', 'A4'); //Orientacion y Tamaño de hoja

//Agregar imagen
$pdf->Image('logo_empresa.jpg', 50, 2, 25, 25);
$pdf->Ln(20);

$pdf->SetAutoPageBreak('auto', 2);
$pdf->SetDisplayMode(75);


//Codigo de datos
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(100, 6, $emisor['ruc'] . '-' . $emisor['razon_social']);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(80, 6, $emisor['ruc'], 'LRT', 1, 'C', 0);


$pdf->SetFont('Arial', '', 8);
$pdf->Cell(100, 6, $emisor['direccion']);


$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(80, 6, $tipo_comprobante['descripcion'] . ' ELECTRONICA', 'LR', 1, 'C', 0);
$pdf->Cell(100);
$pdf->Cell(80, 6, $venta['serie'] . '-' . $venta['correlativo'], 'BLR', 0, 'C', 0);

$pdf->Ln();

//Datos del cliente
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(30, 6, 'NUM DOC: ', 0, 0, 'L', 0);
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(30, 6, $cliente['nrodoc'], 0, 1, 'L', 0);

$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(30, 6, 'CLIENTE: ', 0, 0, 'L', 0);
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(30, 6, $cliente['razon_social'], 0, 1, 'L', 0);

$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(30, 6, 'DIRECCION: ', 0, 0, 'L', 0);
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(30, 6, $cliente['direccion'], 0, 1, 'L', 0);

$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(30, 6, 'FECHA EMISION: ', 0, 0, 'L', 0);
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(30, 6, $venta['fecha_emision'], 0, 1, 'L', 0);

$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(30, 6, 'FORMA DE PAGO: ', 0, 0, 'L', 0);
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(30, 6, $venta['forma_pago'], 0, 1, 'L', 0);

$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(30, 6, 'MONTO PENDIENTE: ', 0, 0, 'L', 0);
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(30, 6, $venta['monto_pendiente'], 0, 1, 'L', 0);

$pdf->Ln(3);

//Detalle de venta
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(20, 6, 'ITEM', 1, 0, 'C', 0);
$pdf->Cell(25, 6, 'CANTIDAD', 1, 0, 'C', 0);
$pdf->Cell(100, 6, 'PRODUCTO', 1, 0, 'C', 0);
$pdf->Cell(20, 6, 'V.U.', 1, 0, 'C', 0);
$pdf->Cell(25, 6, 'SUBTOTAL', 1, 1, 'C', 0);

$pdf->SetFont('Arial', '', 8);

while ($fila = $detalle->fetch(PDO::FETCH_NAMED)) {
    $pdf->Cell(20, 6, $fila['item'], 1, 0, 'C', 0);
    $pdf->Cell(25, 6, $fila['cantidad'], 1, 0, 'R', 0);
    $pdf->Cell(100, 6, $fila['nombre'], 1, 0, 'L', 0);
    $pdf->Cell(20, 6, $fila['valor_unitario'], 1, 0, 'R', 0);
    $pdf->Cell(25, 6, $fila['valor_total'], 1, 1, 'R', 0);
}

$pdf->Cell(165, 6, 'OP.GRAVADAS', '', 0, 'R', 0);
$pdf->Cell(25, 6, $venta['op_gravadas'], 1, 1, 'R', 0);

$pdf->Cell(165, 6, 'OP.EXONERADAS', '', 0, 'R', 0);
$pdf->Cell(25, 6, $venta['op_exoneradas'], 1, 1, 'R', 0);

$pdf->Cell(165, 6, 'OP.INAFECTAS', '', 0, 'R', 0);
$pdf->Cell(25, 6, $venta['op_inafectas'], 1, 1, 'R', 0);

$pdf->Cell(165, 6, 'IGV 18%', '', 0, 'R', 0);
$pdf->Cell(25, 6, $venta['igv'], 1, 1, 'R', 0);

$pdf->Cell(165, 6, 'IMPORTE TOTAL', '', 0, 'R', 0);
$pdf->Cell(25, 6, $venta['total'], 1, 1, 'R', 0);

$pdf->Ln(10);
$pdf->Cell(165, 6, utf8_decode('SON: ' . CantidadEnLetra($venta['total'])), 0, 0, 'C', 0);
$pdf->Ln(20);

//Codigo QR
//RUC | TIPO DE DOCUMENTO | SERIE | NUMERO | MTO TOTAL IGV | MTO TOTAL DEL COMPROBANTE | FECHA DE EMISION | TIPO DE DOCUMENTO ADQUIRENTE | NUMERO DE DOCUMENTO ADQUIRENTE |

$ruc = $emisor['ruc'];
$tipo = $venta['tipocomp'];
$serie = $venta['serie'];
$correlativo = $venta['correlativo'];
$igv = $venta['igv'];
$total = $venta['total'];
$fecha = $venta['fecha_emision'];
$tipcl = $cliente['tipodoc'];
$nrocl = $cliente['nrodoc'];

$texto_qr = $ruc . '|' . $tipo . '|' . $serie . '|' . $correlativo . '|' . $igv . '|' . $total . '|' . $fecha . '|' . $tipcl . '|' . $nrocl . '|';

$nombre_qr = $ruc . '-' . $tipo . '-' . $serie . '-' . $correlativo;
$ruta_qr = $nombre_qr . '.PNG';

QRcode::png($texto_qr, $ruta_qr, 'Q', 15 , 0);

$pdf->Image($ruta_qr, 80, $pdf->GetY(), 25, 25);

$pdf->ln(30);
$pdf->Cell(165, 6, utf8_decode('Representación impresa de la factura electrónica'), 0,0,'C',0);
$pdf->ln(10);
$pdf->Cell(165, 6, utf8_decode('Este comprobante puede ser consultado en ceti.org.pe'), 0,0,'C',0);


//Salida del PDF
$pdf->Output('I', $nombre_qr . '.PDF');


?>