<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Koszyk — Jaguar</title>
    <link rel="shortcut icon" href="img/logo-white.png" type="image/x-icon">
    <link rel="stylesheet" href="base.css">
    <link rel="stylesheet" href="pages.css">
</head>
<body>
    <header>
        <img src="img/logo-black.png" alt="Jaguar logo">
        <h1>Jaguar</h1>
    </header>
    <nav>
        <a href="index.php">Strona główna</a>
        <form action="index.php" method="get">
            <select name="type" id="type-filter"><option value="all">Wszystkie</option></select>
            <input type="text" name="search" id="search-filter" placeholder="Szukaj...">
            <input type="submit" value="🔍">
        </form>
        <a href="listing.php">+ Dodaj produkt</a>
        <a href="cart.php" data-cart-count>🛒</a>
        <div class="spacer"></div>
        <div class="nav-user" data-user-area></div>
    </nav>

    <div id="page-message" class="message hidden"></div>

    <div class="cart-layout">
        <main id="cart-items"></main>

        <aside class="cart-sidebar">
            <div class="summary-box">
                <h2>Podsumowanie: <span id="cart-total">$0.00</span></h2>
                <form id="checkout-form">
                    <label for="first_name">Imię</label>
                    <input type="text" name="first_name" id="first_name" placeholder="Jan" required>

                    <label for="last_name">Nazwisko</label>
                    <input type="text" name="last_name" id="last_name" placeholder="Kowalski" required>

                    <label for="address">Adres</label>
                    <input type="text" name="address" id="address" placeholder="ul. Przykładowa 1" required>

                    <label for="city">Miasto</label>
                    <input type="text" name="city" id="city" placeholder="Warszawa" required>

                    <label for="postal_code">Kod pocztowy</label>
                    <input type="text" name="postal_code" id="postal_code" placeholder="00-000" required>

                    <button type="submit">Złóż zamówienie</button>
                </form>
            </div>
        </aside>
    </div>

    <script src="js/config.js"></script>
    <script type="module" src="js/cart.js"></script>
</body>
</html>
