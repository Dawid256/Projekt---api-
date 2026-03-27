import { apiPost } from './api.js';
import { initCommonUi, setMessage } from './common.js';

const form       = document.querySelector('#register-form');
const messageBox = document.querySelector('#page-message');

initCommonUi();

form.addEventListener('submit', async e => {
    e.preventDefault();

    const payload = {
        login:           form.login.value.trim(),
        password:        form.password.value,
        confirmPassword: form.confirm.value,
    };

    try {
        await apiPost('/register.php', payload);
        setMessage(messageBox, 'Konto utworzone! Przekierowuję do logowania...', 'success');
        form.reset();
        setTimeout(() => { window.location.href = 'login.php'; }, 900);
    } catch (err) {
        setMessage(messageBox, err.message, 'error');
    }
});
