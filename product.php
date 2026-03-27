<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produkt — Jaguar</title>
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
    <div id="loading" class="loading hidden">Ładowanie produktu</div>
    <main id="product-box"></main>

    <script src="js/config.js"></script>
    <script type="module" src="js/product.js"></script>
</body>
</html>
