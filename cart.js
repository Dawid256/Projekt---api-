import { apiGet, apiPost } from './api.js';
import {
    esc, money, getCart, getAuthUser, clearCart,
    setItemQuantity, removeFromCart,
    initCommonUi, loadCategories, setMessage,
} from './common.js';

const itemsBox   = document.querySelector('#cart-items');
const totalBox   = document.querySelector('#cart-total');
const messageBox = document.querySelector('#page-message');
const form       = document.querySelector('#checkout-form');
const typeSelect = document.querySelector('#type-filter');

function renderCart(products) {
    const cart       = getCart();
    const productMap = new Map(products.map(p => [Number(p.id), p]));
    let total        = 0;

    const html = cart.map(entry => {
        const p = productMap.get(Number(entry.id));
        if (!p) return '';

        const qty = Math.min(Number(entry.quantity), Number(p.quantity));
        if (qty !== Number(entry.quantity)) setItemQuantity(p.id, qty);

        const subtotal = qty * Number(p.price);
        total += subtotal;

        return `
            <div class="product">
                <img src="${esc(p.image_url || 'img/logo-black.png')}" alt="${esc(p.name)}">
                <div class="product-info">
                    <h1>${esc(p.name)}</h1>
                    <h3>$${money(subtotal)}</h3>
                    <div class="cart-controls">
                        <span>Ilość: ${qty}</span>
                        <button data-action="plus"   data-id="${Number(p.id)}">+1</button>
                        <button data-action="minus"  data-id="${Number(p.id)}">-1</button>
                        <button data-action="delete" data-id="${Number(p.id)}" class="danger">Usuń</button>
                    </div>
                </div>
            </div>`;
    }).join('');

    itemsBox.innerHTML = html || '<div class="empty-state"><h2>Koszyk jest pusty</h2></div>';
    totalBox.textContent = `$${money(total)}`;

    itemsBox.querySelectorAll('[data-action]').forEach(btn => {
        btn.addEventListener('click', () => {
            const id      = Number(btn.dataset.id);
            const action  = btn.dataset.action;
            const current = getCart().find(i => Number(i.id) === id);
            const product = productMap.get(id);
            if (!current || !product) return;

            if (action === 'plus')   setItemQuantity(id, Math.min(Number(current.quantity) + 1, Number(product.quantity)));
            else if (action === 'minus')  setItemQuantity(id, Number(current.quantity) - 1);
            else if (action === 'delete') removeFromCart(id);

            loadCart();
        });
    });
}

async function loadCart() {
    const cart = getCart();
    if (!cart.length) {
        itemsBox.innerHTML = '<div class="empty-state"><h2>Koszyk jest pusty</h2></div>';
        totalBox.textContent = '$0.00';
        return;
    }
    const ids = cart.map(i => i.id).join(',');
    try {
        const data = await apiGet('/products.php', { ids });
        renderCart(data.products || []);
    } catch (err) {
        setMessage(messageBox, err.message, 'error');
    }
}

async function init() {
    initCommonUi();
    await loadCategories([typeSelect]);
    await loadCart();

    form.addEventListener('submit', async e => {
        e.preventDefault();
        const cart = getCart();
        if (!cart.length) { setMessage(messageBox, 'Koszyk jest pusty.', 'error'); return; }

        const user = getAuthUser();
        if (!user) {
            setMessage(messageBox, 'Musisz być zalogowany, aby złożyć zamówienie.', 'error');
            setTimeout(() => { window.location.href = 'login.php'; }, 1200);
            return;
        }

        const payload = {
            first_name:  form.first_name.value.trim(),
            last_name:   form.last_name.value.trim(),
            address:     form.address.value.trim(),
            city:        form.city.value.trim(),
            postal_code: form.postal_code.value.trim(),
            items:       cart,
        };

        try {
            const data = await apiPost('/order.php', payload);
            clearCart();
            form.reset();
            await loadCart();
            setMessage(messageBox, `✅ Zamówienie złożone! Numer: #${data.order_id}`, 'success');
        } catch (err) {
            setMessage(messageBox, err.message, 'error');
        }
    });
}

init();
