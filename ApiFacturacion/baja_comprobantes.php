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
    'tipodoc'               =>  'RA', //RC: RESUMEN DE COMPROBANTES, RA: RESUMEN DE ANULACIONES
    'serie'                 =>  date('Ymd'), //fecha de envío
    'correlativo'           =>  1, //numero de envios en el dia, empezando desde 1
    'fecha_emision'         =>  date('Y-m-d'),
    'fecha_envio'           =>  date('Y-m-d')
);

$detalle = array();

$cant = 10;

for ($i=1; $i <=$cant ; $i++) { 
    $detalle[] = array(
        'item'              =>  $i,
        'tipodoc'           =>  '01',
        'serie'             =>  'F00' . rand(1, 9),
        'correlativo'       =>  rand(1, 500000),
        'motivo'            =>  'ERROR EN EL DOCUMENTO'
    );
}

//PASO 01: CREAR EL XML - BAJA DE COMPROBANTES
require_once('xml.php');
$objXML= new GeneradorXML();
$nombreXML = $emisor['ruc'] . '-' . $cabecera['tipodoc'] . '-' . $cabecera['serie'] . '-' . $cabecera['correlativo'];
$rutaXML = 'xml/';

$objXML->CrearXmlBajaDocumentos($emisor, $cabecera, $detalle, $rutaXML . $nombreXML);
echo '</br> PASO 01: XML DE BAJA DE COMPROBANTES CREADO';

//PASO 02
require_once('ApiFacturacion.php');
$objApi = new ApiFacturacion();
$ticket = $objApi->EnviarResumenComprobantes($emisor, $nombreXML);

//PASO 03 - Consultar el Ticket
if ($ticket > 0) {
    $objApi->ConsultarTicket($emisor, $cabecera, $ticket);
}



?>