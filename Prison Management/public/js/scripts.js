document.getElementById('loginForm').addEventListener('submit', function(event) {
    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value.trim();
    const errorMessage = document.getElementById('errorMessage');

    // Check for URL error parameter on page load
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('error')) {
        errorMessage.textContent = urlParams.get('error') === 'invalid' ? 'Invalid username or password.' : 
                                 urlParams.get('error') === 'db_failed' ? 'Database connection failed.' : 
                                 'Please fill in all fields.';
    } else if (urlParams.has('db_success') && urlParams.get('db_success') === 'true') {
        alert("db connection success"); // Show alert for successful connection
    }

    if (!username || !password) {
        errorMessage.textContent = 'Please fill in all fields.';
        event.preventDefault();
    } else {
        errorMessage.textContent = '';
    }
});