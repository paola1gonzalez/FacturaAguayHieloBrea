 <?php
// Recibir los datos JSON desde JavaScript
$data = json_decode(file_get_contents('php://input'), true);

// Verificar que se recibieron datos
if (!$data) {
  echo json_encode(["status" => "error", "message" => "No se recibieron datos"]);
  exit;
}

// Conectar a la base de datos
$host = "127.0.0.1";
$usuario = "root";
$clave = ""; // Si tienes contraseña, escríbela aquí
$bd = "facturacion_brea"; // Nombre actualizado de la base de datos

$conn = new mysqli($host, $usuario, $clave, $bd);

// Verificar conexión
if ($conn->connect_error) {
  echo json_encode(["status" => "error", "message" => "Conexión fallida: " . $conn->connect_error]);
  exit;
}

// Insertar en tabla facturas
$stmt = $conn->prepare("INSERT INTO facturas (cliente, telefono, tipo_pago, total) VALUES (?, ?, ?, ?)");
$stmt->bind_param("sssd", $data['cliente'], $data['telefono'], $data['tipoPago'], $data['total']);
$stmt->execute();

$factura_id = $stmt->insert_id;
$stmt->close();

// Insertar productos relacionados
foreach ($data['productos'] as $producto) {
  $stmt = $conn->prepare("INSERT INTO productos_factura (factura_id, nombre_producto, cantidad, total_producto) VALUES (?, ?, ?, ?)");
  $stmt->bind_param("isid", $factura_id, $producto['nombre'], $producto['cantidad'], $producto['totalProducto']);
  $stmt->execute();
  $stmt->close();
}

$conn->close();

// Respuesta a JavaScript
echo json_encode(["status" => "success"]);
?>