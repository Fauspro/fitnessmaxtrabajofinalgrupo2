/* ===== CARRITO FLOTANTE - FITNESS MAX ===== */

(function () {

    var CLAVE = 'fitnessmax_carrito';

    // Número de WhatsApp del gimnasio (con código de país, sin + ni espacios).
    // Ejemplo Argentina (Corrientes): '549379' + número sin el 0 ni el 15.
    // Es el mismo número de contacto que figura en Nosotros.
    var WHATSAPP = '5493775448912';

    var carrito = [];

    try {
        carrito = JSON.parse(localStorage.getItem(CLAVE)) || [];
    } catch (e) {
        carrito = [];
    }

    // Los productos ahora vienen de la base de datos (ids "plan-N" / "prod-N").
    // Se descartan los ítems guardados con el formato viejo (ej: "plan-basico").
    carrito = carrito.filter(function (i) { return /^(plan|prod)-\d+$/.test(String(i.id)); });

    function guardar() {
        try {
            localStorage.setItem(CLAVE, JSON.stringify(carrito));
        } catch (e) {}
    }

    function dinero(n) {
        return '$' + Number(n).toLocaleString('es-AR');
    }

    /* ---------- Crear botón y panel ---------- */

    var fab = document.createElement('button');
    fab.className = 'cart-fab';
    fab.setAttribute('aria-label', 'Abrir carrito');
    fab.innerHTML = '🛒<span class="cart-count">0</span>';

    var overlay = document.createElement('div');
    overlay.className = 'cart-overlay';

    var panel = document.createElement('aside');
    panel.className = 'cart-panel';
    panel.innerHTML =
        '<div class="cart-head">' +
            '<h3>TU CARRITO</h3>' +
            '<button class="cart-close" aria-label="Cerrar">✕</button>' +
        '</div>' +
        '<div class="cart-items"></div>' +
        '<div class="cart-foot">' +
            '<div class="cart-total"><span>Total</span><span class="cart-total-num">$0</span></div>' +
            '<button class="cart-checkout">Finalizar compra</button>' +
            '<button class="cart-clear">Vaciar carrito</button>' +
        '</div>';

    document.body.appendChild(fab);
    document.body.appendChild(overlay);
    document.body.appendChild(panel);

    var contador = fab.querySelector('.cart-count');
    var lista = panel.querySelector('.cart-items');
    var totalNum = panel.querySelector('.cart-total-num');

    /* ---------- Abrir / cerrar ---------- */

    function abrir() {
        panel.classList.add('open');
        overlay.classList.add('open');
    }

    function cerrar() {
        panel.classList.remove('open');
        overlay.classList.remove('open');
    }

    fab.addEventListener('click', abrir);
    overlay.addEventListener('click', cerrar);
    panel.querySelector('.cart-close').addEventListener('click', cerrar);

    document.addEventListener('keydown', function (ev) {
        if (ev.key === 'Escape') cerrar();
    });

    /* ---------- Dibujar el carrito ---------- */

    var dibujar = function () {

        lista.innerHTML = '';

        var total = 0;
        var cantidad = 0;

        if (carrito.length === 0) {
            var vacio = document.createElement('p');
            vacio.className = 'cart-empty';
            vacio.textContent = 'Tu carrito está vacío.';
            lista.appendChild(vacio);
        }

        carrito.forEach(function (item) {

            total += item.precio * item.cant;
            cantidad += item.cant;

            var fila = document.createElement('div');
            fila.className = 'cart-item';

            var info = document.createElement('div');
            info.className = 'cart-item-info';
            var nombre = document.createElement('strong');
            nombre.textContent = item.nombre;
            var precio = document.createElement('small');
            precio.textContent = dinero(item.precio) + ' c/u';
            info.appendChild(nombre);
            info.appendChild(precio);

            var qty = document.createElement('div');
            qty.className = 'cart-qty';
            var menos = document.createElement('button');
            menos.textContent = '−';
            menos.setAttribute('data-accion', 'menos');
            menos.setAttribute('data-id', item.id);
            var num = document.createElement('span');
            num.textContent = item.cant;
            var mas = document.createElement('button');
            mas.textContent = '+';
            mas.setAttribute('data-accion', 'mas');
            mas.setAttribute('data-id', item.id);
            qty.appendChild(menos);
            qty.appendChild(num);
            qty.appendChild(mas);

            var quitar = document.createElement('button');
            quitar.className = 'cart-remove';
            quitar.textContent = '🗑️';
            quitar.setAttribute('data-accion', 'quitar');
            quitar.setAttribute('data-id', item.id);

            fila.appendChild(info);
            fila.appendChild(qty);
            fila.appendChild(quitar);
            lista.appendChild(fila);
        });

        totalNum.textContent = dinero(total);
        contador.textContent = cantidad;
        contador.style.display = cantidad > 0 ? 'block' : 'none';

        guardar();
    };

    /* ---------- Acciones ---------- */

    function agregar(id, nombre, precio) {

        var existente = carrito.find(function (i) { return i.id === id; });

        if (existente) {
            existente.cant++;
        } else {
            carrito.push({ id: id, nombre: nombre, precio: precio, cant: 1 });
        }

        dibujar();

        fab.classList.remove('bump');
        void fab.offsetWidth;
        fab.classList.add('bump');
    }

    // Botones "Agregar" de la página (cualquier elemento con data-add)
    document.addEventListener('click', function (ev) {

        var btn = ev.target.closest('[data-add]');
        if (!btn) return;

        var precio = parseFloat(btn.getAttribute('data-precio'));
        if (isNaN(precio)) return;

        agregar(
            btn.getAttribute('data-id'),
            btn.getAttribute('data-nombre'),
            precio
        );
    });

    // Botones + / − / 🗑️ dentro del panel
    lista.addEventListener('click', function (ev) {

        var btn = ev.target.closest('[data-accion]');
        if (!btn) return;

        var id = btn.getAttribute('data-id');
        var accion = btn.getAttribute('data-accion');
        var item = carrito.find(function (i) { return i.id === id; });
        if (!item) return;

        if (accion === 'mas') item.cant++;
        if (accion === 'menos') item.cant--;

        if (accion === 'quitar' || item.cant <= 0) {
            carrito = carrito.filter(function (i) { return i.id !== id; });
        }

        dibujar();
    });

    panel.querySelector('.cart-clear').addEventListener('click', function () {
        carrito = [];
        dibujar();
    });

    /* ---------- Finalizar compra: único formulario del sitio (datos, forma de pago y registro del pedido) ---------- */

    var modal = document.createElement('div');
    modal.className = 'co-overlay';
    modal.innerHTML =
        '<div class="co-box">' +
            '<button class="co-close" type="button" aria-label="Cerrar">✕</button>' +
            '<form class="co-form" novalidate autocomplete="off">' +
                '<h3>FINALIZAR COMPRA</h3>' +
                '<p class="co-sub">Total a pagar: <strong class="co-total"></strong></p>' +
                '<label>Nombre y apellido<input class="co-nombre" maxlength="50" autocomplete="name"><small class="co-err" data-e="nombre"></small></label>' +
                '<label>Email (Gmail)<input class="co-email" type="email" maxlength="100" autocomplete="email" placeholder="nombre@gmail.com"><small class="co-err" data-e="email"></small></label>' +
                '<label>Teléfono<input class="co-tel" maxlength="15" inputmode="numeric" autocomplete="tel" placeholder="Solo números"><small class="co-err" data-e="tel"></small></label>' +
                '<div class="co-pago">' +
                    '<span class="co-pago-t">Forma de pago</span>' +
                    '<label class="co-radio"><input type="radio" name="metodo" value="tarjeta"> 💳 Tarjeta</label>' +
                    '<label class="co-radio"><input type="radio" name="metodo" value="efectivo"> 💵 Efectivo</label>' +
                    '<small class="co-err" data-e="metodo"></small>' +
                '</div>' +
                '<div class="co-tarjeta" style="display:none">' +
                    '<label>Número de tarjeta<input class="co-num" maxlength="19" inputmode="numeric" placeholder="0000 0000 0000 0000"><small class="co-err" data-e="num"></small></label>' +
                    '<div class="co-fila">' +
                        '<label>Vencimiento<input class="co-venc" maxlength="5" inputmode="numeric" placeholder="MM/AA"><small class="co-err" data-e="venc"></small></label>' +
                        '<label>Código (CVV)<input class="co-cvv" maxlength="4" inputmode="numeric" placeholder="123"><small class="co-err" data-e="cvv"></small></label>' +
                    '</div>' +
                '</div>' +
                '<p class="co-efectivo" style="display:none">Pagás en efectivo al retirar tu pedido en el gimnasio.</p>' +
                '<p class="co-error"></p>' +
                '<button class="co-send" type="submit">Confirmar pedido</button>' +
            '</form>' +
            '<div class="co-ok" style="display:none">' +
                '<h3>¡PEDIDO REGISTRADO!</h3>' +
                '<p class="co-ok-txt"></p>' +
                '<a class="co-wa" target="_blank" rel="noopener">Enviar por WhatsApp</a>' +
                '<button class="co-fin" type="button">Cerrar</button>' +
            '</div>' +
        '</div>';
    document.body.appendChild(modal);

    var $ = function (sel) { return modal.querySelector(sel); };
    var err = function (k) { return modal.querySelector('[data-e="' + k + '"]'); };
    var coForm = $('.co-form'), coOk = $('.co-ok'), coError = $('.co-error'), coSend = $('.co-send');
    var fNombre = $('.co-nombre'), fEmail = $('.co-email'), fTel = $('.co-tel');
    var fNum = $('.co-num'), fVenc = $('.co-venc'), fCvv = $('.co-cvv');
    var bloqueTarjeta = $('.co-tarjeta'), notaEfectivo = $('.co-efectivo');

    function metodoElegido() {
        var r = modal.querySelector('input[name="metodo"]:checked');
        return r ? r.value : '';
    }

    // Filtros y mensajes de error mientras se escribe
    if (window.Validar) {
        Validar.enlazar(fNombre, 'nombre', err('nombre'));
        Validar.enlazar(fEmail, 'email', err('email'));
        Validar.enlazar(fTel, 'telefono', err('tel'));
        Validar.enlazar(fNum, 'tarjeta', err('num'));
        Validar.enlazar(fVenc, 'vencimiento', err('venc'));
        Validar.enlazar(fCvv, 'cvv', err('cvv'));
    }

    Array.prototype.forEach.call(modal.querySelectorAll('input[name="metodo"]'), function (r) {
        r.addEventListener('change', function () {
            var m = metodoElegido();
            bloqueTarjeta.style.display = (m === 'tarjeta') ? 'block' : 'none';
            notaEfectivo.style.display = (m === 'efectivo') ? 'block' : 'none';
            err('metodo').textContent = '';
        });
    });

    function abrirCheckout() {
        coForm.reset();
        coForm.style.display = 'block';
        coOk.style.display = 'none';
        bloqueTarjeta.style.display = 'none';
        notaEfectivo.style.display = 'none';
        coError.textContent = '';
        Array.prototype.forEach.call(modal.querySelectorAll('.co-err'), function (e) { e.textContent = ''; });
        var total = carrito.reduce(function (a, i) { return a + i.precio * i.cant; }, 0);
        $('.co-total').textContent = dinero(total);
        modal.classList.add('open');
        fNombre.focus();
    }

    function cerrarCheckout() { modal.classList.remove('open'); }

    $('.co-close').addEventListener('click', cerrarCheckout);
    $('.co-fin').addEventListener('click', cerrarCheckout);
    modal.addEventListener('click', function (ev) { if (ev.target === modal) cerrarCheckout(); });

    panel.querySelector('.cart-checkout').addEventListener('click', function () {
        if (carrito.length === 0) { alert('Tu carrito está vacío.'); return; }
        cerrar();
        abrirCheckout();
    });

    coForm.addEventListener('submit', function (ev) {
        ev.preventDefault();
        if (!window.Validar) { coError.textContent = 'No se cargaron las validaciones. Recargá la página.'; return; }

        var ok = true;
        ok = Validar.revisar(fNombre, 'nombre', err('nombre')) && ok;
        ok = Validar.revisar(fEmail, 'email', err('email')) && ok;
        ok = Validar.revisar(fTel, 'telefono', err('tel')) && ok;

        var metodo = metodoElegido();
        if (!metodo) { err('metodo').textContent = 'Elegí cómo vas a pagar: tarjeta o efectivo.'; ok = false; }
        if (metodo === 'tarjeta') {
            ok = Validar.revisar(fNum, 'tarjeta', err('num')) && ok;
            ok = Validar.revisar(fVenc, 'vencimiento', err('venc')) && ok;
            ok = Validar.revisar(fCvv, 'cvv', err('cvv')) && ok;
        }
        if (!ok) { coError.textContent = 'Revisá los datos marcados en rojo.'; return; }

        coError.textContent = '';
        coSend.disabled = true;
        coSend.textContent = 'Enviando...';

        var nombre = fNombre.value.trim().replace(/\s+/g, ' ');
        var tel = fTel.value.trim();

        // El número de tarjeta no se envía: al servidor solo viajan los últimos 4 dígitos
        fetch('pedido.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                nombre: nombre,
                email: fEmail.value.trim(),
                telefono: tel,
                metodo: metodo,
                ultimos4: metodo === 'tarjeta' ? fNum.value.replace(/\s/g, '').slice(-4) : '',
                items: carrito.map(function (i) { return { id: i.id, cant: i.cant }; })
            })
        })
        .then(function (r) { return r.json(); })
        .then(function (res) {

            if (!res.ok) { coError.textContent = res.error || 'No se pudo registrar el pedido.'; return; }

            var texto = 'Hola! Soy ' + nombre + '. Hice el pedido #' + res.pedido + ' en Fitness Max (pago: ' + (res.metodo === 'tarjeta' ? 'tarjeta' : 'efectivo') + '):\n';
            res.items.forEach(function (i) {
                texto += '- ' + i.cant + ' x ' + i.nombre + ' (' + dinero(i.subtotal) + ')\n';
            });
            texto += 'Total: ' + dinero(res.total);

            $('.co-ok-txt').textContent =
                'Tu pedido #' + res.pedido + ' por ' + dinero(res.total) + ' quedó registrado (pago en ' + (res.metodo === 'tarjeta' ? 'tarjeta' : 'efectivo') + '). Nos vamos a comunicar al ' + tel + '.';

            var wa = $('.co-wa');
            if (WHATSAPP) {
                wa.href = 'https://wa.me/' + WHATSAPP + '?text=' + encodeURIComponent(texto);
                wa.style.display = 'block';
            } else {
                wa.style.display = 'none';
            }

            carrito = [];
            guardar();
            dibujar();
            coForm.style.display = 'none';
            coOk.style.display = 'block';
        })
        .catch(function () { coError.textContent = 'No hay conexión con el servidor. Probá de nuevo.'; })
        .then(function () { coSend.disabled = false; coSend.textContent = 'Confirmar pedido'; });
    });

    /* ---------- Aparición de tarjetas al hacer scroll ---------- */

    var tarjetas = document.querySelectorAll('.cat, .plan, .service');

    if ('IntersectionObserver' in window) {
        var obs = new IntersectionObserver(function (entradas) {
            entradas.forEach(function (e) {
                if (e.isIntersecting) {
                    e.target.classList.add('visible');
                    obs.unobserve(e.target);
                }
            });
        }, { threshold: 0.15 });
        tarjetas.forEach(function (t) { obs.observe(t); });
    } else {
        tarjetas.forEach(function (t) { t.classList.add('visible'); });
    }

    /* ---------- Menú del celular (botón ☰) ---------- */

    var menuBtn = document.querySelector('.menu-btn');
    var nav = document.querySelector('.nav');

    if (menuBtn && nav) {
        menuBtn.addEventListener('click', function () {
            nav.classList.toggle('open');
        });
    }


    /* ---------- Botón subir ---------- */
    var subir = document.querySelector('.scroll-top');
    if (subir) subir.addEventListener('click', function () { window.scrollTo({ top: 0, behavior: 'smooth' }); });

    /* ---------- Página carrito.html ---------- */
    var pag = document.getElementById('carrito-items');
    if (pag) {
        var pagTotal = document.getElementById('carrito-total');
        var base = dibujar;
        dibujar = function () { base(); pag.innerHTML = lista.innerHTML; pagTotal.textContent = totalNum.textContent; };
        pag.addEventListener('click', function (ev) {
            var b = ev.target.closest('[data-accion]'); if (!b) return;
            var o = lista.querySelector('[data-accion="' + b.getAttribute('data-accion') + '"][data-id="' + b.getAttribute('data-id') + '"]');
            if (o) o.click();
        });
        document.getElementById('carrito-finalizar').addEventListener('click', function () { panel.querySelector('.cart-checkout').click(); });
        document.getElementById('carrito-vaciar').addEventListener('click', function () { panel.querySelector('.cart-clear').click(); });
        fab.style.display = 'none';
    }

    dibujar();

})();