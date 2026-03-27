import { apiGet, apiUpload } from './api.js';
import { esc, getAuthUser, initCommonUi, loadCategories, setMessage } from './common.js';

const form           = document.querySelector('#listing-form');
const messageBox     = document.querySelector('#page-message');
const categorySelect = document.querySelector('#type');
const navTypeSelect  = document.querySelector('#type-filter');

function renderFormCategories(categories) {
    categorySelect.innerHTML = categories.length
        ? categories.map(c => `<option value="${esc(c.type)}">${esc(c.type)}</option>`).join('')
        : '<option value="">Brak kategorii</option>';
}

async function refreshCategories() {
    const data = await apiGet('/categories.php');
    renderFormCategories(data.categories || []);
    await loadCategories([navTypeSelect]);
}

async function init() {
    initCommonUi();

    const user = getAuthUser();
    if (!user) {
        document.querySelector('main').innerHTML = `
            <div class="listing-wrap">
                <div class="listing-card">
                    <h1>Dostęp zablokowany</h1>
                    <p style="margin-top:12px;">Musisz być <a href="login.php">zalogowany</a>, aby dodawać produkty.</p>
                </div>
            </div>`;
        return;
    }

    await refreshCategories();

    form.addEventListener('submit', async e => {
        e.preventDefault();
        const data = new FormData(form);

        try {
            await apiUpload('/create-product.php', data);
            setMessage(messageBox, '✅ Produkt dodany pomyślnie.', 'success');
            form.reset();
            await refreshCategories();
        } catch (err) {
            setMessage(messageBox, err.message, 'error');
        }
    });
}

init();
