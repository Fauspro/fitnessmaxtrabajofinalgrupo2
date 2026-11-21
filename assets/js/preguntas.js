/* Caja de preguntas y opiniones de la página Nosotros */
(function () {
    var form = document.getElementById('preg-form');
    if (!form) return;

    var nombre = document.getElementById('preg-nombre');
    var mensaje = document.getElementById('preg-mensaje');
    var cuenta = document.getElementById('preg-cuenta');
    var estado = document.getElementById('preg-estado');
    var enviar = document.getElementById('preg-enviar');
    var lista = document.getElementById('preg-lista');
    var errNombre = document.getElementById('err-nombre');
    var errMensaje = document.getElementById('err-mensaje');

    Validar.enlazar(nombre, 'nombre', errNombre);

    mensaje.addEventListener('input', function () {
        cuenta.textContent = mensaje.value.length;
        errMensaje.textContent = '';
    });
    mensaje.addEventListener('blur', function () {
        if (mensaje.value !== '') errMensaje.textContent = Validar.reglas.pregunta(mensaje.value);
    });

    function fecha(f) {
        var d = new Date(String(f).replace(' ', 'T'));
        return isNaN(d) ? '' : d.toLocaleDateString('es-AR', { day: 'numeric', month: 'long', year: 'numeric' });
    }

    // textContent evita que alguien publique código HTML en la página
    function dibujar(preguntas) {
        lista.innerHTML = '';
        if (!preguntas.length) {
            var vacio = document.createElement('p');
            vacio.className = 'preg-vacio';
            vacio.textContent = 'Todavía no hay mensajes. ¡Sé el primero en escribir!';
            lista.appendChild(vacio);
            return;
        }
        preguntas.forEach(function (p) {
            var card = document.createElement('article');
            card.className = 'preg-card';
            var cab = document.createElement('div');
            cab.className = 'preg-cab';
            var n = document.createElement('strong'); n.textContent = p.nombre;
            var f = document.createElement('span'); f.textContent = fecha(p.fecha);
            cab.appendChild(n); cab.appendChild(f);
            var t = document.createElement('p'); t.textContent = p.mensaje;
            card.appendChild(cab); card.appendChild(t);
            lista.appendChild(card);
        });
    }

    function cargar() {
        fetch('pregunta.php').then(function (r) { return r.json(); })
            .then(function (res) { dibujar(res.ok ? res.preguntas : []); })
            .catch(function () {
                lista.innerHTML = '';
                var e = document.createElement('p');
                e.className = 'preg-vacio';
                e.textContent = 'No se pudieron cargar los mensajes .';
                lista.appendChild(e);
            });
    }

    form.addEventListener('submit', function (ev) {
        ev.preventDefault();
        estado.textContent = ''; estado.className = 'preg-estado';

        var ok = Validar.revisar(nombre, 'nombre', errNombre);
        var msg = Validar.reglas.pregunta(mensaje.value);
        errMensaje.textContent = msg;
        if (msg) ok = false;
        if (!ok) { estado.textContent = 'Revisá los datos marcados en rojo.'; estado.className = 'preg-estado mal'; return; }

        enviar.disabled = true; enviar.textContent = 'PUBLICANDO...';
        fetch('pregunta.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ nombre: nombre.value.trim().replace(/\s+/g, ' '), mensaje: mensaje.value.trim() })
        })
        .then(function (r) { return r.json(); })
        .then(function (res) {
            if (!res.ok) { estado.textContent = res.error || 'No se pudo publicar.'; estado.className = 'preg-estado mal'; return; }
            dibujar(res.preguntas);
            form.reset(); cuenta.textContent = '0';
            estado.textContent = '¡Gracias! Tu mensaje ya está publicado.'; estado.className = 'preg-estado bien';
        })
        .catch(function () { estado.textContent = 'No hay conexión con el servidor. Probá de nuevo.'; estado.className = 'preg-estado mal'; })
        .then(function () { enviar.disabled = false; enviar.textContent = 'PUBLICAR'; });
    });

    cargar();
})();
