<?php

require __DIR__.'/Routing.php';

$path = trim($_SERVER['REQUEST_URI'], '/');
$path = parse_url($path, PHP_URL_PATH);

Routing::get('', 'DefaultController');
Routing::get('main', 'AccessController');
Routing::get('watchlist', 'AccessController');
Routing::get('portfolio', 'AccessController');
Routing::post('login', 'SecurityController');
Routing::post('register', 'SecurityController');
Routing::get('logout', 'ActionController');
Routing::post("addPortfolio", 'ActionController');
Routing::get("deletePortfolio", 'ActionController');
Routing::get("downloadHistory", 'ActionController');
Routing::post("addTransaction", 'ActionController');
Routing::get('more', 'AccessController');
Routing::get('getUsers', 'ActionController');
Routing::get('getCurrencies', 'ActionController');
Routing::post('blockUser', 'ActionController');
Routing::post('changeCurrency', 'ActionController');
Routing::get('deleteObserved', 'ActionController');
Routing::get('updateWatchlist', 'ActionController');
Routing::get('getPrivileges', 'AccessController');

Routing::run($path);
