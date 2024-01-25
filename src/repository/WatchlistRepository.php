<?php
if (session_status() == PHP_SESSION_NONE) 
    session_start();
require_once 'Repository.php';
require_once __DIR__.'/../models/Asset.php';

class WatchlistRepository extends Repository
{    
    public function __construct()
    {
        parent::__construct();
    }
    public function getObserved(): array
    {
        $result = [];

        $stmt = $this->database->connect()->prepare('
            SELECT id_asset, asset_name as name, asset_ticker as ticker, x.type_name as type,
            price, market_ticker as market, currency_code as currency, price, currency_sign as sign, modification FROM public.watchlist
            natural join public.asset
            natural join public.current_price
            natural join public.market
            natural join public.currency
            natural join public.asset_type as x
            where id_user = ?;
        ');
        $token = $_SESSION['token'];
        $id = $this->getIdFromToken($token);
        $stmt->execute([$id]);
        $watchlist = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($watchlist as $asset) 
        {
             $result[] = new Asset(
                $asset['id_asset'],
                $asset['name'],
                $asset['market'],
                $asset['type'],
                $asset['ticker'],
                $asset['price'],
                $asset['currency'],
                $asset['sign'],
                $asset['modification'],
             );
         }

        return $result;
    }

    public function removeFromObserved(int $assetId): void
    {
        $token = $_SESSION['token'];
        $userId = $this->getIdFromToken($token);
        $stmt = $this->database->connect()->prepare('
            DELETE FROM public.watchlist
            WHERE id_user = ? AND id_asset = ?;
        ');

        $stmt->execute([$userId, $assetId]);
    }

    public function addToObserved(int $assetId): void
    {
        $token = $_SESSION['token'];
        $userId = $this->getIdFromToken($token);

        $stmt = $this->database->connect()->prepare('
            INSERT INTO public.watchlist (id_user, id_asset)
            VALUES (?, ?);
        ');

        $stmt->execute([$userId, $assetId]);
    }

    function deleteAsset($assetId) 
    {
        try 
        {
            $this->removeFromObserved($assetId);
        } catch (PDOException $e) 
        {
            $response = ['success' => false, 'error' => "Error: " . $e->getMessage()];
        } 
        exit();
    }


}