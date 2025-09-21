<?php
// db.php
$host   = "localhost";
$usuario= "u380360322_caixamed";
$senha  = "Miguel847829";
$banco  = "u380360322_caixamed";

$conn = new mysqli($host, $usuario, $senha, $banco);
if ($conn->connect_error) {
  http_response_code(500);
  die(json_encode(["ok"=>false,"erro"=>"Erro na conexão: ".$conn->connect_error]));
}
$conn->set_charset("utf8mb4");
$conn->query("SET time_zone = '-03:00'");

?>
