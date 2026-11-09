const BASE = '/api/v1';

export class ApiError extends Error {
    constructor(status, body) {
        super(body?.message ?? 'Terjadi kesalahan pada server.');
        this.status = status;
        this.errors = body?.errors ?? {};
    }
}

function bentukQuery(params = {}) {
    const query = new URLSearchParams();

    Object.entries(params).forEach(([key, value]) => {
        if (value !== undefined && value !== null && value !== '') {
            query.set(key, value);
        }
    });

    const teks = query.toString();

    return teks ? `?${teks}` : '';
}

async function kirim(method, path, { params, body } = {}) {
    const response = await fetch(`${BASE}${path}${bentukQuery(params)}`, {
        method,
        headers: {
            Accept: 'application/json',
            ...(body ? { 'Content-Type': 'application/json' } : {}),
        },
        body: body ? JSON.stringify(body) : undefined,
    });

    if (response.status === 204) {
        return null;
    }

    const json = await response.json().catch(() => null);

    if (!response.ok) {
        throw new ApiError(response.status, json);
    }

    return json;
}

export const api = {
    get: (path, params) => kirim('GET', path, { params }),
    post: (path, body) => kirim('POST', path, { body }),
    put: (path, body) => kirim('PUT', path, { body }),
    delete: (path) => kirim('DELETE', path),
};
