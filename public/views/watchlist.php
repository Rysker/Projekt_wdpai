<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel ="stylesheet" href= "public/css/watchlist.css">
    <link rel ="stylesheet" href= "public/css/shared.css">
    <script src="src/scripts/watchlistScripts.js" defer></script>
    <title>Login</title>
</head>
<body>
    <div class="leftmenu">
        <h1><a href="main" id="stocktracker">Stock Tracker</a></h1>
        <div class="links">
            <div class="menu-button" onclick="window.location.href='main';">Main</div>
            <div class="menu-button" onclick="window.location.href='watchlist';">Watchlist</div>
            <div class="menu-button" onclick="window.location.href='portfolio';">Portfolio</div>
            <div id="more" class="menu-button" onclick="window.location.href='more';">More</div>
        </div>
    </div>
    <div class="rightmenu">
        <div class="topbar">
            <div class="logout-container">
                <a href="logout">Logout</a>
            </div>
        </div>
        <div class="workspace">
            <table class="watchlist">
                <tr class="legend">
                    <td>Ticker</td>
                    <td>Name</td>
                    <td>Price</td>
                </tr>
                <?php foreach($watchlist as $asset): ?> 
                    <tr class="asset" data-id="<?= $asset->getId(); ?>">
                        <td>
                            <div class="ticker"><?= $asset->getTicker()?></div>
                            <div class="market"><?= $asset->getMarket()?></div>
                        </td>
                        <td>
                            <?= $asset->getName(); ?>
                        </td>
                        <td>
                            <?= $asset->getPrice().$asset->getSign(); ?>
                        </td>
                        <td>
                            <div class="delete-link">
                                <span id="star">&#9733;</span>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?> 
            </table>
        </div>
    </div>
</body>
</html>