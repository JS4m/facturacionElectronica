<?php

require_once('xml.php');


class ApiFacturacion
{

    //Enviar a SUNAT: FACTURAS, BOLETAS, NOTAS DE CREDITO Y NOTAS DE DEBITOS
    public function EnviarComprobanteElectronico($emisor, $nombreXML, $ruta_certificado = "", $ruta_archivo_xml = "xml/", $ruta_archivo_cdr = "cdr/")
    {
        //PASO 02 FIRMAR DIGITALMENTE EL XML - INICIO
        require_once("signature.php");
        $objFirma = new Signature();
        $flg_firma = 0; //ubicacion en el xml
        $ruta = $ruta_archivo_xml . $nombreXML . ".XML";
        $ruta_firma = $ruta_certificado . "certificado_prueba_sunat.pfx"; //@CAMBIAR_PRODUCCION
        $pass_firma = "ceti";//@CAMBIAR_PRODUCCION

        $objFirma->signature_xml($flg_firma, $ruta, $ruta_firma, $pass_firma);
        //echo "</br> PASO 02: XML FIRMA DIGITALMENTE.";
        //PASO 02 FIRMAR DIGITALMENTE EL XML - FIN


        //PASO 03 CONVERTIR XML FIRMADO A .ZIP - INICIO
        $zip = new ZipArchive();
        $nombreZip = $nombreXML . '.ZIP';
        $ruta_zip = $ruta_archivo_xml . $nombreXML . '.ZIP';

        if ($zip->open($ruta_zip, ZipArchive::CREATE) == TRUE) {
            $zip->addFile($ruta, $nombreXML . '.XML');
            $zip->close();
        }
        //echo "</br> PASO 03: XML FIRMADO DIGITALMENTE HA SIDO COMPRIMIDO EN FORMATO .ZIP";
        //PASO 03 CONVERTIR XML FIRMADO A .ZIP - FIN


        //PASO 04: CODIFICAR EL BASE 64 EL ARCHIVO XML .ZIP - INICIO
        $ruta_archivo = $ruta_zip;
        $nombre_archivo = $nombreZip;
        $contenido_del_zip = base64_encode(file_get_contents($ruta_archivo));

        //echo "</br> PASO 04: XML FIRMADO EN ZIP CODIFICADO EN BASE 64: " . $contenido_del_zip;
        //PASO 04: CODIFICAR EL BASE 64 EL ARCHIVO XML .ZIP - FIN


        //PASO 05 - CONSUMO WEB SERVICE DE SUNAT - METODO SENDBILL - INICIO
        $ws = "https://e-beta.sunat.gob.pe/ol-ti-itcpfegem-beta/billService"; //RUTA BETA DE WS DE SUNAT
        //$ws = "https://e-factura.sunat.gob.pe/ol-ti-itcpfegem/billService"; //@CAMBIAR_PRODUCCION

        $xml_envio ='<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://service.sunat.gob.pe" xmlns:wsse="http://docs.oasisopen.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
                    <soapenv:Header>
                        <wsse:Security>
                            <wsse:UsernameToken>
                                <wsse:Username>' . $emisor['ruc'] . $emisor['usuario_secundario'] . '</wsse:Username>
                                <wsse:Password>' . $emisor['clave_usuario_secundario'] . '</wsse:Password>
                            </wsse:UsernameToken>
                        </wsse:Security>
                    </soapenv:Header>
                    <soapenv:Body>
                        <ser:sendBill>
                            <fileName>' . $nombre_archivo . '</fileName>
                            <contentFile>' . $contenido_del_zip . '</contentFile>
                        </ser:sendBill>
                    </soapenv:Body>
                </soapenv:Envelope>';

        //CURL
        $header = array(
            "Content-type: text/xml; charset=\"utf-8\"",
            "Accept: text/xml",
            "Cache-Control: no-cache",
            "Pragma: no-cache",
            "SOAPAction: ",
            "Content-lenght: " . strlen($xml_envio)
        );

        $ch = curl_init(); //Creo el objeto e inicio la llamada
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, 1); //
        curl_setopt($ch,CURLOPT_URL, $ws); // url de ws de sunat
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch,CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch,CURLOPT_TIMEOUT, 30);
        curl_setopt($ch,CURLOPT_POST, true);
        curl_setopt($ch,CURLOPT_POSTFIELDS, $xml_envio);
        curl_setopt($ch,CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch,CURLOPT_CAINFO, dirname(__FILE__)."/cacert.pem"); //comentar Cuando estemos en PRODUCCCION

        $respuesta = curl_exec($ch); // ejecutar el WS y obtener la RPTA de SUNAT
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE); //obtjengo el codigo de rpta

        //echo "</br> PASO 05: CONSUMO DE WEB SERVICE SENDBILL DE SUNAT";
        //PASO 05 - CONSUMO WEB SERVICE DE SUNAT - METODO SENDBILL - FIN


        //PASO 6 AL 9 - INICIO
        $estado_fe = 0; //0: xml aun no se envia SUNAT, 1: Exito CDR, 2: Error SUNAT, 3: Problema de Comunicacion/Conexión

        if ($httpcode == 200) { //OK HUBO COMUNICACION
            $doc = new DOMDocument();
            $doc->loadXML($respuesta);

            if (isset($doc->getElementsByTagName('applicationResponse')->item(0)->nodeValue)) {
                $cdr = $doc->getElementsByTagName('applicationResponse')->item(0)->nodeValue;
                //echo "</br> PASO 06: SUNAT RESPONDIO CON EL XML AR";

                $cdr = base64_decode($cdr);
                //echo "</br> PASO 07: RPTA DE SUNAT DECODIFICADA"; //obtenemos el .ZIP

                file_put_contents($ruta_archivo_cdr . 'R-' . $nombreZip, $cdr); //Compiamos el CDR de memoria a disco el ZIP

                $zip = new ZipArchive();
                if ($zip->open($ruta_archivo_cdr . 'R-' . $nombreZip) == TRUE) {
                    $zip->extractTo($ruta_archivo_cdr);
                    $zip->close();
                    //echo "</br> PASO 08: XML/CDR COPIADO A DISCO Y DESCOMPRIMIDO";

                    $estado_fe = 1;//EXITO
                    //echo "</br> PASO 09: PROCESO TERMINADO";
                }
            }else{
                $estado_fe = 2;
                $codigo = $doc->getElementsByTagName('faultcode')->item(0)->nodeValue;
                $mensaje = $doc->getElementsByTagName('faultstring')->item(0)->nodeValue;

                echo "</br> ERROR EN FE CON CODIGO: " . $codigo . " MENSAJE: " . $mensaje;
            }
        }
        else{
            $estado_fe = 3;
            echo curl_error($ch);
            echo "Problemas de conexión";
        }

        curl_close($ch);
        //PASO 6 AL 9 - FIN

        return $estado_fe;
    }

    //RA, RC
    public function EnviarResumenComprobantes($emisor, $nombreXML, $ruta_certificado = "", $ruta_archivo_xml = "xml/", $ruta_archivo_cdr = "cdr/")
    {
        //PASO 02 FIRMAR DIGITALMENTE EL XML - INICIO
        require_once("signature.php");
        $objFirma = new Signature();
        $flg_firma = 0; //ubicacion en el xml
        $ruta = $ruta_archivo_xml . $nombreXML . ".XML";
        $ruta_firma = $ruta_certificado . "certificado_prueba_sunat.pfx"; //@CAMBIAR_PRODUCCION
        $pass_firma = "ceti";//@CAMBIAR_PRODUCCION

        $objFirma->signature_xml($flg_firma, $ruta, $ruta_firma, $pass_firma);
        //echo "</br> PASO 02: XML FIRMA DIGITALMENTE.";
        //PASO 02 FIRMAR DIGITALMENTE EL XML - FIN


        //PASO 03 CONVERTIR XML FIRMADO A .ZIP - INICIO
        $zip = new ZipArchive();
        $nombreZip = $nombreXML . '.ZIP';
        $ruta_zip = $ruta_archivo_xml . $nombreXML . '.ZIP';

        if ($zip->open($ruta_zip, ZipArchive::CREATE) == TRUE) {
            $zip->addFile($ruta, $nombreXML . '.XML');
            $zip->close();
        }
        //echo "</br> PASO 03: XML FIRMADO DIGITALMENTE HA SIDO COMPRIMIDO EN FORMATO .ZIP";
        //PASO 03 CONVERTIR XML FIRMADO A .ZIP - FIN


        //PASO 04: CODIFICAR EL BASE 64 EL ARCHIVO XML .ZIP - INICIO
        $ruta_archivo = $ruta_zip;
        $nombre_archivo = $nombreZip;
        $contenido_del_zip = base64_encode(file_get_contents($ruta_archivo));

        //echo "</br> PASO 04: XML FIRMADO EN ZIP CODIFICADO EN BASE 64: " . $contenido_del_zip;
        //PASO 04: CODIFICAR EL BASE 64 EL ARCHIVO XML .ZIP - FIN


        //PASO 05 - CONSUMO WEB SERVICE DE SUNAT - METODO SENDBILL - INICIO
        $ws = "https://e-beta.sunat.gob.pe/ol-ti-itcpfegem-beta/billService"; //RUTA BETA DE WS DE SUNAT
        //$ws = "https://e-factura.sunat.gob.pe/ol-ti-itcpfegem/billService"; //@CAMBIAR_PRODUCCION

        $xml_envio ='<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://service.sunat.gob.pe" xmlns:wsse="http://docs.oasisopen.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
            <soapenv:Header>
                <wsse:Security>
                    <wsse:UsernameToken>
                        <wsse:Username>' . $emisor['ruc'] . $emisor['usuario_secundario'] . '</wsse:Username>
                        <wsse:Password>' . $emisor['clave_usuario_secundario'] . '</wsse:Password>
                    </wsse:UsernameToken>
                </wsse:Security>
            </soapenv:Header>
            <soapenv:Body>
                <ser:sendSummary>
                    <fileName>' . $nombre_archivo . '</fileName>
                    <contentFile>' . $contenido_del_zip . '</contentFile>
                </ser:sendSummary>
            </soapenv:Body>
        </soapenv:Envelope>';

        $header = array(
            "Content-type: text/xml; charset=\"utf-8\"",
            "Accept: text/xml",
            "Cache-Control: no-cache",
            "Pragma: no-cache",
            "SOAPAction: ",
            "Content-lenght: " . strlen($xml_envio)
        );

        $ch = curl_init(); //inicia la llamada
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, 1); //
        curl_setopt($ch,CURLOPT_URL, $ws);//url a consultar
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch,CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch,CURLOPT_TIMEOUT, 30);
        curl_setopt($ch,CURLOPT_POST, true);
        curl_setopt($ch,CURLOPT_POSTFIELDS, $xml_envio);
        curl_setopt($ch,CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__)."/cacert.pem");//windows, cuando estemos productivos comenta esta linea

        $respuesta = curl_exec($ch); // ejecutar el WS y obtener la RPTA de SUNAT
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE); //obtjengo el codigo de rpta

        //echo "</br> PASO 05: CONSUMO DE WEB SERVICE SENDSUMMARY DE SUNAT";
        //PASO 05 - CONSUMO WEB SERVICE DE SUNAT - METODO SENDBILL - FIN


        //PASO 6 AL 9 - INICIO
        $estado_fe = 0; //0: xml aun no se envia SUNAT, 1: Exito CDR, 2: Error SUNAT, 3: Problema de Comunicacion/Conexión
        $ticket = 0;

        if ($httpcode == 200) {
            $doc = new DOMDocument();
            $doc->loadXML($respuesta);

            if (isset($doc->getElementsByTagName('ticket')->item(0)->nodeValue)) {
                $ticket = $doc->getElementsByTagName('ticket')->item(0)->nodeValue;
                $estado_fe = 1;
                echo '</br> PASO 06: EL NRO DE TICKET ES: ' . $ticket;
            }else{
                $estado_fe = 2;
                $codigo = $doc->getElementsByTagName('faultcode')->item(0)->nodeValue;
                $mensaje = $doc->getElementsByTagName('faultstring')->item(0)->nodeValue;

                echo "</br> ERROR EN FE CON CODIGO: " . $codigo . " MENSAJE: " . $mensaje;
            }
        }
        else{
            $estado_fe = 3;
            echo curl_error($ch);
            echo "Problemas de conexión";
        }
        curl_close($ch);
        return $ticket;

    }

    public function ConsultarTicket($emisor, $cabecera, $ticket, $ruta_archivo_cdr = "cdr/")
    {
        $ws = "https://e-beta.sunat.gob.pe/ol-ti-itcpfegem-beta/billService"; //WS DE SUNAT BETA
        //$ws = https://e-factura.sunat.gob.pe/ol-ti-itcpfegem/billService //ws DE SUNAT PRODUCCION
        $xml_envio ='<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://service.sunat.gob.pe" xmlns:wsse="http://docs.oasisopen.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
            <soapenv:Header>
            <wsse:Security>
                <wsse:UsernameToken>
                    <wsse:Username>' . $emisor['ruc'] . $emisor['usuario_secundario'] . '</wsse:Username>
                    <wsse:Password>' . $emisor['clave_usuario_secundario'] . '</wsse:Password>
                </wsse:UsernameToken>
            </wsse:Security>
            </soapenv:Header>
            <soapenv:Body>
            <ser:getStatus>
                <ticket>' . $ticket . '</ticket>
            </ser:getStatus>
            </soapenv:Body>
        </soapenv:Envelope>';

        $header = array(
            "Content-type: text/xml; charset=\"utf-8\"",
            "Accept: text/xml",
            "Cache-Control: no-cache",
            "Pragma: no-cache",
            "SOAPAction: ",
            "Content-lenght: " . strlen($xml_envio)
        );

        $ch = curl_init(); //inicia la llamada
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, 1); //
        curl_setopt($ch,CURLOPT_URL, $ws);//url a consultar
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch,CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch,CURLOPT_TIMEOUT, 30);
        curl_setopt($ch,CURLOPT_POST, true);
        curl_setopt($ch,CURLOPT_POSTFIELDS, $xml_envio);
        curl_setopt($ch,CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__)."/cacert.pem");//windows, cuando estemos productivos comenta esta linea

        $response = curl_exec($ch); //Ejecuto y obtengo resultado
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        //echo '</br> PASO 07: CONSULTAR EL NRO DE TICKET';

        $nombre = $emisor['ruc'] . '-' . $cabecera['tipodoc'] . '-' . $cabecera['serie'] . '-' . $cabecera['correlativo'];
        $nombreZip = $nombre . '.ZIP';
        $estado_fe = 0;
        if ($httpcode == 200) {
            $doc = new DOMDocument();
            $doc->loadXML($response);

            if (isset($doc->getElementsByTagName('content')->item(0)->nodeValue)) {
                $cdr = $doc->getElementsByTagName('content')->item(0)->nodeValue;
                $cdr = base64_decode($cdr);

                file_put_contents($ruta_archivo_cdr . 'R-' . $nombreZip, $cdr);

                $zip = new ZipArchive();
                if ($zip->open($ruta_archivo_cdr . 'R-' . $nombreZip)===TRUE) {
                    $zip->extractTo($ruta_archivo_cdr, 'R-' . $nombre . '.XML');
                    //echo '</br> PASO 08: EXTRAEMOS EL ZIP';
                    $zip->close();
                }
                $estado_fe = 1;
                //echo '</br> PASO 09: PROCESO TERMINADO';
            }else{
                $estado_fe = 2;
                $codigo = $doc->getElementsByTagName('faultcode')->item(0)->nodeValue;
                $mensaje = $doc->getElementsByTagName('faultstring')->item(0)->nodeValue;

                echo "</br> ERROR EN FE CON CODIGO: " . $codigo . " MENSAJE: " . $mensaje;
            }
        }
        else{
            $estado_fe = 3;
            echo curl_error($ch);
            echo "Problemas de conexión";
        }
        curl_close($ch);

    }


    function consultarComprobante($emisor, $comprobante)
    {
		try{
				$ws = "https://e-beta.sunat.gob.pe/ol-ti-itcpfegem-beta/billService";
				$soapUser = "";  
				$soapPassword = "";

				$xml_post_string = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" 
				xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://service.sunat.gob.pe" 
				xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
					<soapenv:Header>
						<wsse:Security>
							<wsse:UsernameToken>
								<wsse:Username>'.$emisor['ruc'].$emisor['usuariosol'].'</wsse:Username>
								<wsse:Password>'.$emisor['clavesol'].'</wsse:Password>
							</wsse:UsernameToken>
						</wsse:Security>
					</soapenv:Header>
					<soapenv:Body>
						<ser:getStatus>
							<rucComprobante>'.$emisor['ruc'].'</rucComprobante>
							<tipoComprobante>'.$comprobante['tipodoc'].'</tipoComprobante>
							<serieComprobante>'.$comprobante['serie'].'</serieComprobante>
							<numeroComprobante>'.$comprobante['correlativo'].'</numeroComprobante>
						</ser:getStatus>
					</soapenv:Body>
				</soapenv:Envelope>';
			
				$headers = array(
					"Content-type: text/xml;charset=\"utf-8\"",
					"Accept: text/xml",
					"Cache-Control: no-cache",
					"Pragma: no-cache",
					"SOAPAction: ",
					"Content-length: " . strlen($xml_post_string),
				); 			
			
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
				curl_setopt($ch, CURLOPT_URL, $ws);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
				curl_setopt($ch, CURLOPT_TIMEOUT, 30);
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_post_string); // the SOAP request
				curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			
				//para ejecutar los procesos de forma local en windows
				//enlace de descarga del cacert.pem https://curl.haxx.se/docs/caextract.html
				curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__)."/cacert.pem");

				$response = curl_exec($ch);
				$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
				curl_close($ch);
				echo var_dump($response);
				
			} catch (Exception $e) {
				echo "SUNAT ESTA FUERA SERVICIO: ".$e->getMessage();
			}
    }

}




?>