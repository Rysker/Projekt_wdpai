<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel ="stylesheet" href= "public/css/main.css">
    <link rel ="stylesheet" href= "public/css/shared.css">
    <link rel="stylesheet" href="public/css/modal.css">
    <link rel="stylesheet" href="public/css/form.css">
    <link rel="stylesheet" href="public/css/more.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
    <script src = "src/scripts/morePanel.js" defer></script>
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
            <div class="option" id="userStatus" onclick="userBlock()">
                Block/Unblock User
            </div>
            <div class="option" id="changeCurrency" onclick="changeCurrency()">
                Change Currency
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
</body>
</html>