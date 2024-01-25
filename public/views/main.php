<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel ="stylesheet" href= "public/css/main.css">
    <link rel ="stylesheet" href= "public/css/shared.css">
    <script src = "src/scripts/mainScripts.js" defer></script>
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
            <div class="search">
                <input class="searchbar" type="text" placeholder="Search for assets">
            </div>
            <div class="choice-boxes">
                <div class="choice-box">
                    Stock
                </div>
                <div class="choice-box">
                    Index
                </div>
                <div class="choice-box">
                    ETF
                </div>
                <div class="choice-box">
                    Crypto
                </div>
            </div>
            <div class="list">
                <table class="stock-search">
                    <tr class="legend">
                        <td>Ticker</td>
                        <td>Name</td>
                        <td>Type</td>
                        <td>Price</td>
                        <td>Time</td>
                    </tr>
                    <?php foreach($assets as $asset): ?> 
                        <tr class="asset" data-id="<?= $asset->getId(); ?>" data-asset="<?= htmlspecialchars(json_encode($asset)); ?>">
                            <td>
                                <div class="ticker"><?= $asset->getTicker()?></div>
                                <div class="market"><?= $asset->getMarket()?></div>
                            </td>
                            <td>
                                <?= $asset->getName(); ?>
                            </td>
                            <td>
                                <?= $asset->getType(); ?>
                            </td>
                            <td>
                                <?= $asset->getPrice().$asset->getSign(); ?>
                            </td>
                            <td>
                                <?= $asset->getModification() ?>
                            </td>
                            <td>
                                <div class="star-container" onclick="changeStar(this)">
                                    <span id="star">&#9733;</span>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?> 
                </table>
            </div>
        </div>
    </div>
</body>
</html>