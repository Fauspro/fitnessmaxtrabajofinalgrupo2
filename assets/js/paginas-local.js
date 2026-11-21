/* Lógica de las páginas que antes eran PHP: productos, actividades, activar cuenta y mi cuenta. */
(function () {
    var pagina = document.body.getAttribute('data-page');
    function $(s) { return document.querySelector(s); }
    function el(tag, cls, txt) { var e = document.createElement(tag); if (cls) e.className = cls; if (txt !== undefined) e.textContent = txt; return e; }
    function aviso(cont, msg, ok) { cont.innerHTML = ''; if (msg) cont.appendChild(el('p', 'aviso ' + (ok ? 'ok' : 'error'), msg)); }
    function fechaCorta(f) { var p = f.split('-'); return p[2] + '/' + p[1] + '/' + p[0]; }

    /* ---------- PRODUCTOS ---------- */
    if (pagina === 'productos') {
        var gp = $('#grid-planes');
        FM.planes.forEach(function (pl, i) {
            var card = el('div', 'card' + (i === 1 ? ' active' : ''));
            if (i === 1) card.appendChild(el('span', 'popular', 'MÁS ELEGIDO'));
            card.appendChild(el('h2', '', pl.nombre.toUpperCase()));
            card.appendChild(el('h3', '', FM.dinero(pl.precio)));
            var ul = el('ul');
            pl.descripcion.split('·').forEach(function (t) { t = t.trim(); if (t) ul.appendChild(el('li', '', '✔ ' + t)); });
            card.appendChild(ul);
            var b = el('button', 'btn', 'Elegir Plan'); b.type = 'button';
            b.setAttribute('data-add', ''); b.setAttribute('data-id', 'plan-' + pl.id);
            b.setAttribute('data-nombre', 'Plan ' + pl.nombre); b.setAttribute('data-precio', pl.precio);
            card.appendChild(b); gp.appendChild(card);
        });
        var gs = $('#grid-supl');
        FM.productos.forEach(function (pr) {
            var d = el('div', 'supplement');
            var img = el('img'); img.src = 'assets/img/productos/' + pr.imagen; img.alt = pr.nombre;
            d.appendChild(img); d.appendChild(el('h3', '', pr.nombre)); d.appendChild(el('p', '', pr.descripcion));
            d.appendChild(el('span', '', FM.dinero(pr.precio)));
            var b = el('button', 'btn', 'Comprar'); b.type = 'button';
            b.setAttribute('data-add', ''); b.setAttribute('data-id', 'prod-' + pr.id);
            b.setAttribute('data-nombre', pr.nombre); b.setAttribute('data-precio', pr.precio);
            d.appendChild(b); gs.appendChild(d);
        });
    }

    /* ---------- ACTIVIDADES ---------- */
    if (pagina === 'actividades') {
        var DIAS_ORDEN = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
        var clases = FM.clases.slice().sort(function (a, b) { return DIAS_ORDEN.indexOf(a.dia) - DIAS_ORDEN.indexOf(b.dia) || (a.hora < b.hora ? -1 : 1); });
        var grid = $('#clases-grid');
        clases.forEach(function (c) {
            var pl = FM.plan(c.plan_id), card = el('article', 'clase-card');
            card.appendChild(el('h3', '', c.nombre));
            card.appendChild(el('p', '', '📅 ' + c.dia + ' · 🕐 ' + c.hora));
            card.appendChild(el('p', '', '👥 Cupo: ' + c.cupo + ' · 🎓 ' + c.instructor));
            card.appendChild(el('p', 'plan-tag', pl ? 'Desde plan ' + pl.nombre + ' (' + FM.dinero(pl.precio) + ')' : 'Todos los planes'));
            grid.appendChild(card);
        });

        var zona = $('#zona-reserva'), msgBox = $('#msg-reserva');
        if (!FM.sesion()) {
            zona.innerHTML = '<p>Para reservar necesitás ingresar con tu cuenta de socio.</p>' +
                '<p style="margin-top:14px"><a href="cuenta.html" class="btn btn-red" style="display:inline-block">INGRESAR</a> &nbsp; ' +
                '<a href="activar.html" style="color:#aaa">¿Primera vez? Activá tu cuenta</a></p>';
        } else {
            zona.innerHTML = '<form class="form-web" id="form-reserva">' +
                '<label>Clase<select id="sel-clase"></select></label>' +
                '<div class="cal-wrap"><div class="cal-title">Elegí el día en el calendario (se habilitan solo los días en que se dicta la clase)</div>' +
                '<div class="cal-grid" id="cal"></div><p class="cal-ayuda" id="cal-ayuda">Seleccioná una fecha.</p></div>' +
                '<button type="submit" class="btn-red" id="btn-reservar" disabled>RESERVAR</button></form>';
            var sel = $('#sel-clase'), cal = $('#cal'), ayuda = $('#cal-ayuda'), btn = $('#btn-reservar'), fechaSel = '';
            clases.forEach(function (c) { var o = el('option', '', c.nombre + ' - ' + c.dia + ' ' + c.hora); o.value = c.id; sel.appendChild(o); });
            var NOMBRES = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];
            function parse(s) { var p = s.split('-'); return new Date(+p[0], +p[1] - 1, +p[2]); }
            function dibujar() {
                var c = FM.clase(+sel.value), hoy = parse(FM.hoy()), oc = FM.ocupados();
                cal.innerHTML = ''; fechaSel = ''; btn.disabled = true; ayuda.textContent = 'Seleccioná una fecha.';
                NOMBRES.forEach(function (n) { cal.appendChild(el('div', 'cal-head', n)); });
                var inicio = new Date(hoy); inicio.setDate(inicio.getDate() - ((hoy.getDay() + 6) % 7));
                for (var i = 0; i < 35; i++) {
                    var d = new Date(inicio); d.setDate(inicio.getDate() + i);
                    var f = FM.ymd(d), nDia = ((d.getDay() + 6) % 7) + 1;
                    var b = el('button', 'cal-day'); b.type = 'button';
                    b.appendChild(document.createTextNode(String(d.getDate())));
                    var chico = el('small'); b.appendChild(chico);
                    if (d >= hoy && nDia === FM.DIAS[c.dia]) {
                        var libres = c.cupo - (oc[c.id + '|' + f] || 0);
                        if (libres > 0) {
                            b.classList.add('libre'); chico.textContent = libres + ' lug.';
                            (function (bd, ff, dd) {
                                bd.addEventListener('click', function () {
                                    Array.prototype.forEach.call(cal.querySelectorAll('.cal-day'), function (x) { x.classList.remove('sel'); });
                                    bd.classList.add('sel'); fechaSel = ff; btn.disabled = false;
                                    ayuda.textContent = 'Elegiste: ' + dd.toLocaleDateString('es-AR', { weekday: 'long', day: 'numeric', month: 'long' });
                                });
                            })(b, f, d);
                        } else { b.classList.add('completo'); chico.textContent = 'completo'; b.disabled = true; }
                    } else { b.disabled = true; }
                    cal.appendChild(b);
                }
            }
            sel.addEventListener('change', dibujar); dibujar();
            $('#form-reserva').addEventListener('submit', function (ev) {
                ev.preventDefault();
                var r = FM.reservar(sel.value, fechaSel);
                aviso(msgBox, r.ok ? '¡Reserva confirmada! La ves en Mi Cuenta.' : r.error, r.ok);
                dibujar();
            });
        }
    }

    /* ---------- ACTIVAR CUENTA ---------- */
    if (pagina === 'activar') {
        var selPlan = $('#a-plan');
        FM.planes.forEach(function (p) { var o = el('option', '', p.nombre + ' (' + FM.dinero(p.precio) + ')'); o.value = p.id; selPlan.appendChild(o); });
        $('#form-activar').addEventListener('submit', function (ev) {
            ev.preventDefault();
            var r = FM.activar({
                nombre: $('#a-nombre').value, dni: $('#a-dni').value, email: $('#a-email').value, plan: selPlan.value,
                clave: $('#a-clave').value, clave2: $('#a-clave2').value
            });
            if (r.ok) {
                $('#form-activar').style.display = 'none'; $('#a-extra').style.display = 'none';
                aviso($('#msg-activar'), '¡Cuenta activada! Ya podés ingresar con tu DNI y tu contraseña.', true);
                $('#a-ingresar').style.display = 'inline-block';
            } else { aviso($('#msg-activar'), r.error, false); }
        });
    }

    /* ---------- MI CUENTA ---------- */
    if (pagina === 'cuenta') {
        var s = FM.sesion();
        var accesoAdmin = location.hash === '#admin';
        if (!s || accesoAdmin) {
            $('#vista-login').style.display = 'block';
            if (accesoAdmin) { $('#login-sub').textContent = 'Acceso de administrador: ingresá tu usuario y tu contraseña.'; }
            $('#form-login').addEventListener('submit', function (ev) {
                ev.preventDefault();
                var usuario = $('#l-dni').value.trim(), clave = $('#l-clave').value;
                if (usuario.toLowerCase() === 'admin') {
                    // El administrador se valida en el servidor (PHP): se envía el mismo formulario a adminn/login.php
                    if (location.protocol === 'file:') {
                        aviso($('#msg-login'), 'El administrador necesita el servidor. Abrí el sitio desde http://localhost/gimnasio_fitness_max/cuenta.html (con XAMPP encendido).', false);
                        return;
                    }
                    var f = document.createElement('form'); f.method = 'POST'; f.action = 'adminn/login.php';
                    [['usuario', 'admin'], ['contraseña', clave]].forEach(function (par) {
                        var i = document.createElement('input'); i.type = 'hidden'; i.name = par[0]; i.value = par[1]; f.appendChild(i);
                    });
                    document.body.appendChild(f); f.submit();
                    return;
                }
                var r = FM.ingresar(usuario, clave);
                if (r.ok) { location.href = 'cuenta.html'; } else { aviso($('#msg-login'), r.error, false); }
            });
        } else {
            $('#vista-cuenta').style.display = 'block';
            $('#hola').textContent = 'HOLA, ' + s.nombre.split(' ')[0].toUpperCase();
            var pl = FM.plan(s.plan_id);
            $('#mi-plan').textContent = pl ? pl.nombre + ' (' + FM.dinero(pl.precio) + ')' : 'Sin plan asignado';
            $('#mi-dni').textContent = 'DNI: ' + s.dni;
            $('#cerrar-sesion').addEventListener('click', function (ev) { ev.preventDefault(); FM.cerrarSesion(); location.href = 'cuenta.html'; });

            var pagos = FM.misPagos(), pend = pagos.filter(function (p) { return p.estado === 'pendiente'; }).length;
            if (pend) $('#aviso-pagos').appendChild(el('p', 'aviso error', 'Tenés ' + pend + ' pago(s) pendiente(s). Acercate a recepción para regularizarlos.'));

            var dibujarRes = function (msg, ok) {
                aviso($('#msg-cuenta'), msg, ok);
                var res = FM.misReservas(), cont = $('#tabla-reservas'), hoy = FM.hoy();
                cont.innerHTML = '';
                if (!res.length) { cont.appendChild(el('p', '', 'Todavía no tenés reservas.')); return; }
                var wrap = el('div', 'tabla-wrap'), t = el('table', 'tabla-web');
                t.innerHTML = '<thead><tr><th>Clase</th><th>Día</th><th>Fecha</th><th>Estado</th><th></th></tr></thead>';
                var tb = el('tbody');
                res.forEach(function (r) {
                    var c = FM.clase(r.clase_id), tr = el('tr');
                    tr.appendChild(el('td', '', c.nombre + ' · ' + c.hora)); tr.appendChild(el('td', '', c.dia));
                    tr.appendChild(el('td', '', fechaCorta(r.fecha))); tr.appendChild(el('td', '', r.estado));
                    var td = el('td');
                    if (r.estado === 'confirmada' && r.fecha >= hoy) {
                        var b = el('button', 'btn-mini', 'Cancelar'); b.type = 'button';
                        b.addEventListener('click', function () {
                            if (!confirm('¿Cancelar esta reserva?')) return;
                            var ok2 = FM.cancelar(r.id);
                            dibujarRes(ok2 ? 'Reserva cancelada.' : 'No se pudo cancelar esa reserva.', ok2);
                        });
                        td.appendChild(b);
                    }
                    tr.appendChild(td); tb.appendChild(tr);
                });
                t.appendChild(tb); wrap.appendChild(t); cont.appendChild(wrap);
            };
            dibujarRes('', true);

            var cp = $('#tabla-pagos');
            if (!pagos.length) { cp.appendChild(el('p', '', 'No hay pagos registrados.')); }
            else {
                var w2 = el('div', 'tabla-wrap'), t2 = el('table', 'tabla-web');
                t2.innerHTML = '<thead><tr><th>Concepto</th><th>Monto</th><th>Fecha</th><th>Estado</th></tr></thead>';
                var tb2 = el('tbody');
                pagos.forEach(function (p) {
                    var tr = el('tr');
                    tr.appendChild(el('td', '', p.concepto)); tr.appendChild(el('td', '', FM.dinero(p.monto)));
                    tr.appendChild(el('td', '', fechaCorta(p.fecha)));
                    tr.appendChild(el('td', p.estado === 'pendiente' ? 'est-pend' : 'est-ok', p.estado));
                    tb2.appendChild(tr);
                });
                t2.appendChild(tb2); w2.appendChild(t2); cp.appendChild(w2);
            }
        }
    }
})();
