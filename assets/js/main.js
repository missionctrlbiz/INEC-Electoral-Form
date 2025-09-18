document.addEventListener('DOMContentLoaded', () => {
    // --- ELEMENT SELECTION ---
    const loginForm = document.getElementById('login-form');
    const verificationModal = document.getElementById('verification-modal');
    const verificationForm = document.getElementById('verification-form');
    const modalContent = verificationModal.querySelector('div'); // For animations

    // Buttons
    const loginBtn = document.getElementById('login-btn');
    const verifyBtn = document.getElementById('verify-btn');
    const cancelVerificationBtn = document.getElementById('cancel-verification-btn');

    // Display Areas
    const errorMessageDiv = document.getElementById('error-message');
    const modalErrorMessageDiv = document.getElementById('modal-error-message');
    const phoneEndingSpan = document.getElementById('phone-ending');
    const phoneDigitsInput = document.getElementById('phone_digits');


    // --- HELPER FUNCTIONS ---

    /**
     * Displays an error message in a specified element.
     * @param {HTMLElement} element The div where the message should be shown.
     * @param {string} message The error message to display.
     */
    const displayError = (element, message) => {
        element.textContent = message;
        element.classList.remove('hidden');
    };

    /**
     * Hides an error message element.
     * @param {HTMLElement} element The error div to hide.
     */
    const clearError = (element) => {
        element.textContent = '';
        element.classList.add('hidden');
    };
    
    /**
     * Manages the loading state of a button.
     * @param {HTMLButtonElement} button The button to modify.
     * @param {boolean} isLoading True to show loading state, false to restore.
     * @param {string} defaultText The original text of the button.
     */
    const setButtonLoadingState = (button, isLoading, defaultText = '') => {
        if (isLoading) {
            button.disabled = true;
            button.innerHTML = `
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Processing...
            `;
        } else {
            button.disabled = false;
            button.innerHTML = defaultText;
        }
    };


    // --- MODAL VISIBILITY ---

    const showVerificationModal = () => {
        verificationModal.classList.remove('hidden');
        setTimeout(() => { // Timeout allows the browser to render the element before transitioning
            verificationModal.classList.remove('opacity-0');
            modalContent.classList.remove('opacity-0', 'scale-95');
            phoneDigitsInput.focus(); // Automatically focus the input field
        }, 10);
    };

    const hideVerificationModal = () => {
        verificationModal.classList.add('opacity-0');
        modalContent.classList.add('opacity-0', 'scale-95');
        setTimeout(() => { // Wait for animation to finish before hiding
            verificationModal.classList.add('hidden');
            // Clear input and errors when closing
            phoneDigitsInput.value = '';
            clearError(modalErrorMessageDiv);
        }, 300);
    };


    // --- EVENT LISTENERS ---

    // 1. Handle the initial login form (PU Code and PIN)
    if (loginForm) {
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            clearError(errorMessageDiv);
            setButtonLoadingState(loginBtn, true);

            const formData = new FormData(loginForm);

            try {
                const response = await fetch('core/process_login.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();

                if (data.success) {
                    // Success! Update the modal and show it.
                    phoneEndingSpan.textContent = data.phone_ending || '****';
                    showVerificationModal();
                } else {
                    // Failure! Show the error message.
                    displayError(errorMessageDiv, data.message || 'An unknown error occurred.');
                }
            } catch (error) {
                console.error('Login Fetch Error:', error);
                displayError(errorMessageDiv, 'A network error occurred. Please check your connection and try again.');
            } finally {
                setButtonLoadingState(loginBtn, false, 'Proceed to Verification');
            }
        });
    }

    // 2. Handle the verification modal form (Last 4 digits of phone)
    if (verificationForm) {
        verificationForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            clearError(modalErrorMessageDiv);
            setButtonLoadingState(verifyBtn, true);

            const formData = new FormData(verificationForm);

            try {
                const response = await fetch('core/process_otp.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    // Success! Redirect to the data entry page.
                    verifyBtn.innerHTML = 'Success! Redirecting...';
                    window.location.href = 'data-entry.php';
                } else {
                    // Failure! Show error inside the modal.
                    displayError(modalErrorMessageDiv, data.message || 'Verification failed.');
                    setButtonLoadingState(verifyBtn, false, 'Verify & Sign In');
                }
            } catch (error) {
                console.error('Verification Fetch Error:', error);
                displayError(modalErrorMessageDiv, 'A network error occurred. Please try again.');
                setButtonLoadingState(verifyBtn, false, 'Verify & Sign In');
            }
        });
    }

    // 3. Handle the cancel button in the modal
    if (cancelVerificationBtn) {
        cancelVerificationBtn.addEventListener('click', hideVerificationModal);
    }
});