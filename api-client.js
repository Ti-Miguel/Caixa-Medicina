// api-client.js
const API = {
  base: "api.php",

  // utilidade
  async get(params) {
    const q = new URLSearchParams(params).toString();
    const r = await fetch(`${this.base}?${q}`, {
      credentials: "same-origin",
    });
    if (r.status === 401) {
      window.location.href = "login.html";
      return { ok: false, erro: "Não autenticado" };
    }
    return r.json();
  },

  async post(action, form) {
    const fd = new FormData();
    fd.append("action", action);
    Object.entries(form || {}).forEach(([k, v]) => fd.append(k, v ?? ""));
    const r = await fetch(this.base, {
      method: "POST",
      body: fd,
      credentials: "same-origin",
    });
    if (r.status === 401) {
      window.location.href = "login.html";
      return { ok: false, erro: "Não autenticado" };
    }
    return r.json();
  },

  // Usuários (para selects e compatibilidade com o app)
  usuarios: {
    list: () => API.get({ action: "usuarios.list" }),
    // OBS: para criar logins reais, use auth.php (action=auth.createUser).
    // Este add existe só por compatibilidade com o app (bootstrap);
    // ele cria usuário "sem login" (inativo) se não receber email/senha.
    add: (nome, email = "", senha = "") =>
      API.post("usuarios.add", { nome, email, senha }),
  },

  // Profissionais
  prof: {
    list: () => API.get({ action: "prof.list" }),
    add: (nome) => API.post("prof.add", { nome }),
    update: (id, nome) => API.post("prof.update", { id, nome }),
    del: (id) => API.post("prof.del", { id }),
  },

  // Especialidades
  esp: {
    list: () => API.get({ action: "esp.list" }),
    add: (nome) => API.post("esp.add", { nome }),
    update: (id, nome) => API.post("esp.update", { id, nome }),
    del: (id) => API.post("esp.del", { id }),
  },

  // Procedimentos
  proc: {
    list: () => API.get({ action: "proc.list" }),
    upsert: (nome, valor_cartao, valor_particular) =>
      API.post("proc.upsert", { nome, valor_cartao, valor_particular }),
    del: (id) => API.post("proc.del", { id }),
  },

  // Caixa
  caixa: {
    abrir: (usuario_id, data_caixa, saldo_inicial, obs) =>
      API.post("caixa.abrir", {
        usuario_id,
        data_caixa,
        saldo_inicial,
        obs,
      }),
    encerrar: (usuario_id, data_caixa) =>
      API.post("caixa.encerrar", { usuario_id, data_caixa }),
    getByDia: (usuario_id, data_caixa) =>
      API.get({ action: "caixa.getByDia", usuario_id, data_caixa }),
    list: (ini, fim) => API.get({ action: "caixa.list", ini, fim }),
  },

  // Recebimentos
  rec: {
    add: (payload) => API.post("rec.add", payload),
    listByCaixa: (caixa_id) =>
      API.get({ action: "rec.listByCaixa", caixa_id }),
  },

  // Saídas
  saida: {
    add: (payload) => API.post("saida.add", payload),
    listByCaixa: (caixa_id) =>
      API.get({ action: "saida.listByCaixa", caixa_id }),
  },

  // Relatórios
  relatorio: {
    recebimentos: (q) => API.get({ action: "relatorio.recebimentos", ...q }),
  },

  // Dashboard & Fechamento
  kpis: (usuario_id) => API.get({ action: "dashboard.kpis", usuario_id }),
  fecharDia: (usuario_id) => API.get({ action: "fechamento.doDia", usuario_id }),
};
