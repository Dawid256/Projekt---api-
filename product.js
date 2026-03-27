import { apiGet } from './api.js';
import { esc, money, qp, addToCart, initCommonUi, loadCategories, setMessage } from './common.js';

const productBox = document.querySelector('#product-box');
const messageBox = document.querySelector('#page-message');
const typeSelect = document.querySelector('#type-filter');
const loadingBox = document.querySelector('#loading');

function renderProduct(p) {
    document.title = p.name + ' — Jaguar';
    productBox.innerHTML = `
        <article>
            <section>
                <h1>${esc(p.name)}</h1>
                <h4>${esc(p.description)}</h4>
                <h4>Cena: <strong>$${money(p.price)}</strong></h4>
                <h4>Dostępne: ${Number(p.quantity)}</h4>
                <div class="product-actions">
                    <label for="qty-input">Ilość:</label>
                    <input type="number" id="qty-input" min="1" max="${Number(p.quantity)}" value="1">
                    <button type="button" class="primary" id="add-btn">Dodaj do koszyka</button>
                </div>
            </section>
            <aside>
                <img src="${esc(p.image_url || 'img/logo-black.png')}" alt="${esc(p.name)}">
            </aside>
        </article>`;

    document.querySelector('#add-btn').addEventListener('click', () => {
        const qty    = Number(document.querySelector('#qty-input').value || 1);
        const result = addToCart(p.id, qty, Number(p.quantity));
        if (!result.ok) { setMessage(messageBox, result.message, 'error'); return; }
        setMessage(messageBox, 'Produkt dodany do koszyka.', 'success');
    });
}

async function init() {
    initCommonUi();
    await loadCategories([typeSelect]);

    const id = Number(qp('id'));
    if (!id) { window.location.href = 'index.php'; return; }

    loadingBox.classList.remove('hidden');
    try {
        const data = await apiGet('/product.php', { id });
        renderProduct(data.product);
    } catch (err) {
        setMessage(messageBox, err.message, 'error');
        productBox.innerHTML = '<div class="empty-state"><h2>Nie znaleziono produktu.</h2></div>';
    } finally {
        loadingBox.classList.add('hidden');
    }
}

init();
