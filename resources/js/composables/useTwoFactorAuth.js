import { ref } from 'vue';

const qrCodeSvg = ref('');
const manualSetupKey = ref('');
const recoveryCodesList = ref([]);
const errors = ref([]);

const csrf = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

const request = async (url, options = {}) => {
    const response = await fetch(url, {
        credentials: 'same-origin',
        headers: {
            'X-CSRF-TOKEN': csrf(),
            'X-Requested-With': 'XMLHttpRequest',
            Accept: 'application/json, text/plain, */*',
            ...(options.headers || {}),
        },
        ...options,
    });

    if (!response.ok) {
        const text = await response.text();
        throw new Error(text || `HTTP ${response.status}`);
    }

    const contentType = response.headers.get('content-type') || '';
    if (contentType.includes('application/json')) {
        return response.json();
    }

    return response.text();
};

const normalizeErrors = (err) => {
    if (!err) return ['Unexpected error'];
    if (Array.isArray(err)) return err;

    const message = err?.message || String(err);
    return [message];
};

export const useTwoFactorAuth = () => {
    const clearSetupData = () => {
        qrCodeSvg.value = '';
        manualSetupKey.value = '';
        errors.value = [];
    };

    const fetchSetupData = async () => {
        errors.value = [];

        try {
            const [qrResult, secretResult] = await Promise.all([
                request('/user/two-factor-qr-code'),
                request('/user/two-factor-secret-key'),
            ]);

            qrCodeSvg.value = typeof qrResult === 'string' ? qrResult : (qrResult?.svg || '');
            manualSetupKey.value = typeof secretResult === 'string'
                ? secretResult
                : (secretResult?.secretKey || secretResult?.secret || '');
        } catch (err) {
            errors.value = normalizeErrors(err);
        }
    };

    const fetchRecoveryCodes = async () => {
        errors.value = [];

        try {
            const data = await request('/user/two-factor-recovery-codes');
            recoveryCodesList.value = Array.isArray(data)
                ? data
                : (data?.recovery_codes || data?.codes || []);
        } catch (err) {
            errors.value = normalizeErrors(err);
        }
    };

    return {
        qrCodeSvg,
        manualSetupKey,
        recoveryCodesList,
        errors,
        clearSetupData,
        fetchSetupData,
        fetchRecoveryCodes,
    };
};
