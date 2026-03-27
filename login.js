import { apiPost } from './api.js';
import { initCommonUi, saveAuth, setMessage } from './common.js';

const form       = document.querySelector('#login-form');
const messageBox = document.querySelector('#page-message');

initCommonUi();

form.addEventListener('submit', async e => {
    e.preventDefault();

    const payload = {
        login:    form.login.value.trim(),
        password: form.password.value,
    };

    try {
        const data = await apiPost('/login.php', payload);
        saveAuth({
            id:         data.user.id,
            login:      data.user.login,
            token:      data.token,
            expires_at: data.expires_at,
        });
        setMessage(messageBox, 'Zalogowano pomyślnie. Przekierowuję...', 'success');
        setTimeout(() => { window.location.href = 'index.php'; }, 600);
    } catch (err) {
        setMessage(messageBox, err.message, 'error');
    }
});
