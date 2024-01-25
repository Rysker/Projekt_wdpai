<?php
require_once 'Repository.php';
require_once 'UserRepository.php';
require_once __DIR__.'/../models/Transaction.php';
require_once __DIR__.'/../models/Portfolio.php';
require_once __DIR__.'/../models/Investment.php';
require_once __DIR__.'/../models/Forex.php';

class PortfolioRepository extends Repository
{   
    public function getPortfolios(): array
    {
        $result = [];
        $stmt = $this->database->connect()->prepare('
            select id_portfolio, name from public.portfolio
            where id_user = ?;
        ');
        $token = $_SESSION['token'];
        $id = $this->getIdFromToken($token);
        $stmt->execute([$id]);
        $portfolios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $forex = $this->getForex($id);
        $currency = $this->getCurrency($id);
        foreach ($portfolios as $portfolio) 
        {
            $tmp = $this->getInvestments($portfolio["id_portfolio"]);
            $result[] = new Portfolio
            (
                $portfolio["id_portfolio"],
                $portfolio['name'],
                $tmp,
                $forex,
                $currency
            );
        }
        return $result;
    }

    private function getCurrency($id_user)
    {
        $user = new UserRepository();
        return $user->getCurrency($id_user);
    }

    private function getForex($id_user)
    {
        $result = [];
        $currency = $this->getCurrency($id_user);
        $stmt = $this->database->connect()->prepare('
            select * from getForex(?);
        ');
        $stmt->execute([$currency]);
        $tmp = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($tmp as $forex) 
        {
            $result[] = new Forex
            (
                $forex["from_currency_code"],
                $forex['to_currency_code'],
                $forex['price']
            );
        }
        return $result;
    }

    private function getInvestments($id_portfolio)
    {
        $result = [];

        $stmt = $this->database->connect()->prepare('
            SELECT id_investment, asset_name as name, asset_ticker as ticker, x.type_name as type, price, 
            market_ticker as market, currency_code as currency, price, currency_sign as sign, modification FROM public.investment
            natural join public.asset
            natural join public.current_price
            natural join public.market
            natural join public.currency
            natural join public.asset_type as x
            where id_portfolio = ?;
        ');
        $stmt->execute([$id_portfolio]);
        $investments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($investments as $investment) 
        {
            $tmp = $this->getTransactions($investment['id_investment']);

            $result[] = new Investment
            (
                $investment['id_investment'],
                $investment['name'],
                $investment['market'],
                $investment['type'],
                $investment['ticker'],
                $investment['price'],
                $investment['currency'],
                $investment['sign'],
                $investment['modification'],
                $tmp[0],
                $tmp[1],
                $tmp[2]
            );
        }
        return $result;
    }

    function getPortfolioInfo($portfolioId) 
    {
        try 
        {
            $stmt = $this->database->prepare("SELECT name, total_value FROM portfolios WHERE id = ?");
            $stmt->execute([$portfolioId]);
            $portfolioInfo = $stmt->fetch(PDO::FETCH_ASSOC);
            return $portfolioInfo;
        } 
        catch (PDOException $e) 
        {
            return ['error' => 'Error fetching portfolio information'];
        } 
    }

    private function getTransactions($id_investment)
    {
        $result = [];
        $number = 0;
        $value = 0;
        $all_transactions = [];
        $stmt = $this->database->connect()->prepare('
            SELECT id_transaction, quantity, price, date, transaction_type as type FROM public.transaction
            natural join public.investment
            natural join public.transaction_type
            where id_investment = ?;
        ');
        $stmt->execute([$id_investment]);
        $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($transactions as $transaction) 
        {
            if($transaction['type'] == 'BUY')
            {
                $number = $number + $transaction['quantity'];
                $value = $value + (double)($transaction['quantity']) * (double)($transaction['price']);
            }
            else if($transaction['type'] == 'SELL')
            {
                $number = $number - $transaction['quantity'];
                $value = $value - (double)($transaction['quantity']) * (double)($transaction['price']);
            }
            array_push($all_transactions, new Transaction
            (
                $transaction['id_transaction'],
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

    public function addPortfolio($name)
    {
        $token = $_SESSION['token'];
        $id = $this->getIdFromToken($token);
        $stmt = $this->database->connect()->prepare('
            INSERT into public.portfolio (id_user, name) values (?, ?);
        ');
        $stmt->execute([$id, $name]);
    }

    public function deletePortfolio($portfolio)
    {
        $token = $_SESSION['token'];
        $id = $this->getIdFromToken($token);
        $stmt = $this->database->connect()->prepare('
            DELETE FROM public.portfolio WHERE id_portfolio = ? and id_user = ?;
        ');
        $stmt->execute([$portfolio, $id]);
    }

    public function getHistory($portfolio)
    {
        $token = $_SESSION['token'];
        $id = $this->getIdFromToken($token);
        $stmt = $this->database->connect()->prepare('
            select date, asset_name as name, quantity, price, currency_sign as sign, transaction_type as type
            from public.asset
            natural join public.portfolio
            natural join public.user
            natural join public.transaction
            natural join public.transaction_type
            natural join public.investment
            natural join public.currency
            where id_user = ? and id_portfolio = ?
            order by date asc;
        ');
        $stmt->execute([$id, $portfolio]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addTransaction($id_portfolio, $id_asset, $id_type, $quantity, $price, $date)
    {
        $stmt = $this->database->connect()->prepare('
            CALL public.handle_transaction(?, ?, ?, ?, ?, ?);
        ');
        $stmt->execute([$id_portfolio, $id_asset, $id_type, $quantity, $price, $date]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCurrencies()
    {
        $stmt = $this->database->connect()->prepare('
            select * from public.currency;
        ');
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}