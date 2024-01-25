const searchInput = document.querySelector(".searchbar");
const typeMappings = 
{
    'Crypto': 'Cryptocurrency',
    'Index' : 'Index',
    'ETF' : 'ETF',
    'Stock' : 'Stock'
};
const choiceBoxes = document.querySelectorAll('.choice-box');
const assetRows = document.querySelectorAll('.asset');

function changeStar(starContainer) 
{
    let star = starContainer.querySelector('#star');
    let assetId = starContainer.closest('.asset').dataset.id;

    if (star.style.color === 'black' || star.style.color === '') {
        star.style.color = "gold";
        addToObserved(assetId);
    } else {
        star.style.color = 'black';
        removeFromObserved(assetId);
    }
}

function addToObserved(assetId) 
{
    fetch(`updateWatchlist?id=${assetId}&action=add`, {
        method: 'GET',
    });
}

function removeFromObserved(assetId) 
{
    fetch(`updateWatchlist?id=${assetId}&action=remove`, {
        method: 'GET',
    });
}

function liveSearch(searchValue) 
{
    let filter = searchValue.toUpperCase();
    const table = document.querySelector(".stock-search");
    const rows = table.querySelectorAll(".asset");

    for (i = 0; i < rows.length; i++) 
    {
        td = rows[i].querySelectorAll("td")[1];
        if (td)
        {
            txtValue = td.textContent || td.innerText;
            if (txtValue.toUpperCase().indexOf(filter) > -1) 
                rows[i].style.display = "";
            else 
                rows[i].style.display = "none";
        }
    }
    hideTableIfEmpty();
}

function hideTableIfEmpty() 
{
    let table = document.querySelector('.stock-search');
    let assets = document.querySelectorAll('.asset');
    let visibleAssets = Array.from(assets).filter(assets => {
        return window.getComputedStyle(assets).getPropertyValue('display') !== 'none';
    });

    if(visibleAssets.length == 0)
        table.style.visibility = 'hidden';
    else
        table.style.visibility = 'visible';
}

function updateStars() 
{
    const table = document.querySelector(".stock-search");
    const rows = table.querySelectorAll(".asset");

    rows.forEach((row) => 
    {
        let star = row.querySelector('#star');
        let assetData = JSON.parse(row.dataset.asset);
        let isInObserved = assetData.observed;
        if (isInObserved) 
            star.style.color = "gold";
        else 
            star.style.color = 'black';
    });
}

function handleTypeFilterClick(selectedType) 
{
    let isSelected = this.classList.contains('selected');

    choiceBoxes.forEach((box) => 
    {
        box.classList.remove('selected');
    });

    if (!isSelected) 
    {
        this.classList.add('selected');
        selectedType = typeMappings[this.textContent.trim()] || null;
    } else 
        selectedType = null;

    assetRows.forEach((row) => 
    {
        let assetType = row.querySelector('td:nth-child(3)').textContent.trim();
        let isVisible = selectedType === null || assetType === selectedType;
        row.style.display = isVisible ? '' : 'none';
    });
    hideTableIfEmpty();
}

choiceBoxes.forEach((choiceBox) => 
{
    choiceBox.onclick = function () 
    {
        const selectedType = this.textContent.trim();
        handleTypeFilterClick.call(this, selectedType);
    };
});

searchInput.addEventListener("input", function () 
{
    liveSearch(this.value);
});

updateStars();