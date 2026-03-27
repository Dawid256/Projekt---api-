import { apiGet } from './api.js';
import { esc, money, initCommonUi, loadCategories, setMessage, qp } from './common.js';

const form        = document.querySelector('#filter-form');
const typeSelect  = document.querySelector('#type-filter');
const searchInput = document.querySelector('#search-filter');
const productsBox = document.querySelector('#products');
const messageBox  = document.querySelector('#page-message');
const loadingBox  = document.querySelector('#loading');

function productCard(p) {
    const img  = esc(p.image_url || 'img/logo-black.png');
    const name = esc(p.name);
    const qty  = Number(p.quantity || 0);
    return `
        <div class="product">
            <img src="${img}" alt="${name}" loading="lazy">
            <h2>${name}</h2>
            <h3>$${money(p.price)}</h3>
            <p>Dostępne: ${qty}</p>
            <a href="product.php?id=${Number(p.id)}">Zobacz produkt</a>
        </div>`;
}

async function loadProducts() {
    loadingBox.classList.remove('hidden');
    productsBox.innerHTML = '';
    const params = {};
    if (typeSelect.value && typeSelect.value !== 'all') params.type = typeSelect.value;
    if (searchInput.value.trim()) params.search = searchInput.value.trim();

    try {
        const data     = await apiGet('/products.php', params);
        const products = data.products || [];
        if (!products.length) {
            productsBox.innerHTML = `<div class="product-card-empty"><h2>Brak produktów</h2></div>`;
        } else {
            productsBox.innerHTML = products.map(productCard).join('');
        }
    } catch (err) {
        setMessage(messageBox, err.message, 'error');
    } finally {
        loadingBox.classList.add('hidden');
    }
}

async function init() {
    initCommonUi();
    const type   = qp('type')   || 'all';
    const search = qp('search') || '';
    await loadCategories([typeSelect], type);
    searchInput.value = search;

    form.addEventListener('submit', async e => {
        e.preventDefault();
        const url = new URL(window.location.href);
        url.searchParams.set('type', typeSelect.value || 'all');
        if (searchInput.value.trim()) url.searchParams.set('search', searchInput.value.trim());
        else url.searchParams.delete('search');
        window.history.replaceState({}, '', url);
        await loadProducts();
    });

    await loadProducts();
}

init();
