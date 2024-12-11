<?php

namespace App\Controller;
use App\Class\Cliente;
use App\Class\Usuario;
use App\Class\Telefono;
use App\Excepcions\DeleteClientException;
use App\Excepcions\ReadClientException;
use App\Controller\InterfaceController;
use mysql_xdevapi\Exception;
use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Validator;
use Ramsey\Uuid\Uuid;
use App\Class\ClienteModificable;
use App\Model\ClienteModel;
include_once "InterfaceController.php";

class ClienteController implements InterfaceController
{
    //GET /clients
    public function index($api){
        include_once DIRECTORIO_VISTAS."/Clients/indexClient.php";
    }

    //GET /clients/create
    public function create($api){
        //Aquí mostraríamos el formulario de registro
        $uuid = Uuid::uuid4();
        include_once DIRECTORIO_VISTAS."/Clients/createClient.php";

        echo "Formulario de registro de un cliente";

    }

    //POST /clients
    public function store($api)
    {
        $errores = [];

        // Generate a random UUID for the user
        $uuid = Uuid::uuid4()->toString();

        // Convert clientisopen to boolean
        if (isset($_POST['clientisopen'])) {
            $_POST['clientisopen'] = $_POST['clientisopen'] === '1';
        }

        // Add the generated UUID to the request data
        $_POST['useruuid'] = $uuid;

        try {
            // Validate the input data
            Validator::key('clientname', Validator::stringType()->notEmpty()->length(1, 100))
                ->key('clientaddress', Validator::optional(Validator::stringType()->length(1, 255)))
                ->key('clientisopen', Validator::boolType())
                ->key('clientcost', Validator::number()->positive())
                ->key('useruuid', Validator::uuid()) // Validate the generated UUID
                ->assert($_POST);
        } catch (NestedValidationException $exception) {
            $errores = $exception->getMessages();
        }

        // Check for validation errors
        if (!empty($errores)) {
            include_once DIRECTORIO_VISTAS . "/Clients/errorClient.php";
            return;
        }

        // Create and save client
        $cliente = ClienteModificable::crearClienteAPartirDeUnArray($_POST);
        ClienteModel::guardarCliente($cliente);

        // Respond based on API or web request
        if ($api) {
            http_response_code(201);
            header('Content-Type: application/json');
            echo json_encode($cliente);
        } else {
            $informacion = ['Se ha creado el usuario correctamente'];
            $_SESSION['clientname'] = $cliente->getNombre();
            $_SESSION['clientuuid'] = $cliente->getUuid();
            include_once DIRECTORIO_VISTAS . "informacion.php";
        }
    }


    //GET /clients/{id_usuario}/edit
    public function edit($id,$api){
        //Comprobar que el cliente exista y cargar los datos
        $cliente=ClienteModel::leerCliente($id);
        if (!$cliente){
            $errores[]="Usuario no encontrado";
            include_once DIRECTORIO_VISTAS."errores.php";
            exit();
        }else{
            include_once DIRECTORIO_VISTAS."Clients/editClient.php"; //Revisar
        }
    }


    //PUT /clients/{id_usuario}
    public function update($id,$api){
        // Obtén el cliente actual desde el modelo
        $cliente = ClienteModel::leerCliente($id);

        // Leer los datos enviados a través de PUT
        parse_str(file_get_contents("php://input"), $datos_put_para_editar);

        // Filtramos y validamos los datos recibidos
        try {
            Validator::key('clientname', Validator::optional(Validator::stringType()->notEmpty()->length(3, 100)), false)
                ->key('clientaddress', Validator::optional(Validator::stringType()->length(1, 255)), false)
                ->key('clientisopen', Validator::optional(Validator::boolType()), false)
                ->key('clientcost', Validator::optional(Validator::numeric()->positive()), false)
                ->key('clientphones', Validator::optional(Validator::arrayType()), false)
                ->key('userdata', Validator::optional(Validator::json()), false)
                ->assert($datos_put_para_editar);
        } catch (NestedValidationException $exception) {
            $errores = $exception->getMessages();
        }

        // Manejar errores de validación
        if (isset($errores) && is_array($errores)) {
            if ($api) {
                http_response_code(400);
                header('Content-Type: application/json');
                echo json_encode(['errors' => $errores]);
                return;
            } else {
                include_once DIRECTORIO_VISTAS . '/Clients/errorClient.php';
                return;
            }
        }

        // Actualizar los datos del cliente
        $cliente->setNombre($datos_put_para_editar['clientname'] ?? $cliente->getNombre());
        $cliente->setDireccion($datos_put_para_editar['clientaddress'] ?? $cliente->getDireccion());
        $cliente->setAbierto($datos_put_para_editar['clientisopen'] ?? $cliente->isAbierto());
        $cliente->setCoste($datos_put_para_editar['clientcost'] ?? $cliente->getCoste());
        $cliente->setTelefonos($datos_put_para_editar['clientphones'] ?? $cliente->getTelefonos());

        // Guardar el cliente actualizado en la base de datos
        $cliente->save();

        // Responder a la solicitud
        if ($api) {
            http_response_code(200);
            header('Content-Type: application/json');
            echo json_encode($cliente);
        } else {
            $_SESSION['clientename'] = $cliente->getNombre();
            $_SESSION['clienteuuid'] = $cliente->getUuid();
            $informacion = ['Se ha actualizado el cliente correctamente'];
            include_once DIRECTORIO_VISTAS . "informacion.php";
        }

    }


    //GET /clients/{id_usuario}
    public function show($id, $api) {
        try {
            // Obtén los datos del cliente desde el modelo
            $cliente = ClienteModel::leerCliente($id);

            // Carga la vista con los datos del cliente
            include_once DIRECTORIO_VISTAS . "/Clients/showClient.php";
        } catch (ReadClientException $e) {
            // Manejo de errores en caso de que no se encuentre el cliente
            echo "<h2>Error: No se pudo cargar el cliente.</h2>";
        }
    }



    //DELETE /clients/{id_usuario}
    public function destroy($id,$api){
        //Borrar los datos de un usuario
            ClienteModel::borrarCliente($id);
            //Si api==false
            if ($api){
                http_response_code(200);
                header('Content-Type: application/json');
                echo json_encode([
                    "mensaje"=>"El usuario ha sido borrado correctamente"
                ]);
            }
            return true;
    }

}