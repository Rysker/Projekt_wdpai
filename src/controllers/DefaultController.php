<?php

require_once __DIR__."/AppController.php";
require_once __DIR__."/../models/Asset.php";
require_once __DIR__."/../repository/WatchlistRepository.php";

class DefaultController extends AppController
{
    private $watchlist_repository;

    public function __construct()
    {
        $this->watchlist_repository = new WatchlistRepository();
    }

    public function index()
    {
        $this->render("index");
    }

    public function login()
    {
        $this->render("login");
    }

    public function register()
    {
        $this->render("register");
    }

    public function main()
    {
        $this->render("main");
    }

    public function watchlist()
    {
        $this->render("watchlist",[ 
            "watchlist" => $this->watchlist_repository->getObserved()
        ]);
    }

    public function portfolio()
    {
        $this->render("portfolio");
    }
}