<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rejestracja — Jaguar</title>
    <link rel="shortcut icon" href="img/logo-white.png" type="image/x-icon">
    <link rel="stylesheet" href="base.css">
    <link rel="stylesheet" href="pages.css">
</head>
<body>
    <header>
        <img src="img/logo-black.png" alt="Jaguar logo">
        <h1>Jaguar</h1>
    </header>

    <main>
        <div class="login-wrap">
            <div class="login-card">
                <h2>Utwórz konto</h2>
                <p>Masz już konto? <a href="login.php">Zaloguj się</a></p>
                <hr>
                <div id="page-message" class="message hidden"></div>
                <form id="register-form">
                    <label for="login">Login</label>
                    <input type="text" name="login" id="login" placeholder="Min. 3 znaki" required autocomplete="username">

                    <label for="password">Hasło</label>
                    <input type="password" name="password" id="password" placeholder="Min. 6 znaków" required autocomplete="new-password">

                    <label for="confirm">Potwierdź hasło</label>
                    <input type="password" name="confirm" id="confirm" placeholder="Powtórz hasło" required autocomplete="new-password">

                    <button type="submit">Utwórz konto</button>
                </form>
            </div>
        </div>
    </main>

    <script src="js/config.js"></script>
    <script type="module" src="js/register.js"></script>
</body>
</html>
