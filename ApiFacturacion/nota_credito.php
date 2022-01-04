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

$cliente = array(
    'tipodoc'                   =>  '6',
    'ruc'                       =>  '10435350378',
    'razon_social'              =>  'Fernando Quiroz',
    'direccion'                 =>  'Lima',
    'pais'                      =>  'PE'
);


$comprobante = array(
    'tipodoc'                       =>  '07',
    'serie'                         =>  'FNC1',
    'correlativo'                   =>  29,
    'fecha_emision'                 =>  date('Y-m-d'),
    'moneda'                        =>  'PEN',
    'total_opgravadas'              =>  0,
    'total_opexoneradas'            =>  0,
    'total_opinafectas'             =>  0,
    'igv'                           =>  0,
    'total'                         =>  0,
    'total_texto'                   =>  '',

    'tipodoc_ref'                   =>  '01',
    'serie_ref'                     =>  'F001',
    'correlativo_ref'               =>  '1001',

    'codmotivo'                     =>  '01',
    'descripcion'                   =>  'ANULACION DE LA OPERACION'
);


$detalle = array(
    array(
        'item'                          =>  '1',
        'codigo'                        =>  'CODPROD01',
        'descripcion'                   =>  'ACEITE',
        'cantidad'                      =>  2,
        'valor_unitario'                =>  50, //SIN IGV = 0
        'precio_unitario'               =>  59, //CON IGV = 18%
        'tipo_precio'                   =>  '01',
        'igv'                           =>  18,
        'porcentaje_igv'                =>  18,
        'valor_total'                   =>  100,
        'importe_total'                 =>  118,
        'unidad'                        =>  'NIU',
        'codigo_afectacion_alt'         =>  '10', //10:gravados, 20:exonerados, 30:inafectos Catálogo No. 07: Códigos de tipo de afectación del IGV
        'codigo_afectacion'             =>  '1000', //Catálogo No. 05: Códigos de tipos de tributos
        'nombre_afectacion'             =>  'IGV', //Catálogo No. 05: Códigos de tipos de tributos
        'tipo_afectacion'               =>  'VAT' //Catálogo No. 05: Códigos de tipos de tributos
    ),
    array(
        'item'                          =>  '2',
        'codigo'                        =>  'CODPROD02',
        'descripcion'                   =>  'LIBRO XL',
        'cantidad'                      =>  1,
        'valor_unitario'                =>  50, //SIN IGV = 0
        'precio_unitario'               =>  50, //CON IGV = 0%
        'tipo_precio'                   =>  '01',
        'igv'                           =>  0,
        'porcentaje_igv'                =>  0,
        'valor_total'                   =>  50,
        'importe_total'                 =>  50,
        'unidad'                        =>  'NIU',
        'codigo_afectacion_alt'         =>  '20', //10:gravados, 20:exonerados, 30:inafectos Catálogo No. 07: Códigos de tipo de afectación del IGV
        'codigo_afectacion'             =>  '9997', //Catálogo No. 05: Códigos de tipos de tributos
        'nombre_afectacion'             =>  'EXO', //Catálogo No. 05: Códigos de tipos de tributos
        'tipo_afectacion'               =>  'VAT' //Catálogo No. 05: Códigos de tipos de tributos
    ),
    array(
        'item'                          =>  '3',
        'codigo'                        =>  'CODPROD03',
        'descripcion'                   =>  'SANDIA',
        'cantidad'                      =>  1,
        'valor_unitario'                =>  10, //SIN IGV = 0
        'precio_unitario'               =>  10, //CON IGV = 0%
        'tipo_precio'                   =>  '01',
        'igv'                           =>  0,
        'porcentaje_igv'                =>  0,
        'valor_total'                   =>  10,
        'importe_total'                 =>  10,
        'unidad'                        =>  'NIU',
        'codigo_afectacion_alt'         =>  '30', //10:gravados, 20:exonerados, 30:inafectos Catálogo No. 07: Códigos de tipo de afectación del IGV
        'codigo_afectacion'             =>  '9998', //Catálogo No. 05: Códigos de tipos de tributos
        'nombre_afectacion'             =>  'INA', //Catálogo No. 05: Códigos de tipos de tributos
        'tipo_afectacion'               =>  'FRE' //Catálogo No. 05: Códigos de tipos de tributos
    ),
);


//INICIALIZAR TOTALES
$op_gravadas = 0;
$op_exoneradas = 0;
$op_inafectas = 0;
$igv = 0;
$total = 0;

foreach ($detalle as $key => $value) {
    if ($value['codigo_afectacion_alt'] == 10) { //gravados
        $op_gravadas = $op_gravadas + $value['valor_total'];
    }
    if ($value['codigo_afectacion_alt'] == 20) { //exonerados
        $op_exoneradas = $op_exoneradas + $value['valor_total'];
    }
    if ($value['codigo_afectacion_alt'] == 30) { //inafectos
        $op_inafectas = $op_inafectas + $value['valor_total'];
    }

    $igv = $igv + $value['igv'];
    $total = $total + $value['importe_total'];
}

$comprobante['total_opgravadas'] = $op_gravadas;
$comprobante['total_opexoneradas'] = $op_exoneradas;
$comprobante['total_opinafectas'] = $op_inafectas;
$comprobante['igv'] = $igv;
$comprobante['total'] = $total;

require_once('cantidad_en_letras.php');
$comprobante['total_texto'] = CantidadEnLetra($total);

//PAS0 01 - CREAR XML DE NC
//SUNAT: RUC EMISOR - TIPO COMPROBANTE - SERIE - CORRELATIVO
//EJEMPLO: 20123456789-01-F001-29.XML

$nombreXML = $emisor['ruc'] . '-' . $comprobante['tipodoc'] . '-' . $comprobante['serie'] . '-' . $comprobante['correlativo'];
$ruta = 'xml/' . $nombreXML;

require_once('xml.php');
$xml = new GeneradorXML();
$xml->CrearXMLNotaCredito($ruta, $emisor, $cliente, $comprobante, $detalle);
echo 'PASO 01: XML DE NOTA DE CREDITO CREADO EXITOSAMENTE..';
//PAS0 01 - CREAR XML DE NOTA DE CREDITO - FIN

//LLAMADO AL APIFACTURACION
require_once('ApiFacturacion.php');
$objApi = new ApiFacturacion();
$objApi->EnviarComprobanteElectronico($emisor, $nombreXML);


















// //PASO 02 FIRMAR DIGITALMENTE EL XML - INICIO
// require_once("signature.php");
// $objFirma = new Signature();
// $flg_firma = 0; //ubicacion en el xml
// $ruta_certificado = "";
// $ruta_archivo_xml = "xml/";
// $ruta = $ruta_archivo_xml . $nombreXML . ".XML";
// $ruta_firma = $ruta_certificado . "certificado_prueba_sunat.pfx"; //@CAMBIAR_PRODUCCION
// $pass_firma = "ceti";//@CAMBIAR_PRODUCCION

// $objFirma->signature_xml($flg_firma, $ruta, $ruta_firma, $pass_firma);
// echo "</br> PASO 02: XML FIRMA DIGITALMENTE.";


// //PASO 02 FIRMAR DIGITALMENTE EL XML - FIN


// //PASO 03 CONVERTIR XML FIRMADO A .ZIP - INICIO
// $zip = new ZipArchive();
// $nombreZip = $nombreXML . '.ZIP';
// $ruta_zip = $ruta_archivo_xml . $nombreXML . '.ZIP';

// if ($zip->open($ruta_zip, ZipArchive::CREATE) == TRUE) {
//     $zip->addFile($ruta, $nombreXML . '.XML');
//     $zip->close();
// }
// echo "</br> PASO 03: XML FIRMADO DIGITALMENTE HA SIDO COMPRIMIDO EN FORMATO .ZIP";

// //PASO 03 CONVERTIR XML FIRMADO A .ZIP - FIN


// //PASO 04: CODIFICAR EL BASE 64 EL ARCHIVO XML .ZIP - INICIO
// $ruta_archivo = $ruta_zip;
// $nombre_archivo = $nombreZip;
// $contenido_del_zip = base64_encode(file_get_contents($ruta_archivo));

// echo "</br> PASO 04: XML FIRMADO EN ZIP CODIFICADO EN BASE 64: " . $contenido_del_zip;
// //PASO 04: CODIFICAR EL BASE 64 EL ARCHIVO XML .ZIP - FIN


// //PASO 05 - CONSUMO WEB SERVICE DE SUNAT - METODO SENDBILL - INICIO

// $ws = "https://e-beta.sunat.gob.pe/ol-ti-itcpfegem-beta/billService"; //RUTA BETA DE WS DE SUNAT
// //$ws = "https://e-factura.sunat.gob.pe/ol-ti-itcpfegem/billService"; //@CAMBIAR_PRODUCCION

// $xml_envio ='<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://service.sunat.gob.pe" xmlns:wsse="http://docs.oasisopen.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
//             <soapenv:Header>
//                 <wsse:Security>
//                     <wsse:UsernameToken>
//                         <wsse:Username>' . $emisor['ruc'] . $emisor['usuario_secundario'] . '</wsse:Username>
//                         <wsse:Password>' . $emisor['clave_usuario_secundario'] . '</wsse:Password>
//                     </wsse:UsernameToken>
//                 </wsse:Security>
//             </soapenv:Header>
//             <soapenv:Body>
//                 <ser:sendBill>
//                     <fileName>' . $nombre_archivo . '</fileName>
//                     <contentFile>' . $contenido_del_zip . '</contentFile>
//                 </ser:sendBill>
//             </soapenv:Body>
//         </soapenv:Envelope>';

// //CURL
// $header = array(
//     "Content-type: text/xml; charset=\"utf-8\"",
//     "Accept: text/xml",
//     "Cache-Control: no-cache",
//     "Pragma: no-cache",
//     "SOAPAction: ",
//     "Content-lenght: " . strlen($xml_envio)
// );

// $ch = curl_init(); //Creo el objeto e inicio la llamada
// curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, 1); //
// curl_setopt($ch,CURLOPT_URL, $ws); // url de ws de sunat
// curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
// curl_setopt($ch,CURLOPT_HTTPAUTH, CURLAUTH_ANY);
// curl_setopt($ch,CURLOPT_TIMEOUT, 30);
// curl_setopt($ch,CURLOPT_POST, true);
// curl_setopt($ch,CURLOPT_POSTFIELDS, $xml_envio);
// curl_setopt($ch,CURLOPT_HTTPHEADER, $header);
// curl_setopt($ch,CURLOPT_CAINFO, dirname(__FILE__)."/cacert.pem"); //comentar Cuando estemos en PRODUCCCION

// $respuesta = curl_exec($ch); // ejecutar el WS y obtener la RPTA de SUNAT
// $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE); //obtjengo el codigo de rpta

// echo "</br> PASO 05: CONSUMO DE WEB SERVICE SENDBILL DE SUNAT";
// //PASO 05 - CONSUMO WEB SERVICE DE SUNAT - METODO SENDBILL - FIN


// //PASO 6 AL 9 - INICIO
// $estado_fe = 0; //0: xml aun no se envia SUNAT, 1: Exito CDR, 2: Error SUNAT, 3: Problema de Comunicacion/Conexión
// $ruta_archivo_cdr = "cdr/";

// if ($httpcode == 200) { //OK HUBO COMUNICACION
//     $doc = new DOMDocument();
//     $doc->loadXML($respuesta);

//     if (isset($doc->getElementsByTagName('applicationResponse')->item(0)->nodeValue)) {
//         $cdr = $doc->getElementsByTagName('applicationResponse')->item(0)->nodeValue;
//         echo "</br> PASO 06: SUNAT RESPONDIO CON EL XML AR";

//         $cdr = base64_decode($cdr);
//         echo "</br> PASO 07: RPTA DE SUNAT DECODIFICADA"; //obtenemos el .ZIP

//         file_put_contents($ruta_archivo_cdr . 'R-' . $nombreZip, $cdr); //Compiamos el CDR de memoria a disco el ZIP

//         $zip = new ZipArchive();
//         if ($zip->open($ruta_archivo_cdr . 'R-' . $nombreZip) == TRUE) {
//             $zip->extractTo($ruta_archivo_cdr);
//             $zip->close();
//             echo "</br> PASO 08: XML/CDR COPIADO A DISCO Y DESCOMPRIMIDO";

//             $estado_fe = 1;//EXITO
//             echo "</br> PASO 09: PROCESO TERMINADO";
//         }
//     }else{
//         $estado_fe = 2;
//         $codigo = $doc->getElementsByTagName('faultcode')->item(0)->nodeValue;
//         $mensaje = $doc->getElementsByTagName('faultstring')->item(0)->nodeValue;

//         echo "</br> ERROR EN FE CON CODIGO: " . $codigo . " MENSAJE: " . $mensaje;
//     }
// }
// else{
//     $estado_fe = 3;
//     echo curl_error($ch);
//     echo "Problemas de conexión";
// }

// curl_close($ch);
// //PASO 6 AL 9 - FIN
?>