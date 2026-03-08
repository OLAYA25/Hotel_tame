<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../backend/config/database.php';
include_once '../models/Producto.php';
include_once '../utils/FileUpload.php';

$database = new Database();
$db = $database->getConnection();
$producto = new Producto($db);

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        if(isset($_GET['id'])) {
            $producto->id = $_GET['id'];
            if($producto->getById()) {
                $producto_arr = array(
                    "id" => $producto->id,
                    "nombre" => $producto->nombre,
                    "descripcion" => $producto->descripcion,
                    "categoria" => $producto->categoria,
                    "precio" => $producto->precio,
                    "imagen_url" => $producto->imagen_url,
                    "stock" => $producto->stock,
                    "activo" => $producto->activo
                );
                http_response_code(200);
                echo json_encode($producto_arr);
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Producto no encontrado."));
            }
        } elseif(isset($_GET['categoria'])) {
            $stmt = $producto->getByCategory($_GET['categoria']);
            $num = $stmt->rowCount();
            
            if($num > 0) {
                $productos_arr = array();
                $productos_arr["records"] = array();
                
                while ($row = $stmt->fetch()) {
                    $producto_item = array(
                        "id" => $row['id'],
                        "nombre" => $row['nombre'],
                        "descripcion" => $row['descripcion'],
                        "categoria" => $row['categoria'],
                        "precio" => $row['precio'],
                        "imagen_url" => $row['imagen_url'],
                        "stock" => $row['stock'],
                        "activo" => $row['activo']
                    );
                    array_push($productos_arr["records"], $producto_item);
                }
                
                http_response_code(200);
                echo json_encode($productos_arr);
            } else {
                http_response_code(200);
                echo json_encode(array("records" => array()));
            }
        } elseif(isset($_GET['all'])) {
            // Incluir productos inactivos
            $stmt = $producto->getAllWithInactive();
            $num = $stmt->rowCount();
            
            if($num > 0) {
                $productos_arr = array();
                $productos_arr["records"] = array();
                
                while ($row = $stmt->fetch()) {
                    $producto_item = array(
                        "id" => $row['id'],
                        "nombre" => $row['nombre'],
                        "descripcion" => $row['descripcion'],
                        "categoria" => $row['categoria'],
                        "precio" => $row['precio'],
                        "imagen_url" => $row['imagen_url'],
                        "stock" => $row['stock'],
                        "activo" => $row['activo']
                    );
                    array_push($productos_arr["records"], $producto_item);
                }
                
                http_response_code(200);
                echo json_encode($productos_arr);
            } else {
                http_response_code(200);
                echo json_encode(array("records" => array()));
            }
        } else {
            // Solo productos activos
            $stmt = $producto->getAll();
            $num = $stmt->rowCount();
            
            if($num > 0) {
                $productos_arr = array();
                $productos_arr["records"] = array();
                
                while ($row = $stmt->fetch()) {
                    $producto_item = array(
                        "id" => $row['id'],
                        "nombre" => $row['nombre'],
                        "descripcion" => $row['descripcion'],
                        "categoria" => $row['categoria'],
                        "precio" => $row['precio'],
                        "imagen_url" => $row['imagen_url'],
                        "stock" => $row['stock'],
                        "activo" => $row['activo']
                    );
                    array_push($productos_arr["records"], $producto_item);
                }
                
                http_response_code(200);
                echo json_encode($productos_arr);
            } else {
                http_response_code(200);
                echo json_encode(array("records" => array()));
            }
        }
        break;
        
    case 'POST':
        // Debug: log de datos recibidos
        error_log("POST request received");
        error_log("FILES: " . print_r($_FILES, true));
        error_log("POST: " . print_r($_POST, true));
        
        // Manejar subida de archivos - usar la ruta correcta
        $upload = new FileUpload('../../assets/images/products');
        $imagen_url = '';
        
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            error_log("Processing file upload");
            $uploadResult = $upload->uploadFile($_FILES['imagen'], 'producto');
            if ($uploadResult['success']) {
                $imagen_url = 'assets/images/products/' . $uploadResult['fileName'];
                error_log("File uploaded successfully: " . $imagen_url);
            } else {
                error_log("File upload failed: " . $uploadResult['message']);
                http_response_code(400);
                echo json_encode(array("message" => $uploadResult['message']));
                break;
            }
        }
        
        // Si hay datos JSON, procesar también
        $data = json_decode(file_get_contents("php://input"));
        error_log("JSON data: " . print_r($data, true));
        
        // Si no hay JSON, usar $_POST
        if (!$data && !empty($_POST)) {
            $data = (object)$_POST;
            error_log("Using POST data: " . print_r($data, true));
        }
        
        if(!empty($data->nombre) && !empty($data->categoria) && !empty($data->precio)) {
            $producto->nombre = $data->nombre;
            $producto->descripcion = $data->descripcion ?? "";
            $producto->categoria = $data->categoria;
            $producto->precio = $data->precio;
            $producto->imagen_url = $imagen_url ?: ($data->imagen_url ?? "");
            $producto->stock = $data->stock ?? 0;
            $producto->activo = isset($data->activo) ? $data->activo : true;
            
            error_log("Creating product with data: " . print_r([
                'nombre' => $producto->nombre,
                'categoria' => $producto->categoria,
                'precio' => $producto->precio,
                'imagen_url' => $producto->imagen_url
            ], true));
            
            if($producto->create()) {
                http_response_code(201);
                echo json_encode(array(
                    "message" => "Producto creado exitosamente.",
                    "imagen_url" => $imagen_url
                ));
            } else {
                error_log("Failed to create product");
                http_response_code(503);
                echo json_encode(array("message" => "No se pudo crear el producto."));
            }
        } else {
            error_log("Missing required fields");
            error_log("nombre: " . ($data->nombre ?? 'empty'));
            error_log("categoria: " . ($data->categoria ?? 'empty'));
            error_log("precio: " . ($data->precio ?? 'empty'));
            http_response_code(400);
            echo json_encode(array("message" => "Datos incompletos. Se requiere: nombre, categoria y precio"));
        }
        break;
        
    case 'PUT':
        // Debug: log de datos recibidos
        error_log("PUT request received");
        error_log("FILES: " . print_r($_FILES, true));
        error_log("POST: " . print_r($_POST, true));
        error_log("GET: " . print_r($_GET, true));
        
        // Manejar subida de archivos - usar la ruta correcta
        $upload = new FileUpload('../../assets/images/products');
        $imagen_url = '';
        
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = $upload->uploadFile($_FILES['imagen'], 'producto');
            if ($uploadResult['success']) {
                $imagen_url = 'assets/images/products/' . $uploadResult['fileName'];
            } else {
                http_response_code(400);
                echo json_encode(array("message" => $uploadResult['message']));
                break;
            }
        }
        
        // Para PUT con FormData, usar $_POST en lugar de JSON
        $data = (object)$_POST;
        error_log("Using POST data for PUT: " . print_r($data, true));
        
        if(!empty($data->id)) {
            $producto->id = $data->id;
            $producto->nombre = $data->nombre ?? "";
            $producto->descripcion = $data->descripcion ?? "";
            $producto->categoria = $data->categoria ?? "";
            $producto->precio = $data->precio ?? 0;
            $producto->imagen_url = $imagen_url ?: ($data->imagen_url ?? "");
            $producto->stock = $data->stock ?? 0;
            $producto->activo = isset($data->activo) ? $data->activo : true;
            
            error_log("Updating product with data: " . print_r([
                'id' => $producto->id,
                'nombre' => $producto->nombre,
                'categoria' => $producto->categoria,
                'precio' => $producto->precio,
                'imagen_url' => $producto->imagen_url
            ], true));
            
            if($producto->update()) {
                http_response_code(200);
                echo json_encode(array(
                    "message" => "Producto actualizado exitosamente.",
                    "imagen_url" => $imagen_url
                ));
            } else {
                error_log("Failed to update product");
                http_response_code(503);
                echo json_encode(array("message" => "No se pudo actualizar el producto."));
            }
        } else {
            error_log("Missing ID for PUT request");
            http_response_code(400);
            echo json_encode(array("message" => "Datos incompletos. Se requiere ID."));
        }
        break;
        
    case 'DELETE':
        $data = json_decode(file_get_contents("php://input"));
        
        if(!empty($data->id)) {
            $producto->id = $data->id;
            
            if($producto->delete()) {
                http_response_code(200);
                echo json_encode(array("message" => "Producto eliminado exitosamente."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "No se pudo eliminar el producto."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "ID no proporcionado."));
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(array("message" => "Método no permitido."));
        break;
}
?>
