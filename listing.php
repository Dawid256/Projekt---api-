<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dodaj produkt — Jaguar</title>
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

    <main>
        <div class="listing-wrap">
            <div class="listing-card">
                <h1>Dodaj produkt</h1>
                <div id="page-message" class="message hidden"></div>

                <form id="listing-form" enctype="multipart/form-data">
                    <label for="name">Nazwa produktu</label>
                    <input type="text" name="name" id="name" placeholder="np. Jaguar Wheel" required>

                    <label for="description">Opis</label>
                    <textarea name="description" id="description" rows="3" placeholder="Opis produktu..." required></textarea>

                    <label for="price">Cena ($)</label>
                    <input type="number" name="price" id="price" min="0.01" step="0.01" value="1.00" required>

                    <label for="quantity">Ilość w magazynie</label>
                    <input type="number" name="quantity" id="quantity" min="1" value="1" required>

                    <label for="type">Kategoria</label>
                    <select name="type" id="type"></select>

                    <label for="newtype">Lub nowa kategoria</label>
                    <input type="text" name="newtype" id="newtype" placeholder="Wpisz nową kategorię (opcjonalne)">
                    <small class="helper">Jeśli wypełnisz to pole, zostanie użyta nowa kategoria.</small>

                    <label for="img">Zdjęcie produktu</label>
                    <input type="file" name="img" id="img" accept="image/png,image/jpeg,image/jpg" required>

                    <button type="submit">Potwierdź dodanie</button>
                </form>
            </div>
        </div>
    </main>

    <script src="js/config.js"></script>
    <script type="module" src="js/listing.js"></script>
</body>
</html>
