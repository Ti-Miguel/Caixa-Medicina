<?php
// api.php
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit; }

date_default_timezone_set('America/Sao_Paulo');


require_once __DIR__ . "/db.php";

session_start(); // sessão para autenticação (auth.php define $_SESSION['user'])




$action = $_GET['action'] ?? $_POST['action'] ?? '';

function jdie($arr){ echo json_encode($arr, JSON_UNESCAPED_UNICODE); exit; }
function now()   { return date('Y-m-d H:i:s'); }
function today() { return date('Y-m-d'); }
function ok($data=[])        { jdie(["ok"=>true,"data"=>$data]); }
function bad($msg,$code=400) { http_response_code($code); jdie(["ok"=>false,"erro"=>$msg]); }

/**
 * Guard de autenticação — exige login feito via auth.php (auth.login).
 * Se quiser liberar alguma ação pública, adicione no array abaixo.
 */
$PUBLIC_ACTIONS = []; // ex.: ['healthcheck']
if (!isset($_SESSION['user']) && !in_array($action, $PUBLIC_ACTIONS, true)) {
  bad("Não autenticado", 401);
}

/* =========================
   USUÁRIOS
   (listar e add de compatibilidade)
   Para criar logins reais use auth.php (action=auth.createUser)
========================= */
if ($action === 'usuarios.list') {
  global $conn;
  $rs = $conn->query("SELECT id, nome, email, ativo FROM usuarios ORDER BY id ASC");
  $out = [];
  while($r=$rs->fetch_assoc()) $out[]=$r;
  ok($out);
}

if ($action === 'usuarios.add') {
  // Compatibilidade: se vier apenas "nome", cria um registro INATIVO (sem login).
  // Para criar usuário com login, envie também email+senha OU use auth.createUser em auth.php.
  global $conn;
  $nome  = trim($_POST['nome']  ?? '');
  $email = trim($_POST['email'] ?? '');
  $senha = (string)($_POST['senha'] ?? '');

  if ($nome==='') bad("Nome obrigatório");

  if ($email !== '' && $senha !== '') {
    $hash = password_hash($senha, PASSWORD_BCRYPT);
    $st = $conn->prepare("INSERT INTO usuarios (nome, email, senha_hash, ativo) VALUES (?, ?, ?, 1)");
    $st->bind_param("sss",$nome,$email,$hash);
    if(!$st->execute()) bad("Falha ao criar usuário (email pode estar em uso).");
    ok(["id"=>$conn->insert_id,"nome"=>$nome,"email"=>$email,"ativo"=>1]);
  } else {
    $placeholderEmail = "no-login-".time()."-".mt_rand(1000,9999)."@local";
    $hash = password_hash(bin2hex(random_bytes(8)), PASSWORD_BCRYPT);
    $st = $conn->prepare("INSERT INTO usuarios (nome, email, senha_hash, ativo) VALUES (?, ?, ?, 0)");
    $st->bind_param("sss",$nome,$placeholderEmail,$hash);
    $st->execute();
    ok(["id"=>$conn->insert_id,"nome"=>$nome,"email"=>$placeholderEmail,"ativo"=>0]);
  }
}

/* =========================
   PROFISSIONAIS
========================= */
if ($action === 'prof.list') {
  global $conn;
  $rs = $conn->query("SELECT id,nome FROM profissionais ORDER BY nome");
  $out=[]; while($r=$rs->fetch_assoc()) $out[]=$r;
  ok($out);
}
if ($action === 'prof.add') {
  global $conn;
  $nome = trim($_POST['nome'] ?? '');
  if ($nome==='') bad("Nome obrigatório");
  $st = $conn->prepare("INSERT INTO profissionais (nome) VALUES (?)");
  $st->bind_param("s",$nome); $st->execute();
  ok(["id"=>$conn->insert_id,"nome"=>$nome]);
}
if ($action === 'prof.update') {
  global $conn;
  $id = intval($_POST['id'] ?? 0);
  $nome = trim($_POST['nome'] ?? '');
  if (!$id || $nome==='') bad("Dados inválidos");
  $st = $conn->prepare("UPDATE profissionais SET nome=? WHERE id=?");
  $st->bind_param("si",$nome,$id); $st->execute();
  ok();
}
if ($action === 'prof.del') {
  global $conn;
  $id = intval($_POST['id'] ?? 0);
  if (!$id) bad("ID inválido");
  $st = $conn->prepare("DELETE FROM profissionais WHERE id=?");
  $st->bind_param("i",$id); $st->execute();
  ok();
}

/* =========================
   ESPECIALIDADES
========================= */
if ($action === 'esp.list') {
  global $conn;
  $rs = $conn->query("SELECT id,nome FROM especialidades ORDER BY nome");
  $out=[]; while($r=$rs->fetch_assoc()) $out[]=$r;
  ok($out);
}
if ($action === 'esp.add') {
  global $conn;
  $nome = trim($_POST['nome'] ?? '');
  if ($nome==='') bad("Nome obrigatório");
  $st = $conn->prepare("INSERT INTO especialidades (nome) VALUES (?)");
  $st->bind_param("s",$nome); $st->execute();
  ok(["id"=>$conn->insert_id,"nome"=>$nome]);
}
if ($action === 'esp.update') {
  global $conn;
  $id = intval($_POST['id'] ?? 0);
  $nome = trim($_POST['nome'] ?? '');
  if (!$id || $nome==='') bad("Dados inválidos");
  $st = $conn->prepare("UPDATE especialidades SET nome=? WHERE id=?");
  $st->bind_param("si",$nome,$id); $st->execute();
  ok();
}
if ($action === 'esp.del') {
  global $conn;
  $id = intval($_POST['id'] ?? 0);
  if (!$id) bad("ID inválido");
  $st = $conn->prepare("DELETE FROM especialidades WHERE id=?");
  $st->bind_param("i",$id); $st->execute();
  ok();
}

/* =========================
   PROCEDIMENTOS (Exames)
========================= */
if ($action === 'proc.list') {
  global $conn;
  $rs = $conn->query("SELECT id,nome,valor_cartao,valor_particular FROM procedimentos ORDER BY nome");
  $out=[]; while($r=$rs->fetch_assoc()) $out[]=$r;
  ok($out);
}
if ($action === 'proc.upsert') {
  global $conn;
  $nome = trim($_POST['nome'] ?? '');
  $vc   = floatval($_POST['valor_cartao'] ?? 0);
  $vp   = floatval($_POST['valor_particular'] ?? 0);
  if ($nome==='') bad("Nome obrigatório");
  $st = $conn->prepare("
    INSERT INTO procedimentos (nome,valor_cartao,valor_particular)
    VALUES (?,?,?)
    ON DUPLICATE KEY UPDATE valor_cartao=VALUES(valor_cartao), valor_particular=VALUES(valor_particular)
  ");
  $st->bind_param("sdd",$nome,$vc,$vp);
  $st->execute();
  ok();
}
if ($action === 'proc.del') {
  global $conn;
  $id = intval($_POST['id'] ?? 0);
  if (!$id) bad("ID inválido");
  $st = $conn->prepare("DELETE FROM procedimentos WHERE id=?");
  $st->bind_param("i",$id); $st->execute();
  ok();
}

/* =========================
   CAIXA (abrir, encerrar, consultar)
========================= */
if ($action === 'caixa.abrir') {
  global $conn;
  $usuario_id   = intval($_POST['usuario_id'] ?? 0);
  $data_caixa   = $_POST['data_caixa'] ?? '';
  $saldo_inicial= floatval($_POST['saldo_inicial'] ?? 0);
  $obs          = trim($_POST['obs'] ?? '');

  // Fallback para o usuário logado, se não vier do front
  if (!$usuario_id) {
    $usuario_id = intval($_SESSION['user']['id'] ?? 0);
  }

  if (!$usuario_id || !$data_caixa) {
    bad("Dados obrigatórios: usuario_id={$usuario_id}, data_caixa='{$data_caixa}'", 400);
  }

  $st = $conn->prepare("INSERT INTO caixas (usuario_id, data_caixa, saldo_inicial, obs, aberto_em)
                        VALUES (?, ?, ?, ?, ?)");
  $agora = now();
  $st->bind_param("isdss", $usuario_id, $data_caixa, $saldo_inicial, $obs, $agora);
  if (!$st->execute()) bad("Já existe caixa para este usuário nesta data ou erro ao abrir.");
  ok(["id"=>$conn->insert_id]);
}

if ($action === 'caixa.encerrar') {
  global $conn;
  $usuario_id = intval($_POST['usuario_id'] ?? 0);
  $data_caixa = $_POST['data_caixa'] ?? '';

  // Fallback para o usuário logado, se não vier do front
  if (!$usuario_id) {
    $usuario_id = intval($_SESSION['user']['id'] ?? 0);
  }

  if (!$usuario_id || !$data_caixa) bad("Dados obrigatórios", 400);

  $st = $conn->prepare("UPDATE caixas SET encerrado_em=? WHERE usuario_id=? AND data_caixa=? AND encerrado_em IS NULL");
  $agora = now();
  $st->bind_param("sis",$agora,$usuario_id,$data_caixa);
  $st->execute();
  ok(["linhas"=>$conn->affected_rows]);
}

if ($action === 'caixa.getByDia') {
  global $conn;
  $usuario_id = intval($_GET['usuario_id'] ?? 0);
  $data_caixa = $_GET['data_caixa'] ?? today();

  // Fallback para o usuário logado, se não vier do front
  if (!$usuario_id) {
    $usuario_id = intval($_SESSION['user']['id'] ?? 0);
  }

  $st = $conn->prepare("SELECT * FROM caixas WHERE usuario_id=? AND data_caixa=? LIMIT 1");
  $st->bind_param("is",$usuario_id,$data_caixa); $st->execute();
  $r = $st->get_result()->fetch_assoc();
  ok($r ?: null);
}

if ($action === 'caixa.list') {
  global $conn;
  $ini = $_GET['ini'] ?? '1900-01-01';
  $fim = $_GET['fim'] ?? '9999-12-31';
  $iniEsc = $conn->real_escape_string($ini);
  $fimEsc = $conn->real_escape_string($fim);
  $sql = "
    SELECT c.*, u.nome AS usuario_nome
    FROM caixas c
    JOIN usuarios u ON u.id=c.usuario_id
    WHERE c.data_caixa BETWEEN '{$iniEsc}' AND '{$fimEsc}'
    ORDER BY c.data_caixa DESC, c.aberto_em DESC
    LIMIT 200
  ";
  $rs = $conn->query($sql);
  $out=[]; while($r=$rs->fetch_assoc()) $out[]=$r;
  ok($out);
}

/* =========================
   RECEBIMENTOS
========================= */
/* =========================
   RECEBIMENTOS
========================= */
if ($action === 'rec.add') {
  global $conn;
  $B = $_POST;
  $caixa_id       = intval($B['caixa_id'] ?? 0);
  $paciente_nome  = trim($B['paciente_nome'] ?? '');
  $paciente_cpf   = preg_replace('/\D+/', '', $B['paciente_cpf'] ?? '');
  $valor          = floatval($B['valor'] ?? 0);
  $forma          = trim($B['forma_pagamento'] ?? '');
  $tabela         = trim($B['tabela'] ?? '');
  $baixa          = trim($B['baixa'] ?? '');
  $indicador      = trim($B['indicador'] ?? '');
  $prof_id        = isset($B['profissional_id'])  && $B['profissional_id']  !=='' ? intval($B['profissional_id'])  : null;
  $esp_id         = isset($B['especialidade_id']) && $B['especialidade_id'] !=='' ? intval($B['especialidade_id']) : null;
  $obs            = trim($B['observacao'] ?? '');
  $created        = now();

  if (!$caixa_id || !$paciente_nome || !$valor || !$forma || !$tabela || !$baixa || !$indicador) bad("Dados obrigatórios");

  $st = $conn->prepare("INSERT INTO recebimentos
    (caixa_id,paciente_nome,paciente_cpf,valor,forma_pagamento,tabela,baixa,indicador,profissional_id,especialidade_id,observacao,created_at)
    VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
  $st->bind_param("issdssssisss",
    $caixa_id,$paciente_nome,$paciente_cpf,$valor,$forma,$tabela,$baixa,$indicador,$prof_id,$esp_id,$obs,$created);
  $st->execute();
  $rec_id = $conn->insert_id;

  // vincula procedimentos (ids separados por vírgula)
  if (!empty($B['procedimento_id_list'])) {
    $ids = array_filter(array_map('intval', explode(',', $B['procedimento_id_list'])));
    if ($ids) {
      $st2 = $conn->prepare("INSERT IGNORE INTO recebimentos_procedimentos (recebimento_id,procedimento_id) VALUES (?,?)");
      foreach($ids as $pid){ $st2->bind_param("ii",$rec_id,$pid); $st2->execute(); }
    }
  }
  ok(["id"=>$rec_id]);
}

/* EDITAR recebimento (TOP-LEVEL) */
if ($action === 'rec.update') {
  global $conn;
  $B = $_POST;

  $id = intval($B['id'] ?? 0);
  if (!$id) bad("id obrigatório");
  $paciente_nome  = trim($B['paciente_nome'] ?? '');
  $paciente_cpf   = preg_replace('/\D+/', '', $B['paciente_cpf'] ?? '');
  $valor          = floatval($B['valor'] ?? 0);
  $forma          = trim($B['forma_pagamento'] ?? '');
  $tabela         = trim($B['tabela'] ?? '');
  $baixa          = trim($B['baixa'] ?? '');
  $indicador      = trim($B['indicador'] ?? '');
  $prof_id        = isset($B['profissional_id'])  && $B['profissional_id']  !== '' ? intval($B['profissional_id'])  : null;
  $esp_id         = isset($B['especialidade_id']) && $B['especialidade_id'] !== '' ? intval($B['especialidade_id']) : null;
  $obs            = trim($B['observacao'] ?? '');

  if (!$paciente_nome || !$valor || !$forma || !$tabela || !$baixa || !$indicador) bad("Dados obrigatórios");

  $sql = "
    UPDATE recebimentos SET
      paciente_nome=?,
      paciente_cpf=?,
      valor=?,
      forma_pagamento=?,
      tabela=?,
      baixa=?,
      indicador=?,
      observacao=?,
      ".($prof_id===null ? "profissional_id=NULL" : "profissional_id=$prof_id").",
      ".($esp_id===null  ? "especialidade_id=NULL" : "especialidade_id=$esp_id")."
    WHERE id=?
  ";
  $st = $conn->prepare($sql);
  $st->bind_param("ssdsssssi", $paciente_nome, $paciente_cpf, $valor, $forma, $tabela, $baixa, $indicador, $obs, $id);
  $st->execute();

  // zera e recria vínculos de exames
  $stDel = $conn->prepare("DELETE FROM recebimentos_procedimentos WHERE recebimento_id=?");
  $stDel->bind_param("i", $id); $stDel->execute();

  if (!empty($B['procedimento_id_list'])) {
    $ids = array_filter(array_map('intval', explode(',', $B['procedimento_id_list'])));
    if ($ids) {
      $st2 = $conn->prepare("INSERT IGNORE INTO recebimentos_procedimentos (recebimento_id,procedimento_id) VALUES (?,?)");
      foreach($ids as $pid){ $st2->bind_param("ii", $id, $pid); $st2->execute(); }
    }
  }

  ok(["id"=>$id]);
}

/* EXCLUIR recebimento (TOP-LEVEL) */
if ($action === 'rec.del') {
  global $conn;
  $id = intval($_POST['id'] ?? 0);
  if (!$id) bad("id obrigatório");

  $st1 = $conn->prepare("DELETE FROM recebimentos_procedimentos WHERE recebimento_id=?");
  $st1->bind_param("i",$id); $st1->execute();

  $st2 = $conn->prepare("DELETE FROM recebimentos WHERE id=?");
  $st2->bind_param("i",$id); $st2->execute();

  ok(["id"=>$id]);
}

/* LISTAR por caixa (continua TOP-LEVEL, separado) */
if ($action === 'rec.listByCaixa') {
  global $conn;
  $caixa_id = intval($_GET['caixa_id'] ?? 0);
  if (!$caixa_id) bad("caixa_id obrigatório");

  $rs = $conn->query("
    SELECT r.*, p.nome AS profissional_nome, e.nome AS especialidade_nome
    FROM recebimentos r
    LEFT JOIN profissionais p ON p.id=r.profissional_id
    LEFT JOIN especialidades e ON e.id=r.especialidade_id
    WHERE r.caixa_id={$caixa_id}
    ORDER BY r.created_at DESC
  ");
  $rows=[]; while($r=$rs->fetch_assoc()) $rows[]=$r;

  // anexar exames por recebimento (inclui valor_otica)
  if ($rows) {
    $ids = implode(',', array_map('intval', array_column($rows,'id')));
    $map = [];
    $q = $conn->query("
      SELECT rp.recebimento_id, pr.id, pr.nome, pr.valor_cartao, pr.valor_particular, pr.valor_otica
      FROM recebimentos_procedimentos rp
      JOIN procedimentos pr ON pr.id = rp.procedimento_id
      WHERE rp.recebimento_id IN ($ids)
    ");
    while($x=$q->fetch_assoc()){ $map[$x['recebimento_id']][] = $x; }
    foreach($rows as &$r){ $r['exames'] = $map[$r['id']] ?? []; }
  }
  ok($rows);
}


/* =========================
   SAÍDAS
========================= */
if ($action === 'saida.add') {
  global $conn;
  $B = $_POST;
  $caixa_id = intval($B['caixa_id'] ?? 0);
  $descricao= trim($B['descricao'] ?? '');
  $valor    = floatval($B['valor'] ?? 0);
  $origem   = trim($B['origem'] ?? '');
  $obs      = trim($B['observacao'] ?? '');
  $created  = now();

  if (!$caixa_id || !$descricao || !$valor || !$origem) bad("Dados obrigatórios");

  $st = $conn->prepare("INSERT INTO saidas (caixa_id,descricao,valor,origem,observacao,created_at) VALUES (?,?,?,?,?,?)");
  $st->bind_param("isdsss",$caixa_id,$descricao,$valor,$origem,$obs,$created);
  $st->execute();
  ok(["id"=>$conn->insert_id]);
}

if ($action === 'saida.listByCaixa') {
  global $conn;
  $caixa_id = intval($_GET['caixa_id'] ?? 0);
  if (!$caixa_id) bad("caixa_id obrigatório");
  $rs = $conn->query("SELECT * FROM saidas WHERE caixa_id={$caixa_id} ORDER BY created_at DESC");
  $out=[]; while($r=$rs->fetch_assoc()) $out[]=$r;
  ok($out);
}

/* =========================
   RELATÓRIOS (TOTAIS de TODO o filtro — sem paginação)
========================= */
if ($action === 'relatorio.totais') {
  global $conn;
  $inicio   = $_GET['inicio'] ?? '1900-01-01';
  $fim      = $_GET['fim'] ?? '9999-12-31';
  $usuario  = $_GET['usuario_id'] ?? '';
  $forma    = $_GET['forma'] ?? '';
  $tabela   = $_GET['tabela'] ?? '';
  $baixa    = $_GET['baixa'] ?? '';
  $indic    = $_GET['indicador'] ?? '';
  $prof     = $_GET['profissional_id'] ?? '';
  $esp      = $_GET['especialidade_id'] ?? '';
  $texto    = trim($_GET['texto'] ?? '');

  $w = [];
  $w[] = "r.created_at >= '".$conn->real_escape_string($inicio)." 00:00:00'";
  $w[] = "r.created_at <= '".$conn->real_escape_string($fim)." 23:59:59'";
  if ($usuario!=='') $w[] = "c.usuario_id = ".intval($usuario);
  if ($forma!=='')   $w[] = "r.forma_pagamento = '".$conn->real_escape_string($forma)."'";
  if ($tabela!=='')  $w[] = "r.tabela = '".$conn->real_escape_string($tabela)."'";
  if ($baixa!=='')   $w[] = "r.baixa = '".$conn->real_escape_string($baixa)."'";
  if ($indic!=='')   $w[] = "r.indicador = '".$conn->real_escape_string($indic)."'";
  if ($prof!=='')    $w[] = "r.profissional_id = ".intval($prof);
  if ($esp!=='')     $w[] = "r.especialidade_id = ".intval($esp);
  if ($texto!=='')   $w[] = "(LOWER(r.paciente_nome) LIKE LOWER('%".$conn->real_escape_string($texto)."%') OR LOWER(r.observacao) LIKE LOWER('%".$conn->real_escape_string($texto)."%'))";
  $where = implode(" AND ", $w);

  // Subquery: valor de exames por recebimento (respeitando a TABELA)
  $subExames = "
    SELECT r.id AS rid,
           SUM(
             CASE
               WHEN LOWER(r.tabela) = 'particular' THEN COALESCE(pr.valor_particular,0)
               WHEN LOWER(r.tabela) LIKE '%cart%' THEN COALESCE(pr.valor_cartao,0)
               WHEN LOWER(r.tabela) LIKE '%ótica%' OR LOWER(r.tabela) LIKE '%otica%' THEN COALESCE(pr.valor_otica,0)
               ELSE 0
             END
           ) AS valor_exames
    FROM recebimentos r
    JOIN caixas c ON c.id = r.caixa_id
    LEFT JOIN recebimentos_procedimentos rp ON rp.recebimento_id = r.id
    LEFT JOIN procedimentos pr ON pr.id = rp.procedimento_id
    WHERE $where
    GROUP BY r.id
  ";

  // TOTAL GERAL (valor base + exames)
  $sqlTotalGeral = "
    SELECT SUM(r.valor + COALESCE(x.valor_exames,0)) AS total
    FROM recebimentos r
    JOIN caixas c ON c.id = r.caixa_id
    LEFT JOIN ($subExames) x ON x.rid = r.id
    WHERE $where
  ";
  $totalGeral = 0.0;
  if ($rs = $conn->query($sqlTotalGeral)) {
    $totalGeral = (float)($rs->fetch_assoc()['total'] ?? 0);
  }

  // POR FORMA
  $sqlPorForma = "
    SELECT r.forma_pagamento AS k, SUM(r.valor + COALESCE(x.valor_exames,0)) AS s
    FROM recebimentos r
    JOIN caixas c ON c.id = r.caixa_id
    LEFT JOIN ($subExames) x ON x.rid = r.id
    WHERE $where
    GROUP BY k
    ORDER BY s DESC
  ";
  $porForma = [];
  if ($rs = $conn->query($sqlPorForma)) while($r=$rs->fetch_assoc()) $porForma[] = ["k"=>$r['k']?:'—',"v"=>(float)$r['s']];

  // POR INDICADOR
  $sqlPorIndic = "
    SELECT r.indicador AS k, SUM(r.valor + COALESCE(x.valor_exames,0)) AS s
    FROM recebimentos r
    JOIN caixas c ON c.id = r.caixa_id
    LEFT JOIN ($subExames) x ON x.rid = r.id
    WHERE $where
    GROUP BY k
    ORDER BY s DESC
  ";
  $porIndic = [];
  if ($rs = $conn->query($sqlPorIndic)) while($r=$rs->fetch_assoc()) $porIndic[] = ["k"=>$r['k']?:'—',"v"=>(float)$r['s']];

  // POR PROFISSIONAL
  $sqlPorProf = "
    SELECT COALESCE(p.nome,'—') AS k, SUM(r.valor + COALESCE(x.valor_exames,0)) AS s
    FROM recebimentos r
    JOIN caixas c ON c.id = r.caixa_id
    LEFT JOIN profissionais p ON p.id = r.profissional_id
    LEFT JOIN ($subExames) x ON x.rid = r.id
    WHERE $where
    GROUP BY k
    ORDER BY s DESC
  ";
  $porProf = [];
  if ($rs = $conn->query($sqlPorProf)) while($r=$rs->fetch_assoc()) $porProf[] = ["k"=>$r['k']?:'—',"v"=>(float)$r['s']];

  // EXAMES: total por exame + quebra por forma
  $sqlExamesForma = "
    SELECT pr.nome AS exame, r.forma_pagamento AS forma,
           SUM(
             CASE
               WHEN LOWER(r.tabela) = 'particular' THEN COALESCE(pr.valor_particular,0)
               WHEN LOWER(r.tabela) LIKE '%cart%' THEN COALESCE(pr.valor_cartao,0)
               WHEN LOWER(r.tabela) LIKE '%ótica%' OR LOWER(r.tabela) LIKE '%otica%' THEN COALESCE(pr.valor_otica,0)
               ELSE 0
             END
           ) AS total
    FROM recebimentos r
    JOIN caixas c ON c.id = r.caixa_id
    JOIN recebimentos_procedimentos rp ON rp.recebimento_id = r.id
    JOIN procedimentos pr ON pr.id = rp.procedimento_id
    WHERE $where
    GROUP BY pr.nome, r.forma_pagamento
    ORDER BY total DESC
  ";
  $examesAgg = []; // { exame: { total, formas: {forma: valor} } }
  if ($rs = $conn->query($sqlExamesForma)) {
    while($r=$rs->fetch_assoc()){
      $ex = $r['exame'] ?: '—';
      $fo = $r['forma'] ?: '—';
      $val= (float)$r['total'];
      if (!isset($examesAgg[$ex])) $examesAgg[$ex] = ["total"=>0.0, "formas"=>[]];
      $examesAgg[$ex]["total"] += $val;
      $examesAgg[$ex]["formas"][$fo] = ($examesAgg[$ex]["formas"][$fo] ?? 0) + $val;
    }
  }

  echo json_encode([
    "ok"=>true,
    "data"=>[
      "totalGeral"=>$totalGeral,
      "porForma"=>$porForma,
      "porIndicador"=>$porIndic,
      "porProfissional"=>$porProf,
      "exames"=>$examesAgg
    ]
  ], JSON_UNESCAPED_UNICODE);
  exit;
}

/* =========================
   RELATÓRIOS (filtros + paginação 50 por página)
========================= */
if ($action === 'relatorio.recebimentos') {
  global $conn;
  $inicio   = $_GET['inicio'] ?? '1900-01-01';
  $fim      = $_GET['fim'] ?? '9999-12-31';
  $usuario  = $_GET['usuario_id'] ?? '';
  $forma    = $_GET['forma'] ?? '';
  $tabela   = $_GET['tabela'] ?? '';
  $baixa    = $_GET['baixa'] ?? '';
  $indic    = $_GET['indicador'] ?? '';
  $prof     = $_GET['profissional_id'] ?? '';
  $esp      = $_GET['especialidade_id'] ?? '';
  $texto    = trim($_GET['texto'] ?? '');

  // paginação fixa
  $page = max(1, intval($_GET['page'] ?? 1));
  $per  = 50;
  $offset = ($page - 1) * $per;

  $w = [];
  $w[] = "r.created_at >= '".$conn->real_escape_string($inicio)." 00:00:00'";
  $w[] = "r.created_at <= '".$conn->real_escape_string($fim)." 23:59:59'";
  if ($usuario!=='') $w[] = "c.usuario_id = ".intval($usuario);
  if ($forma!=='')   $w[] = "r.forma_pagamento = '".$conn->real_escape_string($forma)."'";
  if ($tabela!=='')  $w[] = "r.tabela = '".$conn->real_escape_string($tabela)."'";
  if ($baixa!=='')   $w[] = "r.baixa = '".$conn->real_escape_string($baixa)."'";
  if ($indic!=='')   $w[] = "r.indicador = '".$conn->real_escape_string($indic)."'";
  if ($prof!=='')    $w[] = "r.profissional_id = ".intval($prof);
  if ($esp!=='')     $w[] = "r.especialidade_id = ".intval($esp);
  if ($texto!=='')   $w[] = "(LOWER(r.paciente_nome) LIKE LOWER('%".$conn->real_escape_string($texto)."%') OR LOWER(r.observacao) LIKE LOWER('%".$conn->real_escape_string($texto)."%'))";
  $where = implode(" AND ", $w);

  // total para paginação
  $sqlCount = "
    SELECT COUNT(*) AS total
    FROM recebimentos r
    JOIN caixas c ON c.id = r.caixa_id
    LEFT JOIN profissionais p ON p.id = r.profissional_id
    LEFT JOIN especialidades e ON e.id = r.especialidade_id
    WHERE $where
  ";
  $total = 0;
  if ($rsC = $conn->query($sqlCount)) {
    $total = (int)($rsC->fetch_assoc()['total'] ?? 0);
  }
  $total_pages = $per ? max(1, (int)ceil($total / $per)) : 1;
  if ($page > $total_pages) { $page = $total_pages; $offset = ($page - 1) * $per; }

  // página de dados
  $sql = "
    SELECT r.*, p.nome AS profissional_nome, e.nome AS especialidade_nome
    FROM recebimentos r
    JOIN caixas c ON c.id = r.caixa_id
    LEFT JOIN profissionais p ON p.id = r.profissional_id
    LEFT JOIN especialidades e ON e.id = r.especialidade_id
    WHERE $where
    ORDER BY r.created_at DESC
    LIMIT $per OFFSET $offset
  ";

  $rows=[]; $rs=$conn->query($sql);
  while($r=$rs->fetch_assoc()) $rows[]=$r;

  // anexar exames com valores (inclui valor_otica)
  if ($rows) {
    $ids = implode(',', array_map('intval', array_column($rows,'id')));
    if ($ids) {
      $map = [];
      $q = $conn->query("
        SELECT rp.recebimento_id, pr.id, pr.nome, pr.valor_cartao, pr.valor_particular, pr.valor_otica
        FROM recebimentos_procedimentos rp
        JOIN procedimentos pr ON pr.id = rp.procedimento_id
        WHERE rp.recebimento_id IN ($ids)
      ");
      while($x=$q->fetch_assoc()){ $map[$x['recebimento_id']][] = $x; }
      foreach($rows as &$r){ $r['exames'] = $map[$r['id']] ?? []; }
    }
  }

  echo json_encode([
    "ok" => true,
    "data" => $rows,
    "meta" => [
      "page" => $page,
      "per_page" => $per,
      "total" => $total,
      "total_pages" => $total_pages
    ]
  ], JSON_UNESCAPED_UNICODE);
  exit;
}


/* =========================
   DASHBOARD / FECHAMENTO
========================= */
if ($action === 'dashboard.kpis') {
  global $conn;

  $hoje        = date('Y-m-d');
  $inicio_mes  = date('Y-m-01');
  $fim_mes     = $hoje;

  // Subquery que calcula o total de EXAMES por recebimento conforme a TABELA
  $buildSubExames = function($where) use ($conn) {
    return "
      SELECT r.id AS rid,
             SUM(
               CASE
                 WHEN LOWER(r.tabela) = 'particular' THEN COALESCE(pr.valor_particular,0)
                 WHEN LOWER(r.tabela) LIKE '%cart%'   THEN COALESCE(pr.valor_cartao,0)
                 WHEN LOWER(r.tabela) LIKE '%ótica%' OR LOWER(r.tabela) LIKE '%otica%' THEN COALESCE(pr.valor_otica,0)
                 ELSE 0
               END
             ) AS valor_exames
      FROM recebimentos r
      LEFT JOIN recebimentos_procedimentos rp ON rp.recebimento_id = r.id
      LEFT JOIN procedimentos pr              ON pr.id = rp.procedimento_id
      WHERE $where
      GROUP BY r.id
    ";
  };

  // WHERE para hoje e para mês
  $whereDia = "r.created_at >= '".$conn->real_escape_string($hoje)." 00:00:00' AND r.created_at <= '".$conn->real_escape_string($hoje)." 23:59:59'";
  $whereMes = "r.created_at >= '".$conn->real_escape_string($inicio_mes)." 00:00:00' AND r.created_at <= '".$conn->real_escape_string($fim_mes)." 23:59:59'";

  // ---- TOT DIA (com exames)
  $subDia = $buildSubExames($whereDia);
  $sqlTotalDia = "
    SELECT SUM(r.valor + COALESCE(x.valor_exames,0)) AS s
    FROM recebimentos r
    LEFT JOIN ($subDia) x ON x.rid = r.id
    WHERE $whereDia
  ";
  $totalHoje = 0.0;
  if ($rs = $conn->query($sqlTotalDia)) $totalHoje = (float)($rs->fetch_assoc()['s'] ?? 0);

  // ---- DINHEIRO HOJE (com exames)
  $subDiaDin = $buildSubExames("$whereDia AND r.forma_pagamento = 'Dinheiro'");
  $sqlDinheiroHoje = "
    SELECT SUM(r.valor + COALESCE(x.valor_exames,0)) AS s
    FROM recebimentos r
    LEFT JOIN ($subDiaDin) x ON x.rid = r.id
    WHERE $whereDia AND r.forma_pagamento = 'Dinheiro'
  ";
  $dinheiroHoje = 0.0;
  if ($rs = $conn->query($sqlDinheiroHoje)) $dinheiroHoje = (float)($rs->fetch_assoc()['s'] ?? 0);

  // ---- SAÍDAS HOJE (todas)
  $sqlSaidasHoje = "
    SELECT SUM(s.valor) AS s
    FROM saidas s
    JOIN caixas c ON c.id = s.caixa_id
    WHERE c.data_caixa = '".$conn->real_escape_string($hoje)."'
  ";
  $saidasHoje = 0.0;
  if ($rs = $conn->query($sqlSaidasHoje)) $saidasHoje = (float)($rs->fetch_assoc()['s'] ?? 0);

  // ---- TOTAL MÊS (com exames; todos usuários)
  $subMes = $buildSubExames($whereMes);
  $sqlTotalMes = "
    SELECT SUM(r.valor + COALESCE(x.valor_exames,0)) AS s
    FROM recebimentos r
    LEFT JOIN ($subMes) x ON x.rid = r.id
    WHERE $whereMes
  ";
  $totalMes = 0.0;
  if ($rs = $conn->query($sqlTotalMes)) $totalMes = (float)($rs->fetch_assoc()['s'] ?? 0);

  ok([
    "totalHoje"    => $totalHoje,
    "dinheiroHoje" => $dinheiroHoje,
    "saidasHoje"   => $saidasHoje,
    "totalMes"     => $totalMes
  ]);
}

if ($action === 'fechamento.doDia') {
  global $conn;
  $usuario_id = intval($_GET['usuario_id'] ?? 0);
  $hoje = date('Y-m-d');

  // Caixa do usuário no dia
  $st = $conn->prepare("SELECT * FROM caixas WHERE usuario_id=? AND data_caixa=? LIMIT 1");
  $st->bind_param("is",$usuario_id,$hoje); $st->execute();
  $cx = $st->get_result()->fetch_assoc();
  if (!$cx) ok(null);

  $caixa_id = intval($cx['id']);

  // ---- Subquery de exames por recebimento (TODO o caixa)
  $subExamesAll = "
    SELECT r.id AS rid,
           SUM(
             CASE
               WHEN LOWER(r.tabela) = 'particular' THEN COALESCE(pr.valor_particular,0)
               WHEN LOWER(r.tabela) LIKE '%cart%' THEN COALESCE(pr.valor_cartao,0)
               WHEN LOWER(r.tabela) LIKE '%ótica%' OR LOWER(r.tabela) LIKE '%otica%' THEN COALESCE(pr.valor_otica,0)
               ELSE 0
             END
           ) AS valor_exames
    FROM recebimentos r
    LEFT JOIN recebimentos_procedimentos rp ON rp.recebimento_id = r.id
    LEFT JOIN procedimentos pr ON pr.id = rp.procedimento_id
    WHERE r.caixa_id = {$caixa_id}
    GROUP BY r.id
  ";

  // Recebimentos e totais POR FORMA (com exames)
  $sqlPorForma = "
    SELECT r.forma_pagamento AS forma,
           SUM(r.valor + COALESCE(x.valor_exames,0)) AS s
    FROM recebimentos r
    LEFT JOIN ($subExamesAll) x ON x.rid = r.id
    WHERE r.caixa_id = {$caixa_id}
    GROUP BY r.forma_pagamento
  ";
  $porForma = []; $total = 0.0;
  if ($rs = $conn->query($sqlPorForma)) {
    while($r=$rs->fetch_assoc()){
      $valor = (float)$r['s'];
      $porForma[] = ["forma"=>$r['forma'] ?: "—", "total" => $valor];
      $total += $valor;
    }
  }

  // ---- Somatório de DINHEIRO (com exames)
  $subExamesDin = "
    SELECT r.id AS rid,
           SUM(
             CASE
               WHEN LOWER(r.tabela) = 'particular' THEN COALESCE(pr.valor_particular,0)
               WHEN LOWER(r.tabela) LIKE '%cart%' THEN COALESCE(pr.valor_cartao,0)
               WHEN LOWER(r.tabela) LIKE '%ótica%' OR LOWER(r.tabela) LIKE '%otica%' THEN COALESCE(pr.valor_otica,0)
               ELSE 0
             END
           ) AS valor_exames
    FROM recebimentos r
    LEFT JOIN recebimentos_procedimentos rp ON rp.recebimento_id = r.id
    LEFT JOIN procedimentos pr ON pr.id = rp.procedimento_id
    WHERE r.caixa_id = {$caixa_id} AND r.forma_pagamento = 'Dinheiro'
    GROUP BY r.id
  ";
  $sqlRecDin = "
    SELECT SUM(r.valor + COALESCE(x.valor_exames,0)) AS s
    FROM recebimentos r
    LEFT JOIN ($subExamesDin) x ON x.rid = r.id
    WHERE r.caixa_id = {$caixa_id} AND r.forma_pagamento = 'Dinheiro'
  ";
  $recebDin = 0.0;
  if ($rs = $conn->query($sqlRecDin)) {
    $recebDin = (float)($rs->fetch_assoc()['s'] ?? 0);
  }

  // Saídas em dinheiro (inalterado)
  $sDin = $conn->query("SELECT SUM(valor) s FROM saidas WHERE caixa_id={$caixa_id} AND origem='Dinheiro'");
  $saidasDin = (float)($sDin->fetch_assoc()['s'] ?? 0);

  $saldoFinal = (float)$cx['saldo_inicial'] + $recebDin - $saidasDin;

  ok([
    "caixa"=>$cx,
    "totalRecebido"=>$total,           // total geral do dia (com exames)
    "porForma"=>$porForma,             // por forma (com exames)
    "dinheiro"=>[
      "saldo_inicial"=>(float)$cx['saldo_inicial'],
      "recebido"=>$recebDin,           // << agora inclui exames
      "saidas"=>$saidasDin,
      "saldo_final"=>$saldoFinal       // << saldo com exames
    ]
  ]);
}


bad("Ação não encontrada",404);
