<?php
if (session_status() == PHP_SESSION_NONE) 
    session_start();
require_once "AppController.php";
require_once __DIR__ .'/../models/User.php';
require_once __DIR__ .'/../repository/UserRepository.php';
require_once __DIR__."/../repository/WatchlistRepository.php";
require_once __DIR__."/../repository/PortfolioRepository.php";
require_once __DIR__."/../repository/AssetRepository.php";

class ActionController extends AppController
{
    private $watchlist_repository;
    private $portfolio_repository;
    private $asset_repository;
    private $user_repository;
    private $access;
    public function __construct()
    {
        $this->watchlist_repository = new WatchlistRepository();
        $this->portfolio_repository = new PortfolioRepository();
        $this->asset_repository = new AssetRepository();
        $this->user_repository = new UserRepository();
        $this->access = new AccessController();
    }

    public function addPortfolio()
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
            try 
            {
                $name = $_POST['name'];
                if(is_string($name))
                {
                    $this->portfolio_repository->addPortfolio($_POST['name']);
                    $this->access->portfolio();
                }
                else
                {
                    error_log("Invalid input");
                    return $this->access->portfolio();
                }

            } 
            catch (PDOException $exception) 
            {
                return $this->access->portfolio();
            }
            
        }
    }

    public function deletePortfolio()
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
            try 
            {
                $this->portfolio_repository->deletePortfolio($_GET['id']);
                echo json_encode(['success' => true, 'message' => 'Portfolio deleted successfully']);
            } 
            catch (PDOException $exception) 
            {
                error_log("Error deleting portfolio: " . $exception->getMessage());
                echo json_encode(['success' => false, 'message' => 'Error deleting portfolio']);
            }
            
        }
    }
    private function isUserLoggedIn()
    {
       return isset($_SESSION['valid']) && $_SESSION['valid'] === true && time() < $_SESSION['expire'];
    }

    public function downloadHistory()
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
            try 
            {
                $transactions = $this->portfolio_repository->getHistory($_GET['id']);
                echo json_encode($transactions);
            } 
            catch (PDOException $exception) 
            {
                error_log("Error downloading history: " . $exception->getMessage());
                echo json_encode(['success' => false, 'message' => 'Error downloading history']);
            }
            
        }
    }

    public function addTransaction()
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
            try 
            {
                $id_portfolio = filter_input(INPUT_POST, 'portfolio_id', FILTER_VALIDATE_INT);
                $id_asset = filter_input(INPUT_POST, 'input_asset', FILTER_VALIDATE_INT);
                $id_type = filter_input(INPUT_POST, 'input_type', FILTER_VALIDATE_INT);
                $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_FLOAT);
                $price = $_POST['price'];
                $price = str_replace(',', '.', $price);
                $date = $_POST['date'];
                $date_check = $this->isRealDate($date);
                if(!(
                    $id_portfolio !== false &&
                    $id_asset !== false &&
                    $id_type !== false &&
                    $quantity !== false &&
                    is_numeric($price) &&
                    $date_check !== false
                ))
                {
                    $this->render("portfolio", [
                        "messages" => "Invalid input data."
                    ]);
                    exit();
                }
                $this->portfolio_repository->addTransaction($id_portfolio, $id_asset, $id_type, $quantity, $price, $date);
                $this->access->portfolio();
            } 
            catch (PDOException $exception) 
            {
                return $this->access->portfolio();
            }
        }
    }
    public function blockUser()
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
            try 
            {
                $id_user = filter_input(INPUT_POST, 'input_user', FILTER_VALIDATE_INT);
                $id_type = filter_input(INPUT_POST, 'input_type', FILTER_VALIDATE_INT);
                $this->user_repository->updateUserStatus($id_user, $id_type);
                $this->access->more();
            } 
            catch (PDOException $exception) 
            {
                return $this->access->more();
            } 
        }
    }

    public function changeCurrency()
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
            try 
            {
                $id_currency = filter_input(INPUT_POST, 'input_currency', FILTER_VALIDATE_INT);
                $this->user_repository->updateUserCurrency($id_currency);
                $this->access->more();
            } 
            catch (PDOException $exception) 
            {
                return $this->access->more();
            } 
        }
    }

    public function logout()
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
            try 
            {
                $users = $this->user_repository->removeSession();
                header("Location: login");
            } 
            catch (PDOException $exception) 
            {
                return $this->access->more();
            }  
        }
    }
    public function getUsers()
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
            try 
            {
                $users = $this->user_repository->getUsers();
                echo json_encode($users);
            } 
            catch (PDOException $exception) 
            {
                return $this->access->more();
            }  
        }
    }

    public function getCurrencies()
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
            try 
            {
                $currencies = $this->portfolio_repository->getCurrencies();
                echo json_encode($currencies);
            } 
            catch (PDOException $exception) 
            {
                return $this->access->more();
            }  
        }
    }

    public function deleteObserved()
    {
        try 
        {
            $id_asset = $_GET['id'];
            $this->watchlist_repository->removeFromObserved($id_asset);
        } 
        catch (PDOException $exception) 
        {
            error_log("Error during deleting asset!");
            return $this->access->more();
        }
    }

    public function updateWatchlist()
    {
        try
        {
            if($_GET['action'] == 'add')
                $this->watchlist_repository->addToObserved($_GET['id']);
            else if($_GET['action'] == 'remove')
                $this->watchlist_repository->removeFromObserved($_GET['id']);
        }
        catch (PDOException $exception) 
        {
            error_log("Error during deleting asset!");
            return $this->access->watchlist();
        }
    }

    function isRealDate($date) 
    { 
        if (false === strtotime($date)) 
            return false;
        list($year, $month, $day) = explode('-', $date); 
        return checkdate($month, $day, $year);
    }

}