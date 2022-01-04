<?php

$emisor = array(
    'tipodoc'                   =>  '6', //Catálogo No. 06: Códigos de tipos de documentos de identidad
    'ruc'                       =>  '20123456789',
    'razon_social'              =>  'CETI',
    'nombre_comercial'          =>  'CETI',
    'direccion'                 =>  'CHICLAYO',
    'ubigeo'                    =>  '130101',
    'departamento'              =>  'LAMBAYEQUE',
    'provincia'                 =>  'CHICLAYO',
    'distrito'                  =>  'CHICLAYO',
    'pais'                      =>  'PE',
    'usuario_secundario'        =>  'MODDATOS', //MODDATOS:USUARIO DE PRUEBA O BETA DE SUNAT
    'clave_usuario_secundario'  =>  'MODDATOS'
);

$cabecera = array(
    'tipodoc'               =>  'RC', //RC: RESUMEN DE COMPROBANTES, RA: RESUMEN DE ANULACIONES
    'serie'                 =>  date('Ymd'), //fecha de envío
    'correlativo'           =>  1, //numero de envios en el dia, empezando desde 1
    'fecha_emision'         =>  date('Y-m-d'),
    'fecha_envio'           =>  date('Y-m-d')
);

$detalle = array();

$cant = 500;

for ($i=1; $i <=$cant ; $i++) { 
    $item_total = rand(100, 690);
    $item_valor = $item_total / 1.18;
    $item_valor = (float) number_format($item_valor, 2, '.', 1);
    $item_igv = $item_total - $item_valor;

    $detalle[] = array(
        'item'                  =>  $i,
        'tipodoc'                =>  '03',
        'serie'                 =>  'B00' . rand(1, 9),
        'correlativo'           =>  rand(100, 500000),
        'condicion'             =>  rand(1,3), //1: alta, 2:modificacion, 3:baja o anulacion
        'moneda'                =>  'PEN',
        'importe_total'         =>  $item_total,
        'valor_total'           =>  $item_valor,
        'igv_total'             =>  $item_igv,
        'tipo_total'            =>  '01', //01:gravados, 02;exo, 03:ina
        'codigo_afectacion'     =>  '1000',
        'nombre_afectacion'     =>  'IGV',
        'tipo_afectacion'       =>  'VAT'
    );
}

//PASO 01: CREAR XML DE RESUMEN DE COMPROBANTES - INICIO
require_once('xml.php');
$objXML = new GeneradorXML();
$nombreXML = $emisor['ruc'] . '-' . $cabecera['tipodoc'] . '-' . $cabecera['serie'] . '-' . $cabecera['correlativo'];
$rutaXML = 'xml/';

$objXML->CrearXMLResumenDocumentos($emisor, $cabecera, $detalle, $rutaXML . $nombreXML);
echo '</br> PASO 01: XML DE RESUMEN DE COMPROBANTES CREADO SATISFACTORIAMENTE';

//PASO 01: CREAR XML DE RESUMEN DE COMPROBANTES - FIN

//PASO 02 LLAMAR AL API-FACTUCACION
require_once('ApiFacturacion.php');
$ApiFac = new ApiFacturacion();
$ticket = $ApiFac->EnviarResumenComprobantes($emisor, $nombreXML);

if ($ticket > 0) {
    //CONSULTAR TICKET
    $ApiFac->ConsultarTicket($emisor, $cabecera, $ticket);
}



?>