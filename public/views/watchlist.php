<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel ="stylesheet" href= "public/css/watchlist.css">
    <title>Login</title>
</head>
<body>
    <div class="leftmenu">
        <h1><a href="main" id="stocktracker">Stock Tracker</a></h1>
        <div class="links">
            <h1><a href="main" id="main">Main</a></h1>
            <h1><a href="watchlist" id="watchlist">Watchlist</a></h1>
            <h1><a href="portfolio" id="portfolio">Portfolio</a></h1>
            <h1><a href="analysis" id="analysis">Analysis</a></h1>
            <h1><a href="history" id="history">History</a></h1>
        </div>
        <h1><a href="more" id="more">More</a></h1>
    </div>
    <div class="rightmenu">
        <div class="topbar">
        </div>
        <div class="workspace">
            <div class="watchlist">
                <div class="legend">
                    <p>Ticker</p>
                    <p>Name</p>
                    <p>Price</p>
                </div>
                <?php foreach($watchlist as $asset): ?> 
                    <div class="asset">
                        <p>
                        <?= $asset->getTicker(); ?>
                        </p>
                        <p>
                        <?= $asset->getName(); ?>
                        </p>
                        <p>
                        <?= $asset->getPrice(); ?>
                        </p>
                    </div>
                <?php endforeach; ?> 
            </div>
        </div>
    </div>
</body>
</html>