/* ===========================
   Helpers & Estado
=========================== */
const el  = (sel) => document.querySelector(sel);
const els = (sel) => Array.from(document.querySelectorAll(sel));

const fmt         = (v) => (Number(v) || 0).toLocaleString("pt-BR", { style: "currency", currency: "BRL" });
const yyyyMMdd    = (d) => d.toISOString().slice(0, 10);
const parseNumber = (v) => (isNaN(parseFloat(v)) ? 0 : parseFloat(v));

// id simples, se precisar
const genId = (prefix = "id") => `${prefix}_${Math.random().toString(36).slice(2, 9)}_${Date.now().toString(36)}`;

let state = {
  usuarioAtivo: null, // id numérico do usuário
  caixaAtivo: null    // id numérico do caixa do dia p/ usuário
};

// cache leve
window._cache = {
  usuarios: [],
  profissionais: [],
  especialidades: [],
  procedimentos: [] // [{id,nome,valor_cartao,valor_particular,valor_otica}]
};

/* ===========================
   Datas & Horário (ajuste de fuso)
=========================== */
// Se o banco devolve "YYYY-MM-DD HH:MM:SS" SEM timezone, indique aqui se é UTC
const DB_TIME_IS_UTC = true; // mude para false se o backend já grava hora local

function parseServerTS(ts) {
  if (!ts) return null;
  const s = String(ts).trim();

  // Já tem timezone? (ex.: 2025-08-31T14:00:00-03:00 ou ...Z)
  if (/[T ]\d{2}:\d{2}:\d{2}(?:\.\d+)?(?:Z|[+-]\d{2}:\d{2})$/.test(s)) {
    return new Date(s.replace(" ", "T"));
  }

  // Formato comum: "YYYY-MM-DD HH:MM:SS" (sem TZ)
  const m = s.match(/^(\d{4})-(\d{2})-(\d{2})[ T](\d{2}):(\d{2})(?::(\d{2}))?/);
  if (!m) return new Date(s); // fallback

  const [, y, mo, da, h, mi, se = "0"] = m;
  if (DB_TIME_IS_UTC) {
    // Trata como UTC e deixa o JS converter para local
    return new Date(Date.UTC(+y, +mo - 1, +da, +h, +mi, +se));
    } else {
    // Trata como hora local já correta
    return new Date(+y, +mo - 1, +da, +h, +mi, +se);
  }
}
const fmtTS = (ts) => {
  const d = parseServerTS(ts);
  return d && !isNaN(d) ? d.toLocaleString("pt-BR") : "—";
};

/* ===========================
   Cache e Normalização de Exames
=========================== */
async function ensureProcedimentosCache() {
  if (!window._cache.procedimentos || !window._cache.procedimentos.length) {
    const r = await API.proc.list();
    window._cache.procedimentos = r.data || [];
  }
}
function buildProcMaps() {
  const arr = window._cache.procedimentos || [];
  return {
    byId:   Object.fromEntries(arr.map(p => [String(p.id), p])),
    byName: Object.fromEntries(arr.map(p => [p.nome, p])),
  };
}
/** Normaliza exames de um registro vindo do relatório, independente do formato */
function normalizeExamesRecord(r, maps) {
  // 1) Se já veio array de exames (ideal)
  if (Array.isArray(r.exames) && r.exames.length) {
    return r.exames.map(e => {
      const ref = maps.byName[e.nome] || maps.byId[String(e.id || "")] || {};
      return {
        id: e.id ?? ref.id ?? null,
        nome: e.nome ?? ref.nome ?? "",
        valor_cartao: Number(e.valor_cartao ?? ref.valor_cartao ?? 0),
        valor_particular: Number(e.valor_particular ?? ref.valor_particular ?? 0),
        valor_otica: Number(e.valor_otica ?? ref.valor_otica ?? 0),
      };
    });
  }
  // 2) CSV de IDs
  const idsCSV = r.procedimento_id_list || r.exames_ids || r.procedimentos_ids;
  if (idsCSV) {
    return String(idsCSV)
      .split(",").map(s => s.trim()).filter(Boolean)
      .map(id => {
        const p = maps.byId[String(id)];
        return p ? {
          id: p.id, nome: p.nome,
          valor_cartao: Number(p.valor_cartao || 0),
          valor_particular: Number(p.valor_particular || 0),
          valor_otica: Number(p.valor_otica || 0),
        } : null;
      }).filter(Boolean);
  }
  // 3) String de nomes
  const nomesStr = r.exames_nomes || r.procedimentos || r.examesLista || r.exames_nomes_joined;
  if (nomesStr) {
    return String(nomesStr)
      .split(",").map(s => s.trim()).filter(Boolean)
      .map(nome => {
        const p = maps.byName[nome] || {};
        return {
          id: p.id ?? null, nome,
          valor_cartao: Number(p.valor_cartao || 0),
          valor_particular: Number(p.valor_particular || 0),
          valor_otica: Number(p.valor_otica || 0),
        };
      });
  }
  return [];
}

/* ===========================
   Preço por Tabela e Total com Exames
=========================== */
function priceForTabela(proc, tabelaLower) {
  if (tabelaLower === 'particular') return Number(proc.valor_particular || 0);
  if (tabelaLower.includes('cart')) return Number(proc.valor_cartao || 0);
  if (tabelaLower.includes('ótica') || tabelaLower.includes('otica')) return Number(proc.valor_otica || 0);
  return 0;
}
function totalAtendimentoComExames(rec) {
  const t = (rec.tabela || '').toLowerCase();
  const exames = rec._exames || rec.exames || [];
  const valorExames = exames.reduce((acc, info) => acc + priceForTabela(info, t), 0);
  return Number(rec.valor || 0) + valorExames;
}

/* ===========================
   Bootstrap Inicial (via API)
=========================== */
async function bootstrap() {
  // Usuários
  let ures = await API.usuarios.list();
  let usuarios = ures.data || [];
  if (!usuarios.length) {
    await API.usuarios.add("Usuário 1");
    usuarios = (await API.usuarios.list()).data || [];
  }

  // Profissionais, Especialidades, Procedimentos
  const [pres, eres, rprocs] = await Promise.all([
    API.prof.list(),
    API.esp.list(),
    API.proc.list()
  ]);

  window._cache.usuarios       = usuarios;
  window._cache.profissionais  = pres.data  || [];
  window._cache.especialidades = eres.data  || [];
  window._cache.procedimentos  = rprocs.data|| [];

  // Tentar usar o usuário logado como padrão
  try {
    const meResp = await fetch('auth.php?action=auth.me', { credentials: 'same-origin' });
    const me = await meResp.json();
    const meId = me?.data?.id;
    const meEmail = me?.data?.email;
    const match = usuarios.find(u => u.id == meId || (meEmail && u.email === meEmail));
    state.usuarioAtivo = match?.id || meId || usuarios[0]?.id || null;
  } catch {
    state.usuarioAtivo = usuarios[0]?.id || null;
  }
}

/* ===========================
   Navegação do App
=========================== */
function setupNav() {
  const buttons = document.querySelectorAll('.sidebar button');
  if (!buttons.length) return;

  buttons.forEach(btn => {
    btn.addEventListener('click', async () => {
      const target   = btn.dataset.screen;
      const targetEl = document.getElementById(target);
      if (!targetEl) {
        console.warn(`[NAV] Section "#${target}" não encontrada.`);
        return;
      }

      document.querySelectorAll('.sidebar button').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      document.querySelectorAll('.screen').forEach(s => s.classList.remove('visible'));
      targetEl.classList.add('visible');

      // hidrata a tela alvo
      switch (target) {
        case 'abrirCaixa':     await renderListaCaixas(); break;
        case 'recebimentos':   await renderRecebimentosDoDia(); break;
        case 'saidas':         await renderSaidasDoDia(); break;
        case 'relatorios':     hydrateFiltros(); await aplicarFiltros(); break;
        case 'procedimentos':  await renderProcedimentos(); break;
        case 'profissionais':  await renderProfissionais(); await renderEspecialidades(); break;
        case 'dashboard':      await refreshKPIs(); await renderChartsDashboard(); break;
        case 'fechamento':     await renderFechamento(); break;
      }
    });
  });

  const already = document.querySelector('.screen.visible');
  if (!already) {
    const first = document.getElementById('abrirCaixa') || document.querySelector('.screen');
    if (first) first.classList.add('visible');
  }
}

/* ===========================
   Sessão / Usuário / Caixa
=========================== */
// Mostra o usuário logado no header
async function hydrateUserHeader() {
  try {
    const r = await fetch("auth.php?action=auth.me", { credentials: "same-origin" });
    const j = await r.json();
    const nomeOuEmail = j?.data?.nome || j?.data?.email || "—";
    const span = el("#userName");
    if (span) span.textContent = nomeOuEmail;
  } catch {}
}

// Resiliente: pode não existir <select id="usuarioAtivo">
async function hydrateUsuarios() {
  if (!window._cache.usuarios?.length) {
    const r = await API.usuarios.list();
    window._cache.usuarios = r.data || [];
  }
  const usuarios = window._cache.usuarios;
  const sel = el("#usuarioAtivo"); // pode não existir

  if (!usuarios.find((u) => u.id == state.usuarioAtivo)) {
    state.usuarioAtivo = usuarios[0]?.id || state.usuarioAtivo || null;
  }

  if (sel) {
    sel.innerHTML = usuarios.map((u) => `<option value="${u.id}">${u.nome}</option>`).join("");
    sel.value = state.usuarioAtivo || "";
    sel.onchange = async () => {
      state.usuarioAtivo = Number(sel.value);
      await updateCaixaStatusUI();
      await renderRecebimentosDoDia();
      await renderSaidasDoDia();
      await refreshKPIs();
      await renderFechamento();
    };
  } else {
    await updateCaixaStatusUI();
    await renderRecebimentosDoDia();
    await renderSaidasDoDia();
    await refreshKPIs();
    await renderFechamento();
  }
}

async function addUsuario() {
  const nome = prompt("Nome do novo usuário:");
  if (!nome) return;
  const r = await API.usuarios.add(nome);
  if (!r.ok) { alert(r.erro || "Erro ao adicionar usuário"); return; }
  const res = await API.usuarios.list();
  window._cache.usuarios = res.data || [];
  await hydrateUsuarios();
  alert("Usuário adicionado.");
}

async function caixaDoDiaAPI(userId, dateStr) {
  const resp = await API.caixa.getByDia(userId, dateStr);
  return resp.data || null;
}

async function abrirCaixa(dateStr, saldoInicial, obs) {
  if (!state.usuarioAtivo) {
    try {
      const me = await fetch('auth.php?action=auth.me', { credentials: 'same-origin' }).then(r=>r.json());
      state.usuarioAtivo = me?.data?.id || state.usuarioAtivo;
    } catch {}
  }
  if (!state.usuarioAtivo) { alert("Selecione um usuário válido."); return; }

  const resp = await API.caixa.abrir(Number(state.usuarioAtivo), dateStr, saldoInicial, obs);
  if (!resp.ok) { alert(resp.erro || "Já existe caixa para este usuário nesta data ou erro ao abrir."); return; }
  await updateCaixaStatusUI();
  await renderListaCaixas();
  alert("Caixa aberto!");
}

async function encerrarCaixaAtual() {
  if (!state.usuarioAtivo) { alert("Selecione o usuário."); return; }
  const hoje = yyyyMMdd(new Date());
  const cx = await caixaDoDiaAPI(state.usuarioAtivo, hoje);
  if (!cx) { alert("Não há caixa aberto hoje para este usuário."); return; }
  if (cx.encerrado_em) { alert("Este caixa já está encerrado."); return; }
  const r = await API.caixa.encerrar(Number(state.usuarioAtivo), hoje);
  if (!r.ok) { alert(r.erro || "Erro ao encerrar"); return; }
  await updateCaixaStatusUI();
  alert("Caixa encerrado!");
}

async function updateCaixaStatusUI() {
  const badge = el("#statusBadge");
  const info  = el("#statusInfo");

  const hoje = yyyyMMdd(new Date());
  const cx = await caixaDoDiaAPI(state.usuarioAtivo, hoje);

  if (!cx) {
    if (badge) {
      badge.textContent = "Caixa não aberto";
      badge.style.color = "#d83c38";
      badge.style.background = "#fff";
    }
    if (info) info.textContent = "";
    state.caixaAtivo = null;
    return;
  }

  const usuarios = window._cache.usuarios;
  const uNome = usuarios.find((x) => x.id == cx.usuario_id)?.nome || "—";

  if (badge) {
    if (cx.encerrado_em) {
      badge.textContent = "Caixa ENCERRADO";
      badge.style.color = "#fff";
      badge.style.background = "#d83c38";
    } else {
      badge.textContent = "Caixa ABERTO";
      badge.style.color = "#fff";
      badge.style.background = "#16a34a";
    }
  }

  if (info) info.textContent = `${uNome} — ${cx.data_caixa} — Saldo inicial: ${fmt(cx.saldo_inicial)}`;
  state.caixaAtivo = cx.id;
}

/* ===========================
   Abertura de Caixa (Tela)
=========================== */
function setupAbertura() {
  const form = el("#formAbertura");
  el("#dataCaixa").value = yyyyMMdd(new Date());

  form.addEventListener("submit", async (e) => {
    e.preventDefault();
    const u = state.usuarioAtivo;
    const d = el("#dataCaixa").value;
    if (!u || !d) { alert("Selecione um usuário válido e verifique a data."); return; }
    await abrirCaixa(
      d,
      parseNumber(el("#saldoInicial").value),
      el("#obsAbertura").value.trim()
    );
  });

  el("#btnFecharCaixaDia").addEventListener("click", encerrarCaixaAtual);
}

/* ===========================
   Filtros e Listagem de Caixas
=========================== */
async function renderListaCaixas() {
  const tb = el('#tabelaCaixas tbody');

  const ini = el('#filtroCxInicio')?.value || '1900-01-01';
  const fim = el('#filtroCxFim')?.value || '9999-12-31';

  const r = await API.caixa.list(ini, fim);
  const lista = r.data || [];

  const rows = lista
    .map(c => {
      const abertura   = c.aberto_em    ? fmtTS(c.aberto_em)    : '—';
      const fechamento = c.encerrado_em ? fmtTS(c.encerrado_em) : '—';
      return `<tr>
        <td>${c.data_caixa}</td>
        <td>${c.usuario_nome}</td>
        <td>${fmt(c.saldo_inicial)}</td>
        <td>${abertura}</td>
        <td>${fechamento}</td>
        <td>${c.obs || ''}</td>
      </tr>`;
    })
    .join('');

  tb.innerHTML = rows || `<tr><td colspan="6">Nenhum caixa no período selecionado.</td></tr>`;
}

function setupFiltroCaixas() {
  const hoje = new Date();
  const inicioMes = new Date(hoje.getFullYear(), hoje.getMonth(), 1).toISOString().slice(0,10);
  const fimHoje = yyyyMMdd(hoje);

  const ini = el('#filtroCxInicio');
  const fim = el('#filtroCxFim');
  if (ini) ini.value = inicioMes;
  if (fim) fim.value = fimHoje;

  const btnApl = el('#btnAplicarFiltroCaixas');
  const btnLim = el('#btnLimparFiltroCaixas');
  if (btnApl) btnApl.addEventListener('click', renderListaCaixas);
  if (btnLim) btnLim.addEventListener('click', async ()=>{
    if (ini) ini.value = '';
    if (fim) fim.value = '';
    await renderListaCaixas();
  });
}

/* ===========================
   Procedimentos (Exames)
=========================== */
function renderChipsExames() {
  const wrap  = el("#chipsExames");
  if (!wrap) return;

  const procs = window._cache.procedimentos || [];
  const nomes = procs.map(p => p.nome);

  wrap.innerHTML = nomes.length
    ? nomes.map((n) => `<span class="chip" data-exame="${n}">${n}</span>`).join("")
    : `<small>Nenhum exame cadastrado. Cadastre em "Valores dos Exames".</small>`;

  wrap.querySelectorAll(".chip").forEach((ch) => {
    ch.addEventListener("click", () => {
      ch.classList.toggle("active");
      updateExamesTotalInfo();
    });
  });

  const tabelaSel = el("#tabela");
  if (tabelaSel) tabelaSel.addEventListener("change", updateExamesTotalInfo);

  updateExamesTotalInfo();
}

function updateExamesTotalInfo() {
  const infoEl = el("#examesTotalInfo");
  if (!infoEl) return;

  const procs = window._cache.procedimentos || [];
  const mapByName = Object.fromEntries(procs.map(p => [p.nome, p]));

  const sel = Array.from(document.querySelectorAll("#chipsExames .chip.active"))
    .map((x) => x.dataset.exame);

  const tabelaLower = (el("#tabela")?.value || "").toLowerCase();

  const total = sel.reduce((acc, n) => {
    const v = mapByName[n] || { valor_cartao: 0, valor_particular: 0, valor_otica: 0 };
    if (tabelaLower === "particular") return acc + Number(v.valor_particular || 0);
    if (tabelaLower.includes("cart")) return acc + Number(v.valor_cartao || 0);
    if (tabelaLower.includes("ótica") || tabelaLower.includes("otica")) return acc + Number(v.valor_otica || 0);
    return acc;
  }, 0);

  const legendaTabela = tabelaLower ? ` (Tabela: ${el("#tabela").value})` : "";
  infoEl.textContent = sel.length
    ? `Exames selecionados: ${sel.join(", ")}${legendaTabela} | Total de exames: ${fmt(total)}`
    : "";
}

async function renderProcedimentos() {
  const tb = el("#tabelaProcedimentos tbody");
  if (!tb) return;

  const r = await API.proc.list();
  const procs = r.data || [];
  window._cache.procedimentos = procs;

  const linhas = procs
    .sort((a, b) => a.nome.localeCompare(b.nome))
    .map((p) => `
      <tr>
        <td>${p.nome}</td>
        <td>${fmt(p.valor_cartao)}</td>
        <td>${fmt(p.valor_particular)}</td>
        <td>${fmt(p.valor_otica || 0)}</td>
        <td>
          <button class="btn btn-outline"
            data-edit="${p.id}"
            data-nome="${p.nome}"
            data-vc="${p.valor_cartao}"
            data-vp="${p.valor_particular}"
            data-vo="${p.valor_otica || 0}"
          >Editar</button>
          <button class="btn" data-del="${p.id}">Excluir</button>
        </td>
      </tr>
    `).join("");

  tb.innerHTML = linhas || `<tr><td colspan="5">Nenhum exame cadastrado.</td></tr>`;

  // Editar → preenche o form
  tb.querySelectorAll("[data-edit]").forEach((btn) => {
    btn.addEventListener("click", () => {
      el("#procNome").value = btn.dataset.nome;
      el("#procValorCartao").value = Number(btn.dataset.vc) || 0;
      el("#procValorParticular").value = Number(btn.dataset.vp) || 0;
      const vo = el("#procValorOtica");
      if (vo) vo.value = Number(btn.dataset.vo) || 0;
    });
  });

  // Excluir
  tb.querySelectorAll("[data-del]").forEach((btn) => {
    btn.addEventListener("click", async () => {
      const id = Number(btn.dataset.del);
      if (!confirm("Excluir este exame?")) return;
      const rr = await API.proc.del(id);
      if (!rr.ok) { alert(rr.erro || "Erro ao excluir"); return; }
      await renderProcedimentos();
      renderChipsExames();
      updateExamesTotalInfo();
    });
  });
}

function setupProcedimentos() {
  const form = el("#formProcedimento");
  if (!form) return;
  form.addEventListener("submit", async (e) => {
    e.preventDefault();
    const nome        = el("#procNome").value.trim();
    const cartao      = parseNumber(el("#procValorCartao").value);
    const particular  = parseNumber(el("#procValorParticular").value);
    const oticaInp    = el("#procValorOtica");
    const otica       = oticaInp ? parseNumber(oticaInp.value) : 0;
    if (!nome) { alert("Informe o nome do exame."); return; }
    const r = await API.proc.upsert(nome, cartao, particular, otica); // 4º parâmetro: Ótica
    if (!r.ok) { alert(r.erro || "Erro ao salvar exame"); return; }

    el("#procNome").value = "";
    el("#procValorCartao").value = "";
    el("#procValorParticular").value = "";
    if (oticaInp) oticaInp.value = "";

    await renderProcedimentos();
    renderChipsExames();
    updateExamesTotalInfo();
  });
}

/* ===========================
   Profissionais & Especialidades
=========================== */
async function renderProfissionais() {
  const tb = el("#tabelaProf tbody");
  if (!tb) return;

  const res = await API.prof.list();
  const arr = res.data || [];
  window._cache.profissionais = arr;

  tb.innerHTML = arr.length
    ? arr.map((p) => `
        <tr>
          <td>${p.nome}</td>
          <td>
            <button class="btn btn-outline" data-edit="${p.id}" data-nome="${p.nome}">Editar</button>
            <button class="btn" data-del="${p.id}">Excluir</button>
          </td>
        </tr>
      `).join("")
    : `<tr><td colspan="2">Nenhum profissional.</td></tr>`;

  tb.querySelectorAll("[data-edit]").forEach((b) => {
    b.addEventListener("click", async () => {
      const id  = Number(b.dataset.edit);
      const novo = prompt("Editar nome do profissional:", b.dataset.nome || "");
      if (!novo) return;
      const rr = await API.prof.update(id, novo);
      if (!rr.ok) { alert(rr.erro || "Erro ao atualizar"); return; }
      await renderProfissionais();
      await hydrateProfEspSelects();
    });
  });

  tb.querySelectorAll("[data-del]").forEach((b) => {
    b.addEventListener("click", async () => {
      const id = Number(b.dataset.del);
      if (!confirm("Excluir este profissional?")) return;
      const rr = await API.prof.del(id);
      if (!rr.ok) { alert(rr.erro || "Erro ao excluir"); return; }
      await renderProfissionais();
      await hydrateProfEspSelects();
    });
  });
}

async function renderEspecialidades() {
  const tb = el("#tabelaEsp tbody");
  if (!tb) return;

  const res = await API.esp.list();
  const arr = res.data || [];
  window._cache.especialidades = arr;

  tb.innerHTML = arr.length
    ? arr.map((p) => `
        <tr>
          <td>${p.nome}</td>
          <td>
            <button class="btn btn-outline" data-edit="${p.id}" data-nome="${p.nome}">Editar</button>
            <button class="btn" data-del="${p.id}">Excluir</button>
          </td>
        </tr>
      `).join("")
    : `<tr><td colspan="2">Nenhuma especialidade.</td></tr>`;

  tb.querySelectorAll("[data-edit]").forEach((b) => {
    b.addEventListener("click", async () => {
      const id  = Number(b.dataset.edit);
      const novo = prompt("Editar nome da especialidade:", b.dataset.nome || "");
      if (!novo) return;
      const rr = await API.esp.update(id, novo);
      if (!rr.ok) { alert(rr.erro || "Erro ao atualizar"); return; }
      await renderEspecialidades();
      await hydrateProfEspSelects();
    });
  });

  tb.querySelectorAll("[data-del]").forEach((b) => {
    b.addEventListener("click", async () => {
      const id = Number(b.dataset.del);
      if (!confirm("Excluir esta especialidade?")) return;
      const rr = await API.esp.del(id);
      if (!rr.ok) { alert(rr.erro || "Erro ao excluir"); return; }
      await renderEspecialidades();
      await hydrateProfEspSelects();
    });
  });
}

async function setupProfEsp() {
  const formProf = el("#formProf");
  if (formProf) {
    formProf.addEventListener("submit", async (e) => {
      e.preventDefault();
      const nome = el("#profNome").value.trim();
      if (!nome) return;
      const rr = await API.prof.add(nome);
      if (!rr.ok) { alert(rr.erro || "Erro ao adicionar"); return; }
      el("#profNome").value = "";
      await renderProfissionais();
      await hydrateProfEspSelects();
    });
  }

  const formEsp = el("#formEsp");
  if (formEsp) {
    formEsp.addEventListener("submit", async (e) => {
      e.preventDefault();
      const nome = el("#espNome").value.trim();
      if (!nome) return;
      const rr = await API.esp.add(nome);
      if (!rr.ok) { alert(rr.erro || "Erro ao adicionar"); return; }
      el("#espNome").value = "";
      await renderEspecialidades();
      await hydrateProfEspSelects();
    });
  }
}

async function hydrateProfEspSelects() {
  if (!window._cache.profissionais?.length) {
    const r = await API.prof.list();
    window._cache.profissionais = r.data || [];
  }
  if (!window._cache.especialidades?.length) {
    const r = await API.esp.list();
    window._cache.especialidades = r.data || [];
  }

  const profs = window._cache.profissionais;
  const esps  = window._cache.especialidades;

  const profSel    = el("#profissional");
  const espSel     = el("#especialidade");
  const filtroProf = el("#filtroProf");
  const filtroEsp  = el("#filtroEsp");

  const optProfs = `<option value="">—</option>${profs.map((p) => `<option value="${p.id}">${p.nome}</option>`).join("")}`;
  const optEsps  = `<option value="">—</option>${esps.map((e) => `<option value="${e.id}">${e.nome}</option>`).join("")}`;

  if (profSel) profSel.innerHTML = optProfs;
  if (espSel)  espSel.innerHTML  = optEsps;

  if (filtroProf) filtroProf.innerHTML = `<option value="">Todos</option>${profs.map((p) => `<option value="${p.id}">${p.nome}</option>`).join("")}`;
  if (filtroEsp)  filtroEsp.innerHTML  = `<option value="">Todas</option>${esps.map((e) => `<option value="${e.id}">${e.nome}</option>`).join("")}`;
}

/* ===========================
   Recebimentos
=========================== */
function setupRecebimentos() {
  const form = el("#formRecebimento");
  if (!form) return;

  form.addEventListener("submit", async (e) => {
    e.preventDefault();

    if (!state.caixaAtivo) {
      alert("Abra o caixa para lançar recebimentos.");
      return;
    }

    const pacienteNome    = el("#pacienteNome").value.trim();
    const pacienteCPF     = el("#pacienteCPF").value.replace(/\D+/g, "");
    const valor           = parseNumber(el("#valor").value);
    const formaPagamento  = el("#formaPagamento").value;
    const tabela          = el("#tabela").value;
    const baixa           = el("#baixa").value;
    const indicador       = el("#indicador").value;
    const profissionalId  = el("#profissional").value || "";
    const especialidadeId = el("#especialidade").value || "";
    const observacao      = el("#observacao").value.trim();

    const examesSel = Array.from(document.querySelectorAll("#chipsExames .chip.active"))
      .map((x) => x.dataset.exame);

    if (!pacienteNome || !valor || !formaPagamento || !tabela || !baixa || !indicador) {
      alert("Preencha os campos obrigatórios.");
      return;
    }

    // nomes -> ids
    if (!window._cache.procedimentos?.length) {
      const r = await API.proc.list();
      window._cache.procedimentos = r.data || [];
    }
    const ids = window._cache.procedimentos
      .filter(p => examesSel.includes(p.nome))
      .map(p=>p.id).join(',');

    const resp = await API.rec.add({
      caixa_id: state.caixaAtivo,
      paciente_nome: pacienteNome,
      paciente_cpf: pacienteCPF,
      valor,
      forma_pagamento: formaPagamento,
      tabela,
      baixa,
      indicador,
      profissional_id: profissionalId,
      especialidade_id: especialidadeId,
      observacao,
      procedimento_id_list: ids
    });

    if (!resp.ok) { alert(resp.erro || "Erro ao lançar recebimento"); return; }

    el("#formRecebimento").reset();
    els("#chipsExames .chip.active").forEach((ch) => ch.classList.remove("active"));
    updateExamesTotalInfo();
    await renderRecebimentosDoDia();
    await refreshKPIs();
    await renderFechamento();
  });

  renderChipsExames();
}

async function renderRecebimentosDoDia() {
  const tb = el("#tabelaRecebimentos tbody");
  if (!tb) return;

  await ensureProcedimentosCache();
  const maps = buildProcMaps();

  const hoje = yyyyMMdd(new Date());
  const cxResp = await API.caixa.getByDia(state.usuarioAtivo, hoje);
  const cx = cxResp.data;
  const listaRaw = cx ? ((await API.rec.listByCaixa(cx.id)).data || []) : [];

  // Normaliza exames para garantir preços (inclui Ótica)
  const lista = listaRaw.map(r => ({ ...r, _exames: normalizeExamesRecord(r, maps) }));

  const linhas = lista.map((r) => {
    const t = (r.tabela || '').toLowerCase();
    const totalExames = (r._exames || []).reduce((acc, info) => acc + priceForTabela(info, t), 0);
    const examesNomes = (r._exames || []).map(x=>x.nome).join(", ");
    const totalAtendimento = Number(r.valor || 0) + totalExames;

    return `
      <tr>
        <td>${fmtTS(r.created_at)}</td>
        <td>${r.paciente_nome}</td>
        <td>${fmt(r.valor)}</td>
        <td>${r.forma_pagamento}</td>
        <td>${r.tabela}</td>
        <td>${r.baixa}</td>
        <td>${r.indicador}</td>
        <td>${r.profissional_nome || "—"}</td>
        <td>${r.especialidade_nome || "—"}</td>
        <td>${examesNomes || "—"}</td>
        <td>${fmt(totalExames)}</td>
        <td><strong>${fmt(totalAtendimento)}</strong></td>
        <td>${r.observacao || ""}</td>
      </tr>
    `;
  }).join("");

  tb.innerHTML = linhas || `<tr><td colspan="13">Nenhum lançamento hoje.</td></tr>`;

  // Totais abaixo (base + exames)
  const totaisEl = el("#totaisRecebimentos");
  if (totaisEl) {
    const totalRecebido = lista.reduce((a, r) => a + Number(r.valor || 0), 0);
    const totalExamesDia = lista.reduce((acc, r) => {
      const t = (r.tabela || '').toLowerCase();
      return acc + (r._exames || []).reduce((s, info) => s + priceForTabela(info, t), 0);
    }, 0);

    totaisEl.innerHTML = `
      <div class="line"><strong>Total Recebido:</strong> <span class="pill">${fmt(totalRecebido)}</span></div>
      <div class="line"><strong>Somente Exames Complementares:</strong> <span class="pill">${fmt(totalExamesDia)}</span></div>
    `;
  }
}

/* ===========================
   Saídas (Transferências)
=========================== */
function setupSaidas() {
  const form = el("#formSaida");
  if (!form) return;

  form.addEventListener("submit", async (e) => {
    e.preventDefault();

    if (!state.caixaAtivo) {
      alert("Abra o caixa para lançar saídas.");
      return;
    }

    const descricao = el("#saidaDescricao").value.trim();
    const valor     = parseNumber(el("#saidaValor").value);
    const origem    = el("#saidaOrigem").value;
    const obs       = el("#saidaObs").value.trim();

    if (!descricao || !valor || !origem) {
      alert("Preencha os campos obrigatórios.");
      return;
    }

    const r = await API.saida.add({
      caixa_id: state.caixaAtivo,
      descricao,
      valor,
      origem,
      observacao: obs
    });

    if (!r.ok) { alert(r.erro || "Erro ao lançar saída"); return; }

    el("#formSaida").reset();
    await renderSaidasDoDia();
    await refreshKPIs();
    await renderFechamento();
  });
}

async function renderSaidasDoDia() {
  const tb = el("#tabelaSaidas tbody");
  if (!tb) return;

  const hoje = yyyyMMdd(new Date());
  const cx = (await API.caixa.getByDia(state.usuarioAtivo, hoje)).data;
  const lista = cx ? (await API.saida.listByCaixa(cx.id)).data || [] : [];

  tb.innerHTML = lista.length
    ? lista.map((x) => `
        <tr>
          <td>${fmtTS(x.created_at)}</td>
          <td>${x.descricao}</td>
          <td>${x.origem}</td>
          <td>${fmt(x.valor)}</td>
          <td>${x.observacao || ""}</td>
        </tr>
      `).join("")
    : `<tr><td colspan="5">Nenhuma saída hoje.</td></tr>`;
}

/* ===========================
   Relatórios + Filtros + CSV
=========================== */
function hydrateFiltros() {
  const usuarios = window._cache.usuarios || [];
  const selUser = el("#filtroUsuario");
  if (selUser) {
    selUser.innerHTML = `<option value="">Todos</option>${
      usuarios.map(u => `<option value="${u.id}">${u.nome}</option>`).join("")
    }`;
    if (state.usuarioAtivo) selUser.value = String(state.usuarioAtivo);
  }

  // popula exames para filtro "Exame específico"
  const selExame = el("#filtroExameNome");
  (async () => {
    await ensureProcedimentosCache();
    if (selExame) {
      selExame.innerHTML = `<option value="">Todos</option>${
        (window._cache.procedimentos||[])
          .sort((a,b)=>a.nome.localeCompare(b.nome))
          .map(p=>`<option value="${p.nome}">${p.nome}</option>`).join("")
      }`;
    }
  })();
}

async function aplicarFiltros() {
  const tbWrap   = el("#tabelaRelatorio tbody");
  const tbTotais = el("#tabelaTotais tbody");
  if (!tbWrap || !tbTotais) return;

  const q = {
    inicio:  (el("#filtroInicio")?.value || "1900-01-01"),
    fim:     (el("#filtroFim")?.value || "9999-12-31"),
    usuario_id: (el("#filtroUsuario")?.value || ""),
    forma:   (el("#filtroForma")?.value || ""),
    tabela:  (el("#filtroTabela")?.value || ""),
    baixa:   (el("#filtroBaixa")?.value || ""),
    indicador: (el("#filtroIndicador")?.value || ""),
    profissional_id: (el("#filtroProf")?.value || ""),
    especialidade_id: (el("#filtroEsp")?.value || ""),
    texto:   (el("#filtroTexto")?.value || "")
  };
  const examesMode = (el("#filtroExamesMode")?.value || ""); // "", "com", "sem"
  const exameNome  = (el("#filtroExameNome")?.value  || ""); // "" ou nome

  const resp = await API.relatorio.recebimentos(q);
  let lista = resp.data || [];

  // Garantia cache procedimentos + mapas
  await ensureProcedimentosCache();
  const maps = buildProcMaps();

  // 🔒 Filtra por usuário (garantia no front)
  if (q.usuario_id) {
    const uid   = String(q.usuario_id);
    const uNome = (window._cache.usuarios || []).find(u => String(u.id) === uid)?.nome;
    lista = lista.filter(r => {
      if (r.usuario_id != null)           return String(r.usuario_id) === uid;
      if (r.caixa_usuario_id != null)     return String(r.caixa_usuario_id) === uid;
      if (uNome && (r.usuario_nome || r.caixa_usuario_nome)) {
        return (r.usuario_nome === uNome) || (r.caixa_usuario_nome === uNome);
      }
      return true;
    });
  }

  // Normaliza exames de todos os registros (1x só)
  let rows = lista.map(r => ({ ...r, _exames: normalizeExamesRecord(r, maps) }));

  // 🔎 Filtros de exames
  if (examesMode === "com") rows = rows.filter(r => r._exames.length > 0);
  if (examesMode === "sem") rows = rows.filter(r => r._exames.length === 0);
  if (exameNome)           rows = rows.filter(r => r._exames.some(e => e.nome === exameNome));

  // Enriquecimento (exames conforme TABELA)
  const enriched = rows.map((r) => {
    const t = (r.tabela || '').toLowerCase();
    const valorExames = (r._exames || []).reduce((acc, info) => acc + priceForTabela(info, t), 0);
    const totalAtendimento = Number(r.valor || 0) + valorExames;
    return { ...r, _valorExames: valorExames, _totalAtendimento: totalAtendimento };
  });

  // Tabela
  const linhas = enriched.map((r) => `
    <tr>
      <td>${fmtTS(r.created_at)}</td>
      <td>${r.paciente_nome}</td>
      <td>${fmt(r.valor)}</td>
      <td>${r.forma_pagamento}</td>
      <td>${r.tabela}</td>
      <td>${r.baixa}</td>
      <td>${r.indicador}</td>
      <td>${r.profissional_nome || "—"}</td>
      <td>${r.especialidade_nome || "—"}</td>
      <td>${(r._exames||[]).map(x=>x.nome).join(", ") || "—"}</td>
      <td>${fmt(r._valorExames)}</td>
      <td><strong>${fmt(r._totalAtendimento)}</strong></td>
    </tr>
  `).join("");
  tbWrap.innerHTML = linhas || `<tr><td colspan="12">Sem resultados no filtro.</td></tr>`;

  // Totais
  const totalGeral = enriched.reduce((a, b) => a + Number(b._totalAtendimento || 0), 0);
  const addBy = (key) =>
    Object.entries(enriched.reduce((acc, x)=>{
      const k = x[key] || "—";
      acc[k] = (acc[k]||0) + Number(x.valor||0); // agrupamentos pela base (mantido)
      return acc;
    }, {})).sort((a,b)=> b[1]-a[1]);

  // Totais por Exame (respeitando tabela)
  const somaPorExame = {};
  enriched.forEach(r => {
    (r._exames||[]).forEach(info=>{
      const t = (r.tabela||'').toLowerCase();
      const preco = priceForTabela(info, t);
      somaPorExame[info.nome] = (somaPorExame[info.nome]||0) + preco;
    });
  });

  tbTotais.innerHTML = `
    <tr><td><strong>Total Geral</strong></td><td>${fmt(totalGeral)}</td></tr>
    <tr><th colspan="2">Por Forma</th></tr>
    ${addBy("forma_pagamento").map(([k,v])=>`<tr><td>${k}</td><td>${fmt(v)}</td></tr>`).join("")}
    <tr><th colspan="2">Por Indicador</th></tr>
    ${addBy("indicador").map(([k,v])=>`<tr><td>${k}</td><td>${fmt(v)}</td></tr>`).join("")}
    <tr><th colspan="2">Por Profissional</th></tr>
    ${addBy("profissional_nome").map(([k,v])=>`<tr><td>${k}</td><td>${fmt(v)}</td></tr>`).join("")}
    <tr><th colspan="2">Por Exame (Exames Complementares)</th></tr>
    ${
      Object.keys(somaPorExame).length
        ? Object.entries(somaPorExame)
          .sort((a,b)=> b[1]-a[1])
          .map(([nome,total])=>`<tr><td>${nome}</td><td>${fmt(total)}</td></tr>`).join("")
        : `<tr><td>—</td><td>${fmt(0)}</td></tr>`
    }
  `;
}

function exportarCSV() {
  const headers = [
    "Data/Hora","Paciente","Valor","Forma","Tabela","Baixa",
    "Indicador","Profissional","Especialidade","Exames","Valor Exames","Total do Atendimento"
  ];
  const rows = [headers];

  const tbRows = els("#tabelaRelatorio tbody tr");
  tbRows.forEach((tr) => {
    const cols = Array.from(tr.querySelectorAll("td")).map((td) =>
      `"${td.textContent.replace(/"/g, '""')}"`);
    if (cols.length === headers.length) rows.push(cols);
  });

  const csv  = rows.map((r) => r.join(";")).join("\n");
  const blob = new Blob([csv], { type: "text/csv;charset=utf-8;" });
  const url  = URL.createObjectURL(blob);

  const a = document.createElement("a");
  a.href = url;
  a.download = `relatorio_caixa_${Date.now()}.csv`;
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
  URL.revokeObjectURL(url);
}

/* ===========================
   Dashboard (Mês, com exames)
=========================== */
let chartFormas, chartIndicador, chartEsp, chartProf, chartTicketIndicador, chartDiario, chartFechamento;

async function refreshKPIs() {
  const kHoje = el("#kpiHoje");
  const kMes  = el("#kpiMes");
  const kDin  = el("#kpiDinheiroHoje");
  const kSai  = el("#kpiSaidasHoje");

  if (!state.usuarioAtivo) {
    if (kHoje) kHoje.textContent = fmt(0);
    if (kMes)  kMes.textContent  = fmt(0);
    if (kDin)  kDin.textContent  = fmt(0);
    if (kSai)  kSai.textContent  = fmt(0);
    return;
  }

  await ensureProcedimentosCache();

  // --- caixa do dia (apenas do usuário ativo) ---
  const hojeStr = yyyyMMdd(new Date());
  const cxResp  = await API.caixa.getByDia(state.usuarioAtivo, hojeStr);
  const cx      = cxResp.data || null;

  let totalDiaComExames = 0;
  let dinheiroHoje      = 0;
  let saidasHoje        = 0;

  if (cx) {
    const maps = buildProcMaps();
    // Recebimentos do DIA desse caixa + exames
    const recDia = await API.relatorio.recebimentos({
      inicio: cx.data_caixa,
      fim:    cx.data_caixa,
      usuario_id: state.usuarioAtivo,
    });
    const listaDia = (recDia.data || []).map(r => ({ ...r, _exames: normalizeExamesRecord(r, maps) }));

    totalDiaComExames = listaDia.reduce((acc, r) => acc + totalAtendimentoComExames(r), 0);
    dinheiroHoje      = listaDia
      .filter(r => (r.forma_pagamento || '').toLowerCase() === 'dinheiro')
      .reduce((acc, r) => acc + totalAtendimentoComExames(r), 0);

    // Saídas do DIA (do caixa atual)
    const saidas = await API.saida.listByCaixa(cx.id);
    saidasHoje = (saidas.data || []).reduce((a, s) => a + Number(s.valor || 0), 0);
  }

  // --- mês corrente (por usuário) para o KPI "Recebido (Mês)" ---
  const agora       = new Date();
  const inicioMes   = new Date(agora.getFullYear(), agora.getMonth(), 1).toISOString().slice(0, 10);
  const fimMesHoje  = yyyyMMdd(agora);

  const mapsMes = buildProcMaps();
  const recMes = await API.relatorio.recebimentos({
    inicio: inicioMes,
    fim:    fimMesHoje,
    usuario_id: state.usuarioAtivo,
  });
  const listaMes = (recMes.data || []).map(r => ({ ...r, _exames: normalizeExamesRecord(r, mapsMes) }));
  const totalMesComExames = listaMes.reduce((acc, r) => acc + totalAtendimentoComExames(r), 0);

  if (kHoje) kHoje.textContent = fmt(totalDiaComExames);  // APENAS o caixa atual (dia) + exames
  if (kMes)  kMes.textContent  = fmt(totalMesComExames);  // mês corrente + exames (do usuário)
  if (kDin)  kDin.textContent  = fmt(dinheiroHoje);       // em Dinheiro, do caixa do dia, + exames
  if (kSai)  kSai.textContent  = fmt(saidasHoje);         // saídas do caixa do dia
}

async function renderChartsDashboard() {
  if (!state.usuarioAtivo) return;

  await ensureProcedimentosCache();
  const maps = buildProcMaps();

  // Mês corrente
  const hoje      = new Date();
  const inicioMes = new Date(hoje.getFullYear(), hoje.getMonth(), 1).toISOString().slice(0, 10);
  const fimHoje   = yyyyMMdd(hoje);

  // Dados do mês, filtrados por usuário
  const respMes = await API.relatorio.recebimentos({
    inicio: inicioMes,
    fim:    fimHoje,
    usuario_id: state.usuarioAtivo,
  });
  const recs = (respMes.data || []).map(r => ({ ...r, _exames: normalizeExamesRecord(r, maps) }));

  // Helper: agrega por chave usando TOTAL (valor + exames)
  const sumByTotal = (arr, key) =>
    Object.entries(arr.reduce((acc, r) => {
      const k = r[key] || "—";
      acc[k] = (acc[k] || 0) + totalAtendimentoComExames(r);
      return acc;
    }, {})).sort((a, b) => b[1] - a[1]);

  // Por Forma
  const porForma = sumByTotal(recs, "forma_pagamento");
  if (chartFormas) chartFormas.destroy();
  const elFormas = el("#chartFormas");
  if (elFormas) {
    chartFormas = new Chart(elFormas, {
      type: "bar",
      data: { labels: porForma.map(x=>x[0]), datasets: [{ label: "R$", data: porForma.map(x=>x[1]) }] },
      options: { responsive: true, plugins: { legend: { display: false } } }
    });
  }

  // Por Indicador
  const porInd = sumByTotal(recs, "indicador");
  if (chartIndicador) chartIndicador.destroy();
  const elInd = el("#chartIndicador");
  if (elInd) {
    chartIndicador = new Chart(elInd, {
      type: "bar",
      data: { labels: porInd.map(x=>x[0]), datasets: [{ label: "R$", data: porInd.map(x=>x[1]) }] },
      options: { responsive: true, plugins: { legend: { display: false } } }
    });
  }

  // Por Especialidade
  const porEsp = sumByTotal(recs, "especialidade_nome");
  if (chartEsp) chartEsp.destroy();
  const elEsp = el("#chartEsp");
  if (elEsp) {
    chartEsp = new Chart(elEsp, {
      type: "bar",
      data: { labels: porEsp.map(x=>x[0]), datasets: [{ label: "R$", data: porEsp.map(x=>x[1]) }] },
      options: { responsive: true, plugins: { legend: { display: false } } }
    });
  }

  // Por Profissional
  const porProf = sumByTotal(recs, "profissional_nome");
  if (chartProf) chartProf.destroy();
  const elProf = el("#chartProf");
  if (elProf) {
    chartProf = new Chart(elProf, {
      type: "bar",
      data: { labels: porProf.map(x=>x[0]), datasets: [{ label: "R$", data: porProf.map(x=>x[1]) }] },
      options: { responsive: true, plugins: { legend: { display: false } } }
    });
  }

  // Ticket médio por Indicador (mês, com exames)
  const aggInd = recs.reduce((acc, r) => {
    const k = r.indicador || "—";
    if (!acc[k]) acc[k] = { soma: 0, qtd: 0 };
    acc[k].soma += totalAtendimentoComExames(r);
    acc[k].qtd  += 1;
    return acc;
  }, {});
  const ticketInd = Object.entries(aggInd)
    .map(([k, v]) => [k, v.qtd ? v.soma / v.qtd : 0])
    .sort((a,b) => b[1]-a[1]);

  if (chartTicketIndicador) chartTicketIndicador.destroy();
  const elTicket = el("#chartTicketIndicador");
  if (elTicket) {
    chartTicketIndicador = new Chart(elTicket, {
      type: "bar",
      data: { labels: ticketInd.map(x=>x[0]), datasets: [{ label: "R$/lançamento", data: ticketInd.map(x=>x[1]) }] },
      options: { responsive: true, plugins: { legend: { display: false } } }
    });
  }

  // Receita diária do mês (com exames)
  const dias = [];
  for (let d = new Date(inicioMes + "T00:00:00"); d <= new Date(fimHoje + "T00:00:00"); d.setDate(d.getDate()+1)) {
    dias.push(yyyyMMdd(d));
  }
  const somaPorDia = dias.map(day =>
    recs.filter(r => (r.created_at || "").slice(0,10) === day)
        .reduce((a,b) => a + totalAtendimentoComExames(b), 0)
  );

  if (chartDiario) chartDiario.destroy();
  const elDiario = el("#chartDiario");
  if (elDiario) {
    chartDiario = new Chart(elDiario, {
      type: "line",
      data: { labels: dias, datasets: [{ label: "R$", data: somaPorDia, tension: 0.2 }] },
      options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { x: { ticks: { maxRotation: 0, autoSkip: true } } }
      }
    });
  }
}

/* ===========================
   Fechamento (Dia Atual, com exames)
=========================== */
async function renderFechamento() {
  const cont = el("#resumoFechamento");
  const pos  = el("#posicaoDinheiro");

  if (!state.usuarioAtivo) {
    if (cont) cont.innerHTML = `<div class="line"><span>—</span><span class="pill">Selecione um usuário.</span></div>`;
    if (pos)  pos.innerHTML  = ``;
    if (chartFechamento) chartFechamento.destroy();
    return;
  }

  // 1) Dados base do caixa (data, saldo inicial, dinheiro etc.)
  const resp = await API.fecharDia(state.usuarioAtivo);
  const data = resp.data;
  if (!data) {
    if (cont) cont.innerHTML = `<div class="line"><span>—</span><span class="pill">Abra o caixa para ver o fechamento.</span></div>`;
    if (pos)  pos.innerHTML  = "";
    if (chartFechamento) chartFechamento.destroy();
    return;
  }

  await ensureProcedimentosCache();
  const maps = buildProcMaps();

  const cx  = data.caixa;
  const din = data.dinheiro || { saldo_inicial:0, recebido:0, saidas:0, saldo_final:0 };

  // 2) Recebimentos do dia do usuário ativo → Total (com exames) e Por Forma (com exames)
  const hoje = cx.data_caixa;
  const rel = await API.relatorio.recebimentos({
    inicio: hoje,
    fim: hoje,
    usuario_id: state.usuarioAtivo
  });
  const lista = (rel.data || []).map(r => ({ ...r, _exames: normalizeExamesRecord(r, maps) }));

  let totalGeral = 0;
  const porFormaTotais = {}; // forma -> soma com exames
  for (const r of lista) {
    const totalAtendimento = totalAtendimentoComExames(r);
    totalGeral += totalAtendimento;
    const forma = r.forma_pagamento || "—";
    porFormaTotais[forma] = (porFormaTotais[forma] || 0) + totalAtendimento;
  }

  if (cont) {
    const linhasPorForma = Object.entries(porFormaTotais)
      .sort((a,b)=>b[1]-a[1])
      .map(([forma, soma]) => `<div class="line"><span>${forma}</span><span class="pill">${fmt(soma)}</span></div>`)
      .join("");

    cont.innerHTML = `
      <div class="line"><span>Data</span><span class="pill">${cx.data_caixa}</span></div>
      <div class="line"><span>Saldo Inicial (Dinheiro)</span><span class="pill">${fmt(cx.saldo_inicial)}</span></div>
      <div class="line"><strong>Total Recebido (com exames)</strong><strong class="pill">${fmt(totalGeral)}</strong></div>
      <hr/>
      <strong>Por Forma de Pagamento (com exames)</strong>
      ${linhasPorForma || `<div class="line"><span>—</span><span class="pill">${fmt(0)}</span></div>`}
    `;
  }

  if (pos) {
    pos.innerHTML = `
      <div class="line"><span>Saldo Inicial</span><span class="pill">${fmt(din.saldo_inicial)}</span></div>
      <div class="line"><span>Recebido em Dinheiro</span><span class="pill">${fmt(din.recebido)}</span></div>
      <div class="line"><span>Saídas em Dinheiro</span><span class="pill">-${fmt(din.saidas)}</span></div>
      <hr/>
      <div class="line"><strong>Saldo Final em Dinheiro</strong><strong class="pill">${fmt(din.saldo_final)}</strong></div>
    `;
  }

  if (chartFechamento) chartFechamento.destroy();
  const elFech = el("#chartFechamento");
  if (elFech) {
    const labels = Object.keys(porFormaTotais);
    const valores = labels.map(k => porFormaTotais[k]);
    chartFechamento = new Chart(elFech, {
      type: "doughnut",
      data: { labels, datasets: [{ data: valores }] },
      options: { responsive: true, plugins: { legend: { position: "bottom" } } }
    });
  }
}

/* ===========================
   Init
=========================== */
async function init() {
  await bootstrap();

  // header usuário
  await hydrateUserHeader();

  setupNav();
  await hydrateUsuarios();
  const addU = el("#btnAddUsuario");
  if (addU) addU.addEventListener("click", addUsuario);

  // botão sair
  const btnLogout = el("#btnLogout");
  if (btnLogout) {
    btnLogout.addEventListener("click", async () => {
      try {
        const fd = new FormData();
        fd.append("action", "auth.logout");
        await fetch("auth.php", { method: "POST", body: fd, credentials: "same-origin" });
      } catch {}
      window.location.href = "login.html";
    });
  }

  setupAbertura();
  setupFiltroCaixas();
  await renderListaCaixas();
  setupRecebimentos();
  setupSaidas();
  setupProcedimentos();
  await setupProfEsp();
  await hydrateProfEspSelects();
  renderChipsExames();
  await updateCaixaStatusUI();

  // Data padrão p/ relatórios (início do mês até hoje)
  const hoje = new Date();
  const inicioMes = new Date(hoje.getFullYear(), hoje.getMonth(), 1).toISOString().slice(0, 10);
  const fIni = el("#filtroInicio");
  const fFim = el("#filtroFim");
  if (fIni) fIni.value = inicioMes;
  if (fFim) fFim.value = yyyyMMdd(hoje);
  hydrateFiltros();
  await aplicarFiltros();

  // Reaplicar ao trocar filtros
  const filtroUserSel = el("#filtroUsuario");
  if (filtroUserSel) filtroUserSel.addEventListener("change", aplicarFiltros);
  ["#filtroExamesMode", "#filtroExameNome", "#filtroForma", "#filtroTabela", "#filtroBaixa", "#filtroIndicador", "#filtroProf", "#filtroEsp"]
    .forEach(id => {
      const s = el(id);
      if (s) s.addEventListener("change", aplicarFiltros);
    });

  // Dashboard + Fechamento
  await refreshKPIs();
  await renderChartsDashboard();
  await renderFechamento();

  // Relatório botões
  const btnFiltros = el("#btnAplicarFiltros");
  const btnCSV     = el("#btnExportarCSV");
  if (btnFiltros) btnFiltros.addEventListener("click", aplicarFiltros);
  if (btnCSV)     btnCSV.addEventListener("click", exportarCSV);
}

document.addEventListener("DOMContentLoaded", init);
