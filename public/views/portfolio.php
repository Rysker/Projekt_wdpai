<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel ="stylesheet" href= "public/css/shared.css">
    <link rel="stylesheet" href="public/css/modal.css">
    <link rel="stylesheet" href="public/css/form.css">
    <link rel ="stylesheet" href= "public/css/portfolio.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
    <script src="src/scripts/portfolioScripts.js" defer></script>
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
            <div class="top-space">
                <div class="portfolios-part">
                <?php foreach($portfolios as $portfolio): ?>
                    <?php if ($portfolio !== null): ?>
                        <div class="portfolio-pick" data-id="<?= $portfolio->getId(); ?>" data-portfolio="<?= htmlspecialchars(json_encode($portfolio)); ?>">
                            <p><?= $portfolio->getName(); ?></p>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
                </div> 
                <div class="new-portfolio" id="newPortfolio" onclick="openFormModal()">
                    + New Portfolio
                </div>
                <div id="deletePortfolio">
                    <img src="public/assets/trash-solid.svg" alt="Delete" />
                </div>
            </div>

            <div class="analysis-space">
                <div class="pi-topspace">
                    <div class='one-line'>
                        <h2 id="portfolio_name"></h2>
                        <div class="pi-history" onclick="downloadHistory()">
                            History
                        </div>
                    </div>
                    <p id="portfolio_value"></p>
                </div>
                
                <div class="pi-analysis">
                </div>
            </div>

            <div class="content-space">
                <div class="add_investment" id="newPortfolio" onclick="addInvestmentForm(<?php echo htmlspecialchars(json_encode($assets)); ?>)">
                    + New Investment
                </div>
                <table class="portfolio-content">
                    <tr class="legend">
                        <td>Ticker</td>
                        <td>Name</td>
                        <td>Type</td>
                        <td>Quantity</td>
                        <td>Price</td>
                        <td>Value</td>
                        <td>Profit</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div id="modalContainer" class="modal-container" style="display: none;">
        <div id="modalContent" class="modal-content">
            <div id="close">
                <span class="close" onclick="closeFormModal()">Close</span>
            </div>
            <div id="modalForm" class="modal-form">
            </div>
            <div>
        </div>    
    </div>

</body>
</html>