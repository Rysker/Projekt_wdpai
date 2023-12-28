<?php

require_once 'Repository.php';
require_once __DIR__.'/../models/Transaction.php';
require_once __DIR__.'/../models/Portfolio.php';
require_once __DIR__.'/../models/Investment.php';

class PortfolioRepository extends Repository
{    
    public function getPortfolios(): array
    {
        $result = [];

        $stmt = $this->database->connect()->prepare('
            select id_portfolio, name from public.portfolio
            where id_user = 1;
        ');
        $stmt->execute();
        $portfolios = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($portfolios as $portfolio) 
        {
            $tmp = $this->getInvestments($portfolio["id_portfolio"]);
            $result[] = new Portfolio(
                 $portfolio['name'],
                 $tmp
            );
         }

        return $result;
    }

    private function getInvestments($id_portfolio)
    {
        $result = [];

        $stmt = $this->database->connect()->prepare('
        SELECT id_investment, asset_name as name, asset_ticker as ticker, x.type_name as type, price, market_ticker as market FROM public.investment
        natural join public.asset
        natural join public.current_price
        natural join public.market
        natural join public.asset_type as x
        where id_portfolio = :id_portfolio;
        ');
        $stmt->bindParam(':id_portfolio', $id_portfolio);
        $stmt->execute();
        $investments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($investments as $investment) 
        {
            $tmp = $this->getTransactions($investment['id_investment']);

            $result[] = new Investment
            (
                 $investment['name'],
                 $investment['market'],
                 $investment['type'],
                 $investment['ticker'],
                 $investment['price'],
                 $tmp[0],
                 $tmp[1],
                 $tmp[2]
            );
        }
        return $result;
    }

    private function getTransactions($id_investment)
    {
        $result = [];
        $number = 0;
        $value = 0;
        $all_transactions = 0;
        $stmt = $this->database->connect()->prepare('
        SELECT quantity, price, date, transaction_type FROM public.transaction
        natural join public.investment
        natural join public.transaction_type
        where id_transaction = :id_investment;
        ');
        $stmt->bindParam(':id_investment', $id_investment);
        $stmt->execute();
        $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($transactions as $transaction) 
        {
            if($transaction['type'] == 'BUY')
            {
                $number = $number + $transaction['quantity'];
                $value = $value + ($transaction['quantity'] * $transaction['price']);
            }
            else if($transaction['type'] == 'SELL')
            {
                $number = $number - $transaction['quantity'];
                $value = $value - ($transaction['quantity'] * $transaction['price']);
            }
            array_push($all_transactions, new Transaction
            (
                $transaction['quantity'],
                $transaction['price'],
                $transaction['type'],
                $transaction['date']
            ));
        }
        array_push($result, $number);
        array_push($result, $value);
        array_push($result, $all_transactions);
        return $result;
    }
}