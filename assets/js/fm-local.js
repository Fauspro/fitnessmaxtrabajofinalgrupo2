/* Fitness Max - modo HTML (sin PHP ni base de datos).
   Los datos del gimnasio están acá y lo que hace el visitante (pedidos, preguntas,
   cuenta de socio y reservas) se guarda en el navegador (localStorage).
   Este archivo también reemplaza a pedido.php, pregunta.php y api_planes.php. */
(function () {
    var FM = window.FM = {};

    /* ---------- Datos del gimnasio ---------- */
    FM.planes = [
        { id: 1, nombre: 'Básico', precio: 18000, descripcion: 'Acceso al gimnasio · Sala de musculación · Cardio · Vestuarios' },
        { id: 2, nombre: 'Premium', precio: 26000, descripcion: 'Todo el Plan Básico · Clases grupales · Funcional · Boxeo · Yoga' },
        { id: 3, nombre: 'Full', precio: 35000, descripcion: 'Acceso ilimitado · Todas las clases · Nutricionista · Personal Trainer · Seguimiento mensual' }
    ];
    FM.clases = [
        { id: 1, nombre: 'Funcional', dia: 'Lunes', hora: '19:00', cupo: 15, instructor: 'Coach Diego', plan_id: 2 },
        { id: 2, nombre: 'Spinning', dia: 'Martes', hora: '20:00', cupo: 12, instructor: 'Coach Diego', plan_id: 2 },
        { id: 3, nombre: 'Yoga', dia: 'Miércoles', hora: '18:00', cupo: 15, instructor: 'Coach Laura', plan_id: 2 },
        { id: 4, nombre: 'Pilates', dia: 'Jueves', hora: '19:00', cupo: 12, instructor: 'Coach Laura', plan_id: 2 },
        { id: 5, nombre: 'Boxeo', dia: 'Viernes', hora: '20:00', cupo: 15, instructor: 'Pablo González', plan_id: 2 },
        { id: 6, nombre: 'Funcional libre', dia: 'Sábado', hora: '10:00', cupo: 20, instructor: 'Pablo González', plan_id: 1 }
    ];
    FM.productos = [
        { id: 1, nombre: 'Proteína Whey', precio: 38000, descripcion: 'Ideal para aumentar y recuperar masa muscular.', imagen: 'whey.jpg' },
        { id: 2, nombre: 'Creatina', precio: 27000, descripcion: 'Mayor fuerza y mejor rendimiento en cada entrenamiento.', imagen: 'creatina.jpg' },
        { id: 3, nombre: 'Pre Entreno', precio: 24500, descripcion: 'Energía y concentración para entrenar al máximo.', imagen: 'pre.jpg' },
        { id: 4, nombre: 'BCAA', precio: 22000, descripcion: 'Favorece la recuperación muscular después del ejercicio.', imagen: 'bcaa.jpg' },
        { id: 5, nombre: 'Glutamina', precio: 21000, descripcion: 'Ayuda a la recuperación y al cuidado muscular.', imagen: 'glutamina.jpg' },
        { id: 6, nombre: 'Multivitamínico', precio: 15000, descripcion: 'Vitaminas y minerales para tu rendimiento diario.', imagen: 'multivitaminico.jpg' },
        { id: 7, nombre: 'Barras de proteína (x12)', precio: 18500, descripcion: 'Snack práctico con alto contenido de proteínas.', imagen: 'barras.jpg' },
        { id: 8, nombre: 'Quemador de grasa', precio: 29000, descripcion: 'Complemento para acompañar tu plan de definición.', imagen: 'quemador.jpg' },
        { id: 9, nombre: 'Shaker 700 ml', precio: 6500, descripcion: 'Mezclador con tapa antiderrame y rejilla.', imagen: 'shaker.jpg' },
        { id: 10, nombre: 'Guantes de entrenamiento', precio: 12000, descripcion: 'Agarre firme y protección para tus manos.', imagen: 'guantes.jpg' },
        { id: 11, nombre: 'Cinturón de levantamiento', precio: 25000, descripcion: 'Soporte lumbar para cargas pesadas.', imagen: 'cinturon.jpg' },
        { id: 12, nombre: 'Bandas elásticas (set x3)', precio: 9800, descripcion: 'Resistencia progresiva para entrenar en cualquier lugar.', imagen: 'bandas.jpg' },
        { id: 13, nombre: 'Toalla deportiva', precio: 5500, descripcion: 'Microfibra de secado rápido.', imagen: 'toalla.jpg' },
        { id: 14, nombre: 'Botella térmica 1 L', precio: 11000, descripcion: 'Mantiene tu bebida fría durante horas.', imagen: 'botella.jpg' }
    ];
    FM.DIAS = { 'Lunes': 1, 'Martes': 2, 'Miércoles': 3, 'Jueves': 4, 'Viernes': 5, 'Sábado': 6, 'Domingo': 7 };

    /* ---------- Almacenamiento en el navegador ---------- */
    function leer(k, def) { try { var v = localStorage.getItem(k); return v ? JSON.parse(v) : def; } catch (e) { return def; } }
    function guardar(k, v) { try { localStorage.setItem(k, JSON.stringify(v)); } catch (e) { } }
    function pad(n) { return String(n).padStart(2, '0'); }
    function ymd(d) { return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()); }
    function ahora() { var d = new Date(); return ymd(d) + ' ' + pad(d.getHours()) + ':' + pad(d.getMinutes()) + ':' + pad(d.getSeconds()); }
    FM.hoy = function () { return ymd(new Date()); };
    FM.ymd = ymd;
    FM.dinero = function (n) { return '$' + Number(n).toLocaleString('es-AR'); };
    function plan(id) { return FM.planes.filter(function (p) { return p.id === id; })[0] || null; }
    function clase(id) { return FM.clases.filter(function (c) { return c.id === id; })[0] || null; }
    FM.plan = plan; FM.clase = clase;

    /* ---------- Socios, sesión, reservas y pagos ---------- */
    // La contraseña no se guarda en texto plano (es una demo local: no reemplaza a un servidor real).
    function hash(s) {
        var h1 = 5381, h2 = 52711;
        for (var i = 0; i < s.length; i++) { var c = s.charCodeAt(i); h1 = (h1 * 33) ^ c; h2 = (h2 * 31) ^ c; }
        return (h1 >>> 0).toString(36) + (h2 >>> 0).toString(36) + s.length;
    }
    function socios() { return leer('fm_socios', []); }
    FM.sesion = function () {
        var id = leer('fm_sesion', 0);
        return socios().filter(function (s) { return s.id === id; })[0] || null;
    };
    FM.cerrarSesion = function () { try { localStorage.removeItem('fm_sesion'); } catch (e) { } };

    FM.activar = function (d) {
        var dni = String(d.dni || '').replace(/\D/g, ''), email = String(d.email || '').trim().toLowerCase();
        var nombre = String(d.nombre || '').trim().replace(/\s+/g, ' ');
        var e = Validar.reglas.nombre(nombre) || Validar.reglas.email(email);
        if (e) return { ok: false, error: e };
        if (!/^\d{7,9}$/.test(dni)) return { ok: false, error: 'El DNI debe tener entre 7 y 9 números.' };
        if (!plan(+d.plan)) return { ok: false, error: 'Elegí un plan.' };
        if (String(d.clave || '').length < 6) return { ok: false, error: 'La contraseña debe tener al menos 6 caracteres.' };
        if (d.clave !== d.clave2) return { ok: false, error: 'Las contraseñas no coinciden.' };
        var lista = socios();
        if (lista.some(function (s) { return s.dni === dni; })) return { ok: false, error: 'Ese DNI ya tiene una cuenta activada. Ingresá con tu contraseña.' };
        var id = lista.reduce(function (m, s) { return Math.max(m, s.id); }, 0) + 1;
        lista.push({ id: id, nombre: nombre, dni: dni, email: email, plan_id: +d.plan, estado: 'activo', clave: hash(d.clave) });
        guardar('fm_socios', lista);
        var pagos = leer('fm_pagos', []);
        pagos.push({ socio_id: id, concepto: 'Cuota mensual - Plan ' + plan(+d.plan).nombre, monto: plan(+d.plan).precio, fecha: FM.hoy(), estado: 'pendiente' });
        guardar('fm_pagos', pagos);
        return { ok: true };
    };
    FM.ingresar = function (dni, clave) {
        dni = String(dni || '').replace(/\D/g, '');
        var s = socios().filter(function (x) { return x.dni === dni && x.clave === hash(String(clave || '')); })[0];
        if (!s) return { ok: false, error: 'DNI o contraseña incorrectos.' };
        guardar('fm_sesion', s.id);
        return { ok: true };
    };

    function reservas() { return leer('fm_reservas', []); }
    FM.ocupados = function () {
        var o = {};
        reservas().forEach(function (r) { if (r.estado === 'confirmada') { var k = r.clase_id + '|' + r.fecha; o[k] = (o[k] || 0) + 1; } });
        return o;
    };
    FM.reservar = function (claseId, fecha) {
        var s = FM.sesion(), cl = clase(+claseId), hoy = FM.hoy();
        if (!s) return { ok: false, error: 'Iniciá sesión como socio para reservar.' };
        if (s.estado !== 'activo') return { ok: false, error: 'Tu cuenta de socio no está activa. Consultá en recepción.' };
        var f = /^\d{4}-\d{2}-\d{2}$/.test(fecha || '') ? new Date(fecha + 'T00:00:00') : null;
        var limite = new Date(); limite.setDate(limite.getDate() + 60);
        if (!cl || !f || isNaN(f) || fecha < hoy || fecha > ymd(limite)) return { ok: false, error: 'Elegí una clase y una fecha válida.' };
        if (((f.getDay() + 6) % 7) + 1 !== FM.DIAS[cl.dia]) return { ok: false, error: 'Esa clase se dicta los ' + cl.dia + '. Elegí una fecha que caiga ese día.' };
        var pm = plan(cl.plan_id), ps = plan(s.plan_id);
        if (pm && (!ps || ps.precio < pm.precio)) return { ok: false, error: 'Tu plan no incluye esta clase.' };
        var lista = reservas();
        if (lista.some(function (r) { return r.socio_id === s.id && r.clase_id === cl.id && r.fecha === fecha && r.estado === 'confirmada'; })) return { ok: false, error: 'Ya tenés una reserva para esa clase en esa fecha.' };
        if ((FM.ocupados()[cl.id + '|' + fecha] || 0) >= cl.cupo) return { ok: false, error: 'La clase está completa para esa fecha.' };
        var id = lista.reduce(function (m, r) { return Math.max(m, r.id); }, 0) + 1;
        lista.push({ id: id, socio_id: s.id, clase_id: cl.id, fecha: fecha, estado: 'confirmada' });
        guardar('fm_reservas', lista);
        return { ok: true };
    };
    FM.cancelar = function (id) {
        var s = FM.sesion(), lista = reservas(), hoy = FM.hoy(), hecho = false;
        lista.forEach(function (r) {
            if (s && r.id === id && r.socio_id === s.id && r.estado === 'confirmada' && r.fecha >= hoy) { r.estado = 'cancelada'; hecho = true; }
        });
        guardar('fm_reservas', lista);
        return hecho;
    };
    FM.misReservas = function () {
        var s = FM.sesion(); if (!s) return [];
        return reservas().filter(function (r) { return r.socio_id === s.id; })
            .sort(function (a, b) { return a.fecha < b.fecha ? 1 : -1; }).slice(0, 30);
    };
    FM.misPagos = function () {
        var s = FM.sesion(); if (!s) return [];
        return leer('fm_pagos', []).filter(function (p) { return p.socio_id === s.id; }).reverse().slice(0, 20);
    };

    /* ---------- Reemplazo de pedido.php, pregunta.php y api_planes.php ---------- */
    var fetchReal = window.fetch ? window.fetch.bind(window) : null;
    function resp(obj, code) {
        return Promise.resolve(new Response(JSON.stringify(obj), { status: code || 200, headers: { 'Content-Type': 'application/json' } }));
    }
    function preguntas() {
        var l = leer('fm_preguntas', null);
        if (!l) {
            l = [
                { nombre: 'Martín Gómez', mensaje: 'Excelente atención y muy buenas instalaciones.', fecha: '2026-11-07 18:30:00' },
                { nombre: 'Lucía Fernández', mensaje: '¿Tienen clases de yoga por la mañana?', fecha: '2026-11-08 10:15:00' }
            ];
            guardar('fm_preguntas', l);
        }
        return l.slice().reverse().slice(0, 50);
    }

    window.fetch = function (url, opts) {
        var u = String(url).split('?')[0].split('/').pop();
        var body = {};
        try { body = JSON.parse((opts && opts.body) || '{}'); } catch (e) { }

        if (u === 'api_planes.php') return resp({ ok: true, planes: FM.planes });

        if (u === 'pregunta.php') {
            if (!opts || !opts.method || opts.method === 'GET') return resp({ ok: true, preguntas: preguntas() });
            var nombre = String(body.nombre || '').trim().replace(/\s+/g, ' '), msg = String(body.mensaje || '').trim();
            var err = Validar.reglas.nombre(nombre) || Validar.reglas.pregunta(msg);
            if (err) return resp({ ok: false, error: err }, 400);
            var ult = +sessionStorage.getItem('fm_ult_preg') || 0;
            if (Date.now() - ult < 15000) return resp({ ok: false, error: 'Esperá unos segundos antes de publicar de nuevo.' }, 429);
            var l = leer('fm_preguntas', null) || (preguntas().reverse());
            l.push({ nombre: nombre, mensaje: msg, fecha: ahora() });
            guardar('fm_preguntas', l);
            try { sessionStorage.setItem('fm_ult_preg', String(Date.now())); } catch (e) { }
            return resp({ ok: true, preguntas: preguntas() });
        }

        if (u === 'pedido.php') {
            var e2 = Validar.reglas.nombre(String(body.nombre || '').trim()) || Validar.reglas.email(String(body.email || '')) || Validar.reglas.telefono(String(body.telefono || ''));
            if (e2) return resp({ ok: false, error: e2 }, 400);
            if (['tarjeta', 'efectivo'].indexOf(body.metodo) === -1) return resp({ ok: false, error: 'Elegí cómo vas a pagar: tarjeta o efectivo.' }, 400);
            if (body.metodo === 'tarjeta' && !/^\d{4}$/.test(String(body.ultimos4 || ''))) return resp({ ok: false, error: 'Número de tarjeta inválido.' }, 400);
            var items = body.items || [];
            if (!items.length) return resp({ ok: false, error: 'El carrito está vacío.' }, 400);
            var lineas = [], total = 0, malo = false;
            items.forEach(function (it) {
                var m = /^(plan|prod)-(\d+)$/.exec(String(it.id)), cant = parseInt(it.cant, 10);
                var fila = m ? (m[1] === 'plan' ? plan(+m[2]) : FM.productos.filter(function (p) { return p.id === +m[2]; })[0]) : null;
                if (!fila || !(cant >= 1 && cant <= 99)) { malo = true; return; }
                var sub = fila.precio * cant; total += sub;
                lineas.push({ nombre: (m[1] === 'plan' ? 'Plan ' : '') + fila.nombre, precio: fila.precio, cant: cant, subtotal: sub });
            });
            if (malo) return resp({ ok: false, error: 'Hay productos del carrito que ya no están disponibles. Vaciá el carrito y volvé a agregarlos.' }, 400);
            var ped = leer('fm_pedidos', []), s = FM.sesion();
            var pid = ped.reduce(function (m, p) { return Math.max(m, p.id); }, 0) + 1;
            ped.push({ id: pid, nombre: body.nombre, email: body.email, telefono: body.telefono, metodo: body.metodo, ultimos4: body.metodo === 'tarjeta' ? body.ultimos4 : null, total: total, socio_id: s ? s.id : null, fecha: ahora(), items: lineas, estado: 'nuevo' });
            guardar('fm_pedidos', ped);
            return resp({ ok: true, pedido: pid, total: total, metodo: body.metodo, items: lineas });
        }

        return fetchReal ? fetchReal(url, opts) : Promise.reject(new Error('fetch no disponible'));
    };
})();
