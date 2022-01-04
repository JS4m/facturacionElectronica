<?php

try {
    $manejador  =   'mysql';
    $servidor   =   'localhost';
    $base       =   'facturacion25';
    $usuario    =   'root';
    $pass       =   '';

    $cadena = "$manejador:host=$servidor;dbname=$base";

    $cnx = new PDO($cadena, $usuario, $pass, array(
        PDO::ATTR_PERSISTENT => TRUE,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"
    ));


    //CRUD DE CLIENTES

    //insert
    // $sql = "INSERT INTO Cliente(tipodoc, nrodoc, razon_social, direccion) VALUES
    //         (:tipodoc, :nrodoc, :razon_social, :direccion)";
    // $parametros = array(
    //     ':tipodoc'          =>  '1', 
    //     ':nrodoc'           =>  '43535037', 
    //     ':razon_social'     =>  'Fernando Quiroz', 
    //     ':direccion'        =>   'Lima - Surco'
    // );
    // $pre = $cnx->prepare($sql);
    // $pre->execute($parametros);

    // echo 'Cliente registrado';

    //update
    // $sql = "UPDATE Cliente SET tipodoc = :tipodoc, nrodoc = :nrodoc, razon_social = :razon_social, direccion = :direccion WHERE id = :id";
    // $parametros = array(
    //     ':tipodoc'          =>  '6', 
    //     ':nrodoc'           =>  '10435350378', 
    //     ':razon_social'     =>  'Fernando Quiroz C.', 
    //     ':direccion'        =>   'Lima - Surco B',
    //     ':id'               =>  1
    // );
    // $pre = $cnx->prepare($sql);
    // $pre->execute($parametros);

    // echo 'Cliente actualizado';

    //select
    // $sql = "SELECT * FROM Cliente";
    // $res = $cnx->query($sql);
    // $res = $res->fetchAll(PDO::FETCH_NAMED);

    // foreach ($res as $key => $value) {
    //     echo '</br>' . $value['razon_social'] . ' RUC: ' . $value['nrodoc'];
    // }

    //delete
    // $sql = "DELETE FROM Cliente WHERE id = :id";
    // $parametros = array(
    //     ':id'   =>  1
    // );
    // $pre = $cnx->prepare($sql);
    // $pre->execute($parametros);
    // echo 'Cliente eliminado';


} catch (\Throwable $th) {
    throw $th;
}







?>