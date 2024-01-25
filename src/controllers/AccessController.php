<?php
if (session_status() == PHP_SESSION_NONE) 
    session_start();
require_once "AppController.php";
require_once __DIR__ .'/../models/User.php';
require_once __DIR__ .'/../repository/UserRepository.php';
require_once __DIR__."/../repository/WatchlistRepository.php";
require_once __DIR__."/../repository/PortfolioRepository.php";
require_once __DIR__."/../repository/AssetRepository.php";

class AccessController extends AppController
{
    private $watchlist_repository;
    private $user_repository;
    private $portfolio_repository;
    private $asset_repository;

    public function __construct()
    {
        $this->watchlist_repository = new WatchlistRepository();
        $this->portfolio_repository = new PortfolioRepository();
        $this->user_repository = new UserRepository();
        $this->asset_repository = new AssetRepository();
    }

    public function watchlist()
    {
        if (!$this->isUserLoggedIn()) 
        {
            $this->render("login", [
                "messages" => "Access denied!"
            ]);
            exit();
        }
        {
            $_SESSION['expire'] = time() + (10 * 60);
            $this->render("watchlist", [
                "watchlist" => $this->watchlist_repository->getObserved()
            ]);
        }
    }

    public function portfolio()
    {
        if (!$this->isUserLoggedIn()) 
        {
            $this->render("login", [
                "messages" => "Access denied!"
            ]);
            exit();
        }
        else
        {
            $_SESSION['expire'] = time() + (10 * 60);
            $this->render("portfolio", [
                "portfolios" => $this->portfolio_repository->getPortfolios(),
                "assets" => $this->asset_repository->getAssets()
            ]);
        }
    }

    public function more()
    {
        if (!$this->isUserLoggedIn()) 
        {
            $this->render("login", [
                "messages" => "Access denied!"
            ]);
            exit();
        }
        else
        {
            $_SESSION['expire'] = time() + (10 * 60);
            $this->render("more");
        }
    }

    public function main()
    {
        if (!$this->isUserLoggedIn()) 
        {
            $this->render("login", [
                "messages" => "Access denied!"
            ]);
            exit();
        }
        else
        {
            $_SESSION['expire'] = time() + (10 * 60);
            $this->render("main", [
                "assets" => $this->asset_repository->getAssets()
            ]);
        }
    }

    public function logout()
    {
        $this->render("logout");
        exit();
    }


    public function getPrivileges()
    {
        return $this->user_repository->getPriviliges();
    }

    private function isUserLoggedIn()
    {
       return isset($_SESSION['valid']) && $_SESSION['valid'] === true && time() < $_SESSION['expire'];
    }
}