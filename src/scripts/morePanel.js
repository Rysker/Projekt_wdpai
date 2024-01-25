const statusMappings = 
{
    'Verified': '2',
    'Blocked' : '1',
};

let privileges;

function getPrivileges() 
{
    return fetch('getPrivileges')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            return data.privileges;
        })
        .catch(error => {
            console.error('Error fetching privileges:', error);
            return [];
        });
}

function hasPrivilege(permission) 
{
    return privileges.includes(permission);
}

function updateButtonStatus() 
{
    const blockUserButton = document.querySelector('#userStatus');
    const changeCurrencyButton = document.querySelector('#changeCurrency');

    if (hasPrivilege('Administrator')) 
    {
        blockUserButton.style.pointerEvents = 'auto';
        blockUserButton.style.visibility = 'visible';
    }

    if (hasPrivilege('User')) 
    {
        changeCurrencyButton.style.pointerEvents = 'auto';
        changeCurrencyButton.style.visibility = 'visible';
    }
}

function userBlock() 
{
     fetch('getUsers')
     .then(response => response.json())
     .then(users => {
         let modalContainer = document.querySelector('#modalContainer');
         let modalForm = document.querySelector('#modalForm');
         fetch('public/forms/blockUser.php')
             .then(response => response.text())
             .then(formHTML => {
                 modalForm.innerHTML = formHTML;
                 modalContainer.style.display = 'flex';

                 const dataList = modalContainer.querySelector('#input_user');
                 
                 users.forEach(user => {
                    const option = document.createElement('option');
                    option.value = user.id_user;
                    option.textContent = user.id_user + '. ' + user.email + ' ' + user.status;
                    dataList.appendChild(option);
                });

                $('#input_user').select2();

                updateInputTypeDropdown(users);

                $('#input_user').on('change', function () {
                    updateInputTypeDropdown(users);
                });
             })
             .catch(error => {
                 console.error('Error fetching blockUser form:', error);
             });
     })
     .catch(error => {
         console.error('Error fetching users:', error);
     });
}

function changeCurrency() 
{
     fetch('getCurrencies')
     .then(response => response.json())
     .then(currencies => {
         let modalContainer = document.querySelector('#modalContainer');
         let modalForm = document.querySelector('#modalForm');
         fetch('public/forms/changeCurrency.php')
             .then(response => response.text())
             .then(formHTML => {
                 modalForm.innerHTML = formHTML;
                 modalContainer.style.display = 'flex';

                 const dataList = modalContainer.querySelector('#input_currency');
                 
                 currencies.forEach(currency => {
                    const option = document.createElement('option');
                    option.value = currency.id_currency;
                    option.textContent = currency.currency_code;
                    dataList.appendChild(option);
                });

                $('#input_currency').select2();
             })
             .catch(error => {
                 console.error('Error fetching blockUser form:', error);
             });
     })
     .catch(error => {
         console.error('Error fetching users:', error);
     });
}
function closeFormModal() 
{
    const modalContainer = document.getElementById('modalContainer');
    modalContainer.style.display = 'none';
}

function updateInputTypeDropdown(users) {
    $('#input_type option').prop('disabled', false);
    const selectedUserId = $('#input_user').val();
    const selectedUser = users.find(user => user.id_user === selectedUserId);

    if (selectedUser) {
        const userStatus = statusMappings[selectedUser.status.trim()] || null;

        $('#input_type option').each(function () {
            let optionValue = $(this).val();
            if (optionValue !== userStatus)
                $(this).prop('disabled', true);
        });

        $('#input_type option:enabled:first').prop('selected', true);
    }
}

window.onload = function () 
{
    getPrivileges()
        .then(data => {
            privileges = data;
            updateButtonStatus();
        });
};