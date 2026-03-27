

import { getAuthToken } from './common.js';

function buildUrl(path, params = null) {
    const base = (window.API_BASE || '').replace(/\/$/, '');
    const url  = new URL(`${base}${path}`);
    if (params) {
        Object.entries(params).forEach(([k, v]) => {
            if (v === undefined || v === null || v === '') return;
            url.searchParams.set(k, v);
        });
    }
    return url.toString();
}

function authHeaders(extra = {}) {
    const token = getAuthToken();
    const headers = { ...extra };
    if (token) headers['Authorization'] = `Bearer ${token}`;
    return headers;
}

async function parseResponse(res) {
    const data = await res.json().catch(() => ({ message: 'Nieprawidłowa odpowiedź serwera.' }));
    if (!res.ok || data.success === false) {
        throw new Error(data.message || `Błąd HTTP ${res.status}`);
    }
    return data;
}

export async function apiGet(path, params = null) {
    const res = await fetch(buildUrl(path, params), {
        headers: authHeaders(),
    });
    return parseResponse(res);
}

export async function apiPost(path, payload) {
    const res = await fetch(buildUrl(path), {
        method:  'POST',
        headers: authHeaders({ 'Content-Type': 'application/json' }),
        body:    JSON.stringify(payload),
    });
    return parseResponse(res);
}

export async function apiPut(path, payload) {
    const res = await fetch(buildUrl(path), {
        method:  'PUT',
        headers: authHeaders({ 'Content-Type': 'application/json' }),
        body:    JSON.stringify(payload),
    });
    return parseResponse(res);
}

export async function apiDelete(path, payload = null) {
    const res = await fetch(buildUrl(path), {
        method:  'DELETE',
        headers: authHeaders({ 'Content-Type': 'application/json' }),
        body:    payload ? JSON.stringify(payload) : undefined,
    });
    return parseResponse(res);
}

export async function apiUpload(path, formData) {

    const res = await fetch(buildUrl(path), {
        method:  'POST',
        headers: authHeaders(),
        body:    formData,
    });
    return parseResponse(res);
}
