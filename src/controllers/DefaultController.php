<?php

require_once __DIR__."/AppController.php";
require_once __DIR__."/../models/Asset.php";
require_once __DIR__."/../repository/WatchlistRepository.php";
require_once __DIR__."/../repository/PortfolioRepository.php";
require_once __DIR__."/../repository/AssetRepository.php";

class DefaultController extends AppController
{
    public function index()
    {
        $this->render("index");
    }

    public function register()
    {
        $this->render("register");
    }
}