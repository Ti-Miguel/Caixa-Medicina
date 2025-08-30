<?php
require __DIR__.'/db.php';

$nome  = 'José Miguel';
$email = 'jm1243miguel@gmail.com';
$senha = 'amor@100'; // você pode trocar

// gera hash seguro (bcrypt)
$hash  = password_hash($senha, PASSWORD_BCRYPT);

// cria tabela se não existir (seguro se você já criou)
$conn->query("
CREATE TABLE IF NOT EXISTS usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  senha_hash VARCHAR(255) NOT NULL,
  ativo TINYINT(1) NOT NULL DEFAULT 1,
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

// upsert por email (atualiza a senha e reativa se já existir)
$stmt = $conn->prepare("
INSERT INTO usuarios (nome,email,senha_hash,ativo)
VALUES (?,?,?,1)
ON DUPLICATE KEY UPDATE senha_hash=VALUES(senha_hash), ativo=1
");
$stmt->bind_param("sss",$nome,$email,$hash);
$stmt->execute();

echo "Usuário pronto: {$email} / {$senha}";
