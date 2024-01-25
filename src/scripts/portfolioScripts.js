const newPortfolioButton = document.querySelector('.new-portfolio a');
const formContainer = document.querySelector('.form-container');
let selectedPortfolioId;

document.querySelectorAll('.portfolio-pick').forEach(function (portfolioPick) 
{
    portfolioPick.addEventListener('click', function () 
    {
        let portfolioData = this.getAttribute('data-portfolio');

        try 
        {
            let portfolioObject = JSON.parse(portfolioData);
            selectedPortfolioId = this.dataset.id;
            changePortfolio(portfolioObject);
        } 
        catch (error) 
        {
            console.error('Error parsing JSON:', error);
        }
    });
});

function changePortfolio(portfolioObject) 
{
    updatePortfolioInfo(portfolioObject);
    updateTableRows(portfolioObject.investments);
    highlightPortfolio();
    highlightProfit();
    hideTableIfEmpty()
}

function updatePortfolioInfo(portfolio) 
{
    document.querySelector('#portfolio_name').innerHTML = portfolio.name;
    document.querySelector('#portfolio_value').innerHTML = Math.round(portfolio.total_value * 100)/100 + ' ' +portfolio.currency;
}

function updateTableRows(investments) 
{
    let tableBody = document.querySelector('.portfolio-content');
    tableBody.querySelectorAll('tr:nth-child(n+2)').forEach(function (row) 
    {
        row.remove();
    });

    investments.forEach(function (investment) 
    {
        if(investment.quantity != 0)
        {
            let newRow = document.createElement('tr');
            newRow.innerHTML = `
                <td>
                    <div class="ticker">${investment.ticker}</div>
                    <div class="market">${investment.market}</div>
                </td>
                <td>${investment.name}</td>
                <td>${investment.type}</td>
                <td>${investment.quantity}</td>
                <td>${investment.price}${investment.sign}</td>
                <td>${((investment.price * 100) * investment.quantity)/100}${investment.sign}</td>
                <td id='profit'>${investment.profit}${investment.sign}</td>
            `;
            tableBody.appendChild(newRow);
        }
    });
}

function highlightPortfolio() 
{
    let portfolioPicks = document.querySelectorAll('.portfolio-pick');
    portfolioPicks.forEach(function(portfolioPick) 
    {
        let portfolioId = portfolioPick.getAttribute('data-id');
        if (portfolioId == selectedPortfolioId)
            portfolioPick.style.border = '4px solid gold';
        else 
            portfolioPick.style.border = '2px solid #000';
    });
}

function hideTableIfEmpty() 
{
    let table = document.querySelector('.portfolio-content');
    let portfolios = document.querySelector(`.portfolio-pick`);
    if(portfolios == null)
    {
        table.style.visibility = 'hidden';
        return;
    }
    let tmp = document.querySelector(`.portfolio-pick[data-id="${selectedPortfolioId}"]`);
    let portfolio = JSON.parse(tmp.getAttribute('data-portfolio'));
    let value = parseFloat(portfolio.investments.length);
    if (value == 0) 
        table.style.visibility = 'hidden';
    else 
        table.style.visibility = 'visible';
}

function highlightProfit() 
{
    let profits = document.querySelectorAll('#profit');
    profits.forEach(function(profit) {
    let value = parseFloat(profit.textContent);
    if (value < 0) 
        profit.style.color = 'red';
    else 
        profit.style.color = 'green';
});
}

function openFormModal() 
{
    let modalContainer = document.querySelector('#modalContainer');
    let modalForm = document.querySelector('#modalForm');

    fetch('public/forms/add_portfolio_form.php')
        .then(response => response.text())
        .then(formHTML => 
        {
            modalForm.innerHTML = formHTML;
            modalContainer.style.display = 'flex';
        })
        .catch(error => 
        {
            console.error('Error fetching form:', error);
        });
}

function addInvestmentForm(assets) 
{
    let modalContainer = document.querySelector('#modalContainer');
    let modalForm = document.querySelector('#modalForm');
    fetch('public/forms/add_investment_form.php')
        .then(response => response.text())
        .then(formHTML => {
            modalForm.innerHTML = formHTML;
            modalContainer.style.display = 'flex';

            const dataList = modalContainer.querySelector('#input_asset');

            assets.forEach(asset => {
                const option = document.createElement('option');
                option.value = asset.id_asset;
                option.textContent = asset.name+' ('+asset.currency+')';
                dataList.appendChild(option);
            });
            
            const hiddenInput = document.querySelector("#portfolio_id");
            hiddenInput.value = selectedPortfolioId;

            $('#input_asset').select2();

            let tmp = document.querySelector(`.portfolio-pick[data-id="${selectedPortfolioId}"]`);
            let portfolio = JSON.parse(tmp.getAttribute('data-portfolio'));
            updateTransactionTypeOptions(portfolio);

            $('#input_asset').on('change', function () 
            {
                updateTransactionTypeOptions(portfolio);
                validateTransactionType(portfolio);
            });

            document.querySelector('.addTransaction').addEventListener('input', function () 
            {
                validateQuantity(portfolio);
                validatePrice();
                validateDate();
                validateTransactionType(portfolio);
            });

            document.querySelector('.addTransaction').addEventListener('submit', function (event) 
            {
                if (!validateForm()) 
                    event.preventDefault();
            });
        })
        .catch(error => {
            console.error('Error fetching form:', error);
        });
}

function updateTransactionTypeOptions(portfolio) 
{
    let typeInput = document.querySelector('#input_type');
    let assetInput = document.querySelector('#input_asset');
    let selectedAssetName = assetInput.options[assetInput.selectedIndex].text.split(" ")[0];
    let selectedAsset = portfolio.investments.find(x => x.name === selectedAssetName);
    if (!selectedAsset) 
    {
        let sellOption = typeInput.querySelector('option[value="2"]');
        if (sellOption) 
            sellOption.remove();  
    } 
    else 
    {
        if (!typeInput.querySelector('option[value="2"]')) 
        {
            let sellOption = document.createElement('option');
            sellOption.value = '2';
            sellOption.text = 'SELL';
            typeInput.appendChild(sellOption);
        }
    }
}

function validateQuantity(portfolio) 
{
    let quantityInput = document.querySelector('#quantity');
    let assetInput = document.querySelector('#input_asset');
    let selectedAssetName = assetInput.options[assetInput.selectedIndex].text.split(" ")[0];
    let selectedAsset = portfolio.investments.find(x => x.name === selectedAssetName);
    let quantity = parseFloat(quantityInput.value);
    if (selectedAsset != null && (isNaN(quantity) || quantity > selectedAsset.quantity)) 
    {
        quantityInput.value = '';
        quantityInput.setCustomValidity('Quantity must be a number greater than or equal to 1.');
    } 
    else 
        quantityInput.setCustomValidity('');
}

function validatePrice() 
{
    let priceInput = document.querySelector('#price');
    let price = parseFloat(priceInput.value);

    if (isNaN(price) || price < 0.00) 
    {
        priceInput.value = '';
        priceInput.setCustomValidity('Price must be a number greater than or equal to 0.00.');
    } 
    else 
        priceInput.setCustomValidity('');
}

function validateDate() 
{
    let dateInput = document.querySelector('#date');
    let selectedDate = new Date(dateInput.value);
    let currentDate = new Date();

    if (
        isNaN(selectedDate.getTime()) ||
        selectedDate.getFullYear() > currentDate.getFullYear() ||
        (selectedDate.getFullYear() === currentDate.getFullYear() &&
            selectedDate.getMonth() > currentDate.getMonth()) ||
        (selectedDate.getFullYear() === currentDate.getFullYear() &&
            selectedDate.getMonth() === currentDate.getMonth() &&
            selectedDate.getDate() > currentDate.getDate())
    ) 
    {
        dateInput.value = '';
        dateInput.setCustomValidity('Date cannot be greater than or equal to the current date.');
    } 
    else 
        dateInput.setCustomValidity('');
}

function validateTransactionType(portfolio) 
{
    let typeInput = document.querySelector('#input_type');
    let transactionType = typeInput.value;
    let assetInput = document.querySelector('#input_asset');
    let selectedAssetName = assetInput.options[assetInput.selectedIndex].text;
    let selectedAsset = portfolio.investments.find(asset => asset.name === selectedAssetName);

    if (!selectedAsset)
        return;

    let quantityInput = document.querySelector('#quantity');
    let quantity = parseFloat(quantityInput.value);
    if (transactionType === '2' && (isNaN(quantity) || quantity > selectedAsset.quantity)) 
    {
        quantityInput.value = '';
        quantityInput.setCustomValidity('Cannot sell more than the quantity of the investment in the portfolio.');
    } 
    else 
        quantityInput.setCustomValidity('');
    
}

function validateForm() 
{
    let inputs = document.querySelectorAll('.addTransaction input:not([type="hidden"])');
    let isEmpty = Array.from(inputs).some(input => input.value.trim() === '');
    let dateInput = document.querySelector('#date');
    let selectedDate = new Date(dateInput.value);
    let currentDate = new Date();

    if (isEmpty) 
        return false;
    else if (isNaN(selectedDate.getTime()) || selectedDate > currentDate) 
    {
        dateInput.value = '';
        dateInput.setCustomValidity('Date cannot be greater than the current date.');
        return false;
    }
    return true;
}

function closeFormModal() 
{
    const modalContainer = document.getElementById('modalContainer');
    modalContainer.style.display = 'none';
}

function deletePortfolio() 
{
    fetch(`deletePortfolio?id=${selectedPortfolioId}`, 
        {
            method: 'GET',
        })
    .then(response => {
        if (!response.ok) 
        {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }
        return response.text();
    })
    .then(() =>{
        let deletedPortfolio = document.querySelector(`.portfolio-pick[data-id="${selectedPortfolioId}"]`);
        if (deletedPortfolio) 
        {
            deletedPortfolio.remove();
            if(document.querySelector('.portfolio-pick') == null)
            {
                showNoPortfolio()
                hideTableIfEmpty();
            }
            else
            {
                let firstPortfolio = document.querySelector('.portfolio-pick');
                if (firstPortfolio) 
                    firstPortfolio.click();  
            }  
        }
    })
    .catch(error => {
        console.error('Fetch error:', error.message);
    });
}

function downloadHistory() 
{
    const historyEndpoint = `downloadHistory?id=${selectedPortfolioId}`;
    fetch(historyEndpoint, {
        method: 'GET',
    })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
        })
        .then(transactions => {
            const csvContent = 'Date,Name,Quantity,Price,Type\n' +
                transactions.map(transaction =>
                    `${transaction.date},${transaction.name},${transaction.quantity},${transaction.price}${transaction.sign} ,${transaction.type}`
                ).join('\n');

            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });

            const link = document.createElement('a');
            if (link.download !== undefined) 
            {
                const url = URL.createObjectURL(blob);
                link.setAttribute('href', url);
                link.setAttribute('download', 'transaction_history.csv');
                link.style.visibility = 'hidden';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            } 
            else 
                console.error('Download functionality not supported in this browser.');
            
        })
        .catch(error => {
            console.error('Error fetching transaction history:', error);
        });
}

function showNoPortfolio()
{
    document.querySelector('.content-space').style.visibility = "hidden";
    document.querySelector('#deletePortfolio').style.visibility = 'hidden';
    document.querySelector('.analysis-space').style.visibility = 'hidden';
}

function showPortfolio()
{
    document.querySelector('.content-space').style.visibility = "visible";
    document.querySelector('#deletePortfolio').style.visibility = 'visible';
    document.querySelector('.analysis-space').style.visibility = 'visible';
}

if(document.querySelector('.portfolio-pick') != null)
{
    document.querySelector('.portfolio-pick').click();
    showPortfolio();
}
else 
{
    selectedPortfolioId = null;
    showNoPortfolio();
}

document.querySelector('.new-portfolio').addEventListener('click', function (event) 
{
    event.preventDefault();
    openFormModal();
});

document.querySelector('#deletePortfolio').addEventListener('click', function (event) 
{
    event.preventDefault();
    deletePortfolio();
    if(!(document.querySelector('.portfolio-pick') != null))
    {
        showNoPortfolio();
    }
});