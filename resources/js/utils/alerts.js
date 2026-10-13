import Swal from 'sweetalert2';
import './alerts.css';

const fireAlert = (options, { closePrevious = true } = {}) => {
    if (closePrevious && Swal.isVisible()) {
        Swal.close();
    }

    return Swal.fire(options);
};

/**
 * Alerta personalizada básica
 */
export const customAlert = (title, text, icon) => {
    return fireAlert({
        title,
        text,
        icon,
        confirmButtonColor: "#1B396A",
        confirmButtonText: 'Aceptar',
    });
};

/**
 * Alerta de éxito
 */
export const alertaExito = (titulo, mensaje) => {
    return fireAlert({
        icon: 'success',
        title: titulo,
        text: mensaje,
        iconColor: '#10B981',
        showConfirmButton: false,
        timer: 1500,
        customClass: {
            popup: 'custom-alert-success',
        }
    });
};

/**
 * Alerta de error
 */
export const alertaError = (titulo, mensaje) => {
    return fireAlert({
        icon: 'error',
        title: titulo,
        text: mensaje,
        iconColor: '#DC2626',
        showConfirmButton: false,
        timer: 2000,
        customClass: {
            popup: 'custom-alert-error',
        }
    });
};

/**
 * Alerta de advertencia
 */
export const alertaAdvertencia = (titulo, mensaje) => {
    return fireAlert({
        icon: 'warning',
        title: titulo,
        text: mensaje,
        iconColor: '#F59E0B',
        showConfirmButton: false,
        timer: 2000,
        customClass: {
            popup: 'custom-alert-warning',
        }
    });
};

/**
 * Alerta de información
 */
export const alertaInfo = (titulo, mensaje) => {
    return fireAlert({
        icon: 'info',
        title: titulo,
        text: mensaje,
        iconColor: '#1B396A',
        confirmButtonColor: '#1B396A',
        confirmButtonText: 'Aceptar',
    });
};

/**
 * Alerta de cargando
 */
export const alertaCargando = (titulo, mensaje) => {
    return fireAlert({
        html: `
            <div class="flex flex-col items-center">
                <div class="loader spinner-border mt-5 mb-5"></div>
                <h2 class="swal2-title custom-alert-title mb-4">${titulo}</h2>
                <p class="mt-4">${mensaje}</p>
            </div>
        `,
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        customClass: {
            popup: 'custom-alert-loading',
        }
    });
};

/**
 * Cerrar alerta de cargando
 */
export const cerrarAlerta = () => {
    Swal.close();
};

/**
 * Alerta de confirmación/pregunta
 */
export const alertaPregunta = (titulo, mensaje) => {
    return fireAlert({
        icon: 'question',
        title: titulo,
        text: mensaje,
        iconColor: '#1B396A',
        showCancelButton: true,
        confirmButtonText: 'Confirmar',
        cancelButtonText: 'Cancelar',
        buttonsStyling: false,
        reverseButtons: true,
        customClass: {
            popup: 'custom-alert-pregunta',
            confirmButton: 'custom-blue-bottom hover:bg-blue-900 text-white px-4 py-2 rounded ml-2 cursor-pointer',
            cancelButton: 'bg-gray-100 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded mr-2 cursor-pointer',
        }
    }).then((result) => {
        return result.isConfirmed;
    });
};

/**
 * Alerta de confirmación con input escrito
 * El usuario debe escribir exactamente `palabraClave` para continuar.
 * Retorna true si confirmó, false si canceló o escribió mal.
 */
export const alertaConfirmacionEscrita = (titulo, mensaje, palabraClave = 'CONFIRMAR') => {
    return fireAlert({
        title: titulo,
        html: `
            <p class="text-sm text-gray-600 mb-4">${mensaje}</p>
            <p class="text-sm text-gray-500">Escribe <strong style="color:#1B396A">${palabraClave}</strong> para continuar:</p>
        `,
        input: 'text',
        inputPlaceholder: `Escribe ${palabraClave}`,
        inputAttributes: { style: 'text-align: center;' },
        icon: 'warning',
        iconColor: '#1B396A',
        width: '480px',
        showCancelButton: true,
        confirmButtonText: 'Continuar',
        cancelButtonText: 'Cancelar',
        buttonsStyling: false,
        reverseButtons: true,
        customClass: {
            popup: 'custom-alert-confirmacion',
            confirmButton: 'custom-blue-bottom hover:bg-blue-900 text-white px-4 py-2 rounded ml-2 cursor-pointer',
            cancelButton: 'bg-gray-100 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded mr-2 cursor-pointer',
            input: 'swal-custom-input',
            validationMessage: 'swal-custom-validation',
        },
        inputValidator: (value) => {
            if (!value || value.trim() !== palabraClave) {
                return `Debes escribir exactamente "${palabraClave}" en mayúsculas para continuar.`;
            }
        }
    }).then((result) => {
        return result.isConfirmed;
    });
};
