

export const KEYS = {
    cart: 'jaguar_cart',
    auth: 'jaguar_auth',   
};


export function esc(v) {
    return String(v ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

export function money(v) {
    return Number(v ?? 0).toFixed(2);
}

export function setMessage(el, text, type = 'success') {
    if (!el) return;
    el.textContent = text || '';
    el.className   = 'message ' + (text ? type : 'hidden');
    if (!text) el.classList.add('hidden');
}

export function clearMessage(el) {
    setMessage(el, '', '');
}

export function getAuthData() {
    try {
        const raw = localStorage.getItem(KEYS.auth);
        return raw ? JSON.parse(raw) : null;
    } catch { return null; }
}

export function getAuthUser() {
    const d = getAuthData();
    if (!d) return null;
    if (d.expires_at && new Date(d.expires_at) < new Date()) {
        localStorage.removeItem(KEYS.auth);
        return null;
    }
    return d;
}

export function getAuthToken() {
    return getAuthUser()?.token ?? null;
}

export function saveAuth(data) {
    localStorage.setItem(KEYS.auth, JSON.stringify(data));
    renderUserArea();
}

export function logout() {
    localStorage.removeItem(KEYS.auth);
    renderUserArea();
}

export function getCart() {
    try {
        const raw    = localStorage.getItem(KEYS.cart);
        const parsed = raw ? JSON.parse(raw) : [];
        return Array.isArray(parsed)
            ? parsed.filter(i => i && Number(i.id) > 0 && Number(i.quantity) > 0)
            : [];
    } catch { return []; }
}

export function saveCart(cart) {
    localStorage.setItem(KEYS.cart, JSON.stringify(cart));
    updateCartBadge();
}

export function getCartCount() {
    return getCart().reduce((s, i) => s + Number(i.quantity || 0), 0);
}

export function updateCartBadge() {
    const n = getCartCount();
    document.querySelectorAll('[data-cart-count]').forEach(el => {
        el.textContent = n > 0 ? `🛒 (${n})` : '🛒';
    });
}

export function addToCart(productId, qty, maxQty = null) {
    const id  = Number(productId);
    const q   = Number(qty);
    if (!Number.isInteger(id) || id <= 0 || q <= 0) {
        return { ok: false, message: 'Nieprawidłowa ilość.' };
    }
    const cart    = getCart();
    const existing = cart.find(i => Number(i.id) === id);
    const current  = existing ? Number(existing.quantity) : 0;
    const next     = current + q;

    if (maxQty !== null && next > Number(maxQty)) {
        return { ok: false, message: 'Nie można dodać więcej niż dostępny stan.' };
    }
    if (existing) existing.quantity = next;
    else cart.push({ id, quantity: q });

    saveCart(cart);
    return { ok: true };
}

export function setItemQuantity(productId, qty) {
    const id   = Number(productId);
    const q    = Number(qty);
    const cart = getCart()
        .map(i => Number(i.id) === id ? { ...i, quantity: q } : i)
        .filter(i => Number(i.quantity) > 0);
    saveCart(cart);
}

export function removeFromCart(productId) {
    saveCart(getCart().filter(i => Number(i.id) !== Number(productId)));
}

export function clearCart() {
    localStorage.removeItem(KEYS.cart);
    updateCartBadge();
}

export function qp(name) {
    return new URL(window.location.href).searchParams.get(name);
}

export async function loadCategories(selects, selected = 'all') {
    const { apiGet } = await import('./api.js');
    const data = await apiGet('/categories.php');
    const options = ['<option value="all">All</option>']
        .concat((data.categories || []).map(c => {
            const v  = esc(c.type);
            const sel = selected === c.type ? ' selected' : '';
            return `<option value="${v}"${sel}>${v}</option>`;
        }))
        .join('');
    selects.forEach(s => { if (s) s.innerHTML = options; });
    return data.categories || [];
}

export function renderUserArea() {
    const user = getAuthUser();
    document.querySelectorAll('[data-user-area]').forEach(container => {
        if (!container) return;
        if (user?.login) {
            container.innerHTML = `
                <span>Cześć, <strong>${esc(user.login)}</strong></span>
                <a href="my-orders.php" style="font-size:.85rem;text-decoration:underline;">Moje zamówienia</a>
                <button type="button" data-logout-btn>Wyloguj</button>
            `;
        } else {
            container.innerHTML = `<a href="login.php">Zaloguj się</a>`;
        }
    });

    document.querySelectorAll('[data-logout-btn]').forEach(btn => {
        btn.addEventListener('click', async () => {
            try {
                const { apiPost } = await import('./api.js');
                await apiPost('/logout.php', {});
            } catch {  }
            logout();
            if (/login|register/.test(window.location.pathname)) {
                window.location.href = 'index.php';
            }
        });
    });
}

export function initCommonUi() {
    updateCartBadge();
    renderUserArea();
}
