function hideIfEmpty()
{
    let workspace = document.querySelector('.workspace');
    let stars = document.querySelector('#star');
    if(stars == null)
    {
        document.querySelector('.watchlist').style.visibility = 'hidden';
        let messageDiv = document.querySelector('#message');
        if (!messageDiv) 
        {
            messageDiv = document.createElement('div');
            messageDiv.id = 'message';
            messageDiv.style.display = 'flex';
            messageDiv.style.justifyContent = 'center';
            messageDiv.style.fontSize = '300%';
            messageDiv.textContent = 'Your watchlist is empty!';
            workspace.appendChild(messageDiv);
        }
    }
}

document.querySelectorAll('.delete-link').forEach(function(deleteLink) 
{
    deleteLink.addEventListener('click', function(event) 
    {
        event.preventDefault();

        let assetId = this.closest('.asset').dataset.id;

        fetch(`deleteObserved?id=${assetId}`, 
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
        .then(( )=> {
            let deletedRow = document.querySelector(`tr[data-id="${assetId}"]`);
            if (deletedRow) {
                deletedRow.remove();
            }
            hideIfEmpty();
        })
        .catch(error => {
            console.error('Fetch error:', error.message);
        });
    });
});

window.onload = function () 
{
    hideIfEmpty();
};