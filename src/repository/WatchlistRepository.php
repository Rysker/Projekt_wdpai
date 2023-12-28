<?php

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
            SELECT asset_name as name, asset_ticker as ticker, x.type_name as type, price, market_ticker as market FROM public.watchlist
            natural join public.asset
            natural join public.current_price
            natural join public.market
            natural join public.asset_type as x
            where id_user = 1;
        ');
        $stmt->execute();
        $watchlist = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($watchlist as $asset) 
        {
             $result[] = new Asset(
                 $asset['name'],
                 $asset['market'],
                 $asset['type'],
                 $asset['ticker'],
                 $asset['price'],
             );
         }

        return $result;
    }
}