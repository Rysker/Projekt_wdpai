<?php
if (session_status() == PHP_SESSION_NONE) 
    session_start();
require_once 'Repository.php';
require_once __DIR__.'/../models/Transaction.php';
require_once __DIR__.'/../models/Portfolio.php';
require_once __DIR__.'/../models/Investment.php';

class AssetRepository extends Repository
{    
    public function getAssets(): array
    {
        $result = [];
        $token = $_SESSION['token'];
        $id = $this->getIdFromToken($token);
        $stmt = $this->database->connect()->prepare('
            SELECT
            public.asset.id_asset as id,
            asset_name as name,
            asset_ticker as ticker,
            type_name as type,
            market_ticker as market,
            price,
            currency_code as currency,
            currency_sign as sign,
            modification,
            CASE WHEN watchlist.id_asset IS NOT NULL THEN true ELSE false END AS in_watchlist
            FROM
                public.asset
                NATURAL JOIN public.asset_type
                NATURAL JOIN public.market
                NATURAL JOIN public.current_price
                NATURAL JOIN public.currency
                LEFT JOIN public.watchlist ON public.asset.id_asset = public.watchlist.id_asset AND public.watchlist.id_user = ?;
        ');
        $stmt->execute([$id]);
        $assets = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($assets as $asset) 
        {
            $result[] = new Asset
            (
                $asset['id'],
                $asset['name'],
                $asset['market'],
                $asset['type'],
                $asset['ticker'],
                $asset['price'],
                $asset['currency'],
                $asset['sign'],
                $asset['modification'],
                $asset['in_watchlist'],
            );
         }

        return $result;
    }
}

