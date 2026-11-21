/* Validaciones del navegador (el servidor las vuelve a revisar).
   Uso: Validar.enlazar(input, 'nombre', elementoDeError)  y  Validar.revisar(input, 'nombre', elementoDeError) */
(function () {
    var LETRA = "A-Za-zÁÉÍÓÚÜÑáéíóúüñ";
    var RE_NOMBRE = new RegExp("^[" + LETRA + "]+(?:[ '’\\-][" + LETRA + "]+)*$");

    var V = {
        nombre: function (v) {
            v = v.trim().replace(/\s+/g, ' ');
            if (v.length < 2) return 'El nombre debe tener al menos 2 letras.';
            if (v.length > 50) return 'El nombre no puede superar los 50 caracteres.';
            if (!RE_NOMBRE.test(v)) return 'El nombre solo puede tener letras y espacios (sin números ni símbolos).';
            return '';
        },
        email: function (v) {
            v = v.trim();
            if (v.indexOf('@') === -1) return 'El email debe incluir @ (ej: nombre@gmail.com).';
            if (v.length > 100) return 'El email no puede superar los 100 caracteres.';
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v)) return 'Ingresá un email válido (ej: nombre@gmail.com).';
            return '';
        },
        telefono: function (v) {
            if (!/^\d+$/.test(v)) return 'El teléfono solo puede tener números.';
            if (v.length < 8 || v.length > 15) return 'El teléfono debe tener entre 8 y 15 dígitos.';
            return '';
        },
        tarjeta: function (v) {
            var d = v.replace(/\s/g, '');
            if (!/^\d+$/.test(d)) return 'El número de tarjeta solo puede tener números.';
            if (d.length !== 16) return 'El número de tarjeta debe tener 16 dígitos.';
            return '';
        },
        vencimiento: function (v) {
            var m = /^(\d{2})\/(\d{2})$/.exec(v);
            if (!m) return 'Usá el formato MM/AA (ej: 08/28).';
            var mes = +m[1], anio = 2000 + (+m[2]), hoy = new Date();
            if (mes < 1 || mes > 12) return 'El mes debe estar entre 01 y 12.';
            if (anio < hoy.getFullYear() || (anio === hoy.getFullYear() && mes < hoy.getMonth() + 1)) return 'La tarjeta está vencida.';
            return '';
        },
        cvv: function (v) {
            if (!/^\d{3,4}$/.test(v)) return 'El código debe tener 3 o 4 números.';
            return '';
        },
        pregunta: function (v) {
            v = v.trim();
            if (v.length < 5) return 'Escribí al menos 5 caracteres.';
            if (v.length > 300) return 'No puede superar los 300 caracteres.';
            return '';
        }
    };

    // Filtros mientras se escribe: sacan lo que no corresponde y avisan
    var F = {
        nombre: { quitar: new RegExp("[^" + LETRA + " '’\\-]", 'g'), max: 50, aviso: 'En el nombre solo se permiten letras.' },
        telefono: { quitar: /\D/g, max: 15, aviso: 'En el teléfono solo se permiten números.' },
        tarjeta: { quitar: /\D/g, max: 16, aviso: 'En la tarjeta solo se permiten números.', grupos: true },
        cvv: { quitar: /\D/g, max: 4, aviso: 'El código solo puede tener números.' },
        vencimiento: { quitar: /[^\d\/]/g, max: 5, aviso: 'Usá solo números (MM/AA).' }
    };

    function mostrar(el, msg) { if (el) { el.textContent = msg || ''; } }

    function enlazar(input, tipo, errEl) {
        var f = F[tipo];
        input.addEventListener('input', function () {
            var antes = input.value, v = antes;
            if (f) {
                v = v.replace(f.quitar, '');
                if (tipo === 'vencimiento') {
                    v = v.replace(/\//g, '');
                    if (v.length > 2) v = v.slice(0, 2) + '/' + v.slice(2, 4);
                } else {
                    v = v.slice(0, f.max);
                    if (f.grupos) v = v.replace(/(\d{4})(?=\d)/g, '$1 ');
                }
                var recortado = antes.replace(f.quitar, '').length > f.max;
                input.value = v;
                if (v !== antes && (antes.replace(f.quitar, '') !== antes || recortado)) {
                    mostrar(errEl, recortado ? 'Máximo ' + f.max + (tipo === 'vencimiento' ? ' caracteres.' : ' caracteres.') : f.aviso);
                    return;
                }
            }
            mostrar(errEl, '');
        });
        input.addEventListener('blur', function () { if (input.value !== '') mostrar(errEl, V[tipo](input.value)); });
    }

    function revisar(input, tipo, errEl) {
        var msg = V[tipo](input.value);
        mostrar(errEl, msg);
        return msg === '';
    }

    window.Validar = { reglas: V, enlazar: enlazar, revisar: revisar };
})();
