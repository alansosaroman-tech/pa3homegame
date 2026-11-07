const API = 'api/';
const contenido = document.getElementById("contenido");

let estado = { pagina: 1, porPagina: 6, filtros: {} };

function cargarPagina(pagina) {
  fetch(`paginas/${pagina}.html`)
    .then(response => response.text())
    .then(html => {
      contenido.innerHTML = html;

      if (pagina === 'productos') {
        estado = { pagina: 1, porPagina: 6, filtros: {} };
        cargarCategorias();
        cargarProductos();
        document.getElementById('form-filtros').addEventListener('submit', filtrarProductos);
        document.getElementById('form-filtros').addEventListener('reset', limpiarFiltros);
        document.getElementById('por-pagina').addEventListener('change', cambiarPorPagina);
      }
    });
}

document.addEventListener('click', (evento) => {
  const link = evento.target.closest('[data-page]');
  if (link) { evento.preventDefault(); cargarPagina(link.dataset.page); }

  const boton = evento.target.closest('[data-ver-mas]');
  if (boton) mostrarDetalle(boton.dataset.verMas);

  const botonPagina = evento.target.closest('[data-pag]');
  if (botonPagina && !botonPagina.disabled) irAPagina(Number(botonPagina.dataset.pag));

  if (evento.target.id === 'cerrar-modal') {
    document.getElementById('modal').classList.add('hidden');
  }
});

function mostrarDetalle(id) {
  fetch(`${API}producto.php?id=${encodeURIComponent(id)}`)
    .then(respuesta => respuesta.json())
    .then(data => {
      if (!data.ok) { alert(data.error); return; }
      const p = data.producto;
      const imgs = p.imagenes.map(u => `<img src="${u}" class="h-40 object-contain rounded">`).join('');
      document.getElementById('modal-contenido').innerHTML = `
        <div class="flex gap-2 overflow-x-auto mb-4">${imgs}</div>
        <h2 class="text-xl font-bold">${p.nombre}</h2>
        <p class="text-xs text-slate-400">${p.categoria} (cat. ${p.categoria_id}) · ID ${p.id}</p>
        <p class="text-lime-400 text-2xl font-bold my-2">S/ ${p.precio.toFixed(2)}</p>
        <p class="text-sm text-slate-300">${p.descripcion ?? ''}</p>
        <p class="text-sm mt-2">Stock disponible: <b>${p.stock}</b></p>
      `;
      document.getElementById('modal').classList.remove('hidden');
    })
    .catch(() => alert('No se pudo conectar con el servicio'));
}

function pintarProductos(productos) {
  const contenedor = document.getElementById('contenedor-productos');
  if (!productos.length) {
    contenedor.innerHTML = '<p class="text-slate-400 col-span-full">No se encontraron productos.</p>';
    return;
  }
  contenedor.innerHTML = productos.map(p => `
    <div class="bg-slate-800 rounded-xl p-4 border border-slate-700">
      <img src="${p.imagenes[0] ?? ''}" class="h-32 mx-auto object-contain">
      <h3 class="text-sm mt-2">${p.nombre}</h3>
      <p class="text-xs text-slate-400">${p.categoria} · Stock: ${p.stock}</p>
      <p class="text-lime-400 font-bold">S/ ${p.precio.toFixed(2)}</p>
      <button data-ver-mas="${p.id}" class="bg-lime-500 text-slate-900 font-bold w-full mt-2 py-1 rounded">Ver más</button>
    </div>
  `).join('');
}

function cargarProductos() {
  const msg = document.getElementById('mensaje-filtros');

  fetch(`${API}filtrar.php`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      ...estado.filtros,
      pagina: estado.pagina,
      por_pagina: estado.porPagina
    })
  })
    .then(r => r.json())
    .then(data => {
      if (!data.ok) {
        msg.textContent = (data.errores || [data.error]).join(' · ');
        msg.className = 'text-sm text-red-400';
        return;
      }
      const p = data.paginacion;
      if (p.total_registros === 0) {
        msg.textContent = '0 producto(s) encontrado(s)';
      } else {
        const desde = (p.pagina_actual - 1) * p.por_pagina + 1;
        const hasta = desde + data.productos.length - 1;
        msg.textContent = `Mostrando ${desde}-${hasta} de ${p.total_registros} producto(s)`;
      }
      msg.className = 'text-sm text-slate-400';
      pintarProductos(data.productos);
      pintarPaginacion(p);
    })
    .catch(() => {
      document.getElementById('contenedor-productos').innerHTML =
        '<p class="text-red-400 col-span-full">No se pudo conectar con el servicio.</p>';
      document.getElementById('paginacion').innerHTML = '';
    });
}

function pintarPaginacion(p) {
  const nav = document.getElementById('paginacion');

  if (p.total_registros === 0) {
    nav.innerHTML = '';
    return;
  }

  const base = 'px-3 py-1 rounded font-bold ';
  let html = `<button data-pag="${p.pagina_actual - 1}"
      class="${base} bg-slate-700 disabled:opacity-40"
      ${p.pagina_actual <= 1 ? 'disabled' : ''}>Anterior</button>`;

  for (let i = 1; i <= p.total_paginas; i++) {
    const activa = i === p.pagina_actual;
    html += `<button data-pag="${i}"
      class="${base} ${activa ? 'bg-lime-500 text-slate-900' : 'bg-slate-700 hover:bg-slate-600'}"
      ${activa ? 'aria-current="page"' : ''}>${i}</button>`;
  }

  html += `<button data-pag="${p.pagina_actual + 1}"
      class="${base} bg-slate-700 disabled:opacity-40"
      ${p.pagina_actual >= p.total_paginas ? 'disabled' : ''}>Siguiente</button>`;

  nav.innerHTML = html;
}

function irAPagina(numero) {
  estado.pagina = numero;
  cargarProductos();
  document.getElementById('form-filtros').scrollIntoView({ behavior: 'smooth' });
}

function cambiarPorPagina(evento) {
  estado.porPagina = Number(evento.target.value);
  estado.pagina = 1;
  cargarProductos();
}

function limpiarFiltros() {
  setTimeout(() => {
    estado.filtros = {};
    estado.pagina = 1;
    cargarProductos();
  }, 0);
}

function cargarCategorias() {
  fetch(`${API}categorias.php`)
    .then(r => r.json())
    .then(data => {
      const select = document.getElementById('filtro-categoria');
      data.categorias.forEach(c => select.insertAdjacentHTML('beforeend',
        `<option value="${c.id}">${c.nombre}</option>`));
    });
}

function filtrarProductos(evento) {
  evento.preventDefault();
  const filtros = {};
  new FormData(evento.target).forEach((valor, clave) => { if (valor !== '') filtros[clave] = valor; });

  estado.filtros = filtros;
  estado.pagina = 1;
  cargarProductos();
}

cargarPagina('inicio');
