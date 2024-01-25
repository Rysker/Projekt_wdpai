<!DOCTYPE html>
<head>
    <link rel="stylesheet" href="public/css/form_add_investment.css">
</head>
<form class="addTransaction" action="addTransaction" method="POST">
    <input type="hidden" id="portfolio_id" name="portfolio_id" value="">
    <select class="input1" id="input_asset" name="input_asset" style="width: 100%"></select>
    <select class="input1" id="input_type" name="input_type">
        <option value='1'>BUY</option>
        <option value='2'>SELL</option>
    </select>
    <input class="input1" type="number" id="quantity" name="quantity" placeholder="Quantity">
    <input class="input1" type="text" id="price" name="price" placeholder="Price">
    <input class="input1" type="date" id="date" name="date">
    <button class='submit' type="submit">Create</button>
</form>
</form>
</div>