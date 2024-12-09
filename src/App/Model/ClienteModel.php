<?php

namespace App\Model;

use App\Class\Cliente;
use App\Class\Telefono;
use App\Excepcions\EditClientException;
use App\Excepcions\ReadClientException;
use PDO;
use PDOException;
use App\Excepcions\DeleteClientException;

class ClienteModel
{
    private static function conectarBD():?PDO{
        try{
            $conexion = new PDO("mysql:host=".NOMBRE_CONTAINER_DATABASE.";dbname=".NOMBRE_DATABASE
                ,USUARIO_DATABASE,
                PASS_DATABASE);

            $conexion->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
            return $conexion;
        }catch(PDOException $e){
            echo "Fallo de conexión".$e->getMessage();
        }
        return null;
    }

    public static function guardarCliente(Cliente $cliente){

        //Crear una conexión con la base de datos
        $conexion = ClienteModel::conectarBD();

        //Creación de la consulta SQL de los clientes.
        $sql = "INSERT INTO client(clientuuid,
                 useruuid,
                 clientname,
                 clientaddress,
                 clientisopen,
                 clientcost) values(
                                    :useruuid,
                                    :clientname,
                                    :clientaddress,
                                    :clientisopen,
                                    :clientcost)";



        $sentenciaPreparada= $conexion->prepare($sql);



        //Enlazado de parámetros dentro de la consulta
        //$sentenciaPreparada->bindValue("clientuuid", $cliente->getUuid());
        $sentenciaPreparada->bindValue("useruuid", $cliente->getUsuario()->getUuid());
        $sentenciaPreparada->bindValue("clientname", $cliente->getNombre());
        $sentenciaPreparada->bindValue("clientaddress", $cliente->getDireccion());
        $sentenciaPreparada->bindValue("clientisopen", $cliente->isAbierto());
        $sentenciaPreparada->bindValue("clientcost", $cliente->getCoste());


        //Ejecución de la consulta contra la base de datos
        //Necesitamos guardar el usuario antes de guardar el telefono para que la FK funcione
        $sentenciaPreparada->execute();
        /* Creación de la consulta Telefono */
        $sqltelefono= "INSERT INTO phone(phoneprefix,phonenumber,useruuid)
                        VALUES (:prefijo,:numero,:uuid_usuario)";
        $sentenciaPreparadaTelefono = $conexion->prepare($sqltelefono);

        //Realizamos un bucle para guardar todos los telefonos asociados
        foreach ($cliente->getTelefonos() as $telefono){
            $sentenciaPreparadaTelefono->bindValue("prefijo",$telefono->getPrefijo());
            $sentenciaPreparadaTelefono->bindValue("numero",$telefono->getNumero());
            $sentenciaPreparadaTelefono->bindValue("uuid_usuario",$cliente->getUuid());
            $sentenciaPreparadaTelefono->execute();
        }
    }

    public static function borrarCliente(string $uuidCliente):bool{

        //Crear una conexión con la base de datos
        $conexion = ClienteModel::conectarBD();

        $sql = "DELETE FROM client WHERE useruuid=?";

        $sentenciaPreparada = $conexion->prepare($sql);

        $sentenciaPreparada->bindValue(1,$uuidCliente);

        $sentenciaPreparada->execute();

        //Gestionar los errores de ejecución
        if($sentenciaPreparada->rowCount()==0){
            throw new DeleteClientException();
        }else{
            return true;
        }
    }

    public static function editarCliente(Cliente $cliente):?Cliente{

        //Crear una conexión con la base de datos
        $conexion = ClienteModel::conectarBD();
        $sql = "UPDATE client SET 
                 clientuuid=:clientuuid,
                 useruuid=:useruuid,
                 clientname=:clientname,
                 clientaddress=:clientaddress,
                 clientisopen=:clientisopen,
                 clientcost=:clientcost";

        $sentenciaPreparada=$conexion->prepare($sql);

        $sentenciaPreparada->bindValue("clientuuid",$cliente->getUuid());
        $sentenciaPreparada->bindValue("useruuid",$cliente->getUsuario()->getUuid());
        $sentenciaPreparada->bindValue("clientname",$cliente->getNombre());
        $sentenciaPreparada->bindValue("clientaddress",$cliente->getDireccion());
        $sentenciaPreparada->bindValue("clientisopen",$cliente->isAbierto());
        $sentenciaPreparada->bindValue("clientcost",$cliente->getCoste());

        $sentenciaPreparada->execute();

        if ($sentenciaPreparada->rowCount()==0){
            throw new EditClientException();
        }else{
            return $cliente;
        }
    }

    public static function leerUsuario($uuidCliente):?Cliente{

        //Crear una conexión con la base de datos
        $conexion = ClienteModel::conectarBD();

        $sql = "SELECT 
                    clientuuid,
                    clientname,
                    clientaddress,
                    clientisopen,
                    clientcost,
                    useruuid FROM client where clientuuid=:clientuuid";

        //Preparar la sentencia a ejecutar
        $sentenciaPreparada=$conexion->prepare($sql);

        //Hacer la asignación de los parametros de la SQL al valor
        $sentenciaPreparada->bindValue('uuid',$uuidCliente);

        //Ejecutar la consulta con los parametros ya cambiados en la base de datos
        $sentenciaPreparada->execute();

        if($sentenciaPreparada->rowCount()===0){
            //Se ha producido un error
            throw new ReadClientException();
        }else{
            //Leer de la base datos un usuario
            $datosCliente = $sentenciaPreparada->fetch(PDO::FETCH_ASSOC);

            //Creamos la consulta necesaria para conseguir los telefonos de la tabla phone
            $sqlTelefonos = "SELECT phoneprefix,phonenumber FROM phone WHERE useruuid=?"; // <<-- NO ESTOY SEGURO
            $sentenciaTelefonos = $conexion->prepare($sqlTelefonos);
            $sentenciaTelefonos->execute([$uuidCliente]);
            $telefonos=
                Telefono::crearTelefonosDesdeUnArray(
                    $sentenciaTelefonos->fetchAll(PDO::FETCH_ASSOC));


            $cliente=Cliente::crearClienteAPartirDeUnArray($datosCliente);
            $cliente->setTelefonos($telefonos);
            return $cliente;

        }
    }

    public static function comprobarCliente(string $clientname):false|Cliente{

        //Crear una conexión con la base de datos
        $conexion = ClienteModel::conectarBD();

        $sql = "SELECT clientuuid from client where clientname=?";

        $sentenciaPreparada= $conexion->prepare($sql);

        //Forma abreviada de ejecutar una consulta
        $sentenciaPreparada->execute([$clientname]);

        if ($sentenciaPreparada->rowCount()==0){
            return false;
        }else{
            $datosCliente = $sentenciaPreparada->fetch(PDO::FETCH_ASSOC);
            return ClienteModel::leerCliente($datosCliente['clientuuid']);
        }
        return false;
    }
}