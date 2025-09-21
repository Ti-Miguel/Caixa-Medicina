<?php


// auth.php
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit; }

date_default_timezone_set('America/Sao_Paulo');

session_start();
require_once __DIR__ . "/db.php";

$action = $_REQUEST['action'] ?? '';

function jdie($a){ echo json_encode($a, JSON_UNESCAPED_UNICODE); exit; }
function ok($d=[]){ jdie(["ok"=>true,"data"=>$d]); }
function bad($m,$c=400){ http_response_code($c); jdie(["ok"=>false,"erro"=>$m]); }

/**
 * Login correto com password_verify
 * POST: action=auth.login, email, senha
 */
if ($action === 'auth.login') {
  global $conn;
  $email = trim($_POST['email'] ?? '');
  $senha = (string)($_POST['senha'] ?? '');

  if ($email === '' || $senha === '') bad("Informe email e senha.");

  $st = $conn->prepare("SELECT id, nome, email, senha_hash, ativo FROM usuarios WHERE email=? LIMIT 1");
  $st->bind_param("s", $email);
  $st->execute();
  $res = $st->get_result();
  $u = $res->fetch_assoc();

  if (!$u) bad("Usuário não encontrado.", 401);
  if (intval($u['ativo']) !== 1) bad("Usuário inativo.", 403);

  if (!password_verify($senha, $u['senha_hash'])) {
    bad("Senha incorreta.", 401);
  }

  // Tudo certo: cria sessão
  $_SESSION['user'] = [
    "id"    => (int)$u['id'],
    "nome"  => $u['nome'],
    "email" => $u['email']
  ];
  ok($_SESSION['user']);
}

/**
 * Quem está logado
 */
if ($action === 'auth.me') {
  if (!isset($_SESSION['user'])) ok(null);
  ok($_SESSION['user']);
}

/**
 * Logout
 */
if ($action === 'auth.logout') {
  session_unset();
  session_destroy();
  ok();
}

/**
 * (Opcional) Criar usuário seed com hash bcrypt
 * POST: action=auth.createUser, nome, email, senha
 */
if ($action === 'auth.createUser') {
  global $conn;
  $nome  = trim($_POST['nome'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $senha = (string)($_POST['senha'] ?? '');

  if ($nome==='' || $email==='' || $senha==='') bad("Dados obrigatórios.");

  $hash = password_hash($senha, PASSWORD_BCRYPT);

  $st = $conn->prepare("INSERT INTO usuarios (nome, email, senha_hash, ativo) VALUES (?, ?, ?, 1)");
  $st->bind_param("sss", $nome, $email, $hash);
  if (!$st->execute()) bad("Falha ao criar usuário (email pode estar em uso).");
  ok(["id"=>$conn->insert_id, "email"=>$email]);
}

bad("Ação não encontrada",404);
