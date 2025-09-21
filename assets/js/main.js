document.addEventListener('DOMContentLoaded', () => {
    // --- ELEMENT SELECTION ---
    const loginForm = document.getElementById('login-form');
    const loginBtn = document.getElementById('login-btn');
    const errorMessageDiv = document.getElementById('error-message');

    // --- HELPER FUNCTIONS ---
    const displayError = (element, message) => {
        element.textContent = message;
        element.classList.remove('hidden');
    };
    const clearError = (element) => {
        element.textContent = '';
        element.classList.add('hidden');
    };
    const setButtonLoadingState = (button, isLoading, defaultText = '') => {
        if (isLoading) {
            button.disabled = true;
            button.innerHTML = `<svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Processing...`;
        } else {
            button.disabled = false;
            button.innerHTML = defaultText;
        }
    };

    // --- EVENT LISTENER FOR THE LOGIN FORM ---
    if (loginForm) {
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            clearError(errorMessageDiv);
            setButtonLoadingState(loginBtn, true, 'Sign In');

            const formData = new FormData(loginForm);

            try {
                // Call the single, unified authentication script
                const response = await fetch('core/auth_clerk.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();

                if (data.success) {
                    // --- SUCCESS: REDIRECT IMMEDIATELY ---
                    loginBtn.innerHTML = 'Success! Redirecting...';
                    window.location.href = 'data-entry.php';
                } else {
                    // Failure: Show the error message from the server
                    displayError(errorMessageDiv, data.message || 'An unknown error occurred.');
                    setButtonLoadingState(loginBtn, false, 'Sign In');
                }
            } catch (error) {
                console.error('Login Fetch Error:', error);
                displayError(errorMessageDiv, 'A network error occurred. Please check your connection and try again.');
                setButtonLoadingState(loginBtn, false, 'Sign In');
            }
        });
    }
});