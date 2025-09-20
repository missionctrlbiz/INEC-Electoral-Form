document.addEventListener('DOMContentLoaded', () => {
    // --- ELEMENT SELECTION ---
    const loginForm = document.getElementById('login-form');
    const verificationModal = document.getElementById('verification-modal');
    const verificationForm = document.getElementById('verification-form');
    const modalContent = verificationModal.querySelector('div');

    const loginBtn = document.getElementById('login-btn');
    const verifyBtn = document.getElementById('verify-btn');
    const cancelVerificationBtn = document.getElementById('cancel-verification-btn');

    const errorMessageDiv = document.getElementById('error-message');
    const modalErrorMessageDiv = document.getElementById('modal-error-message');
    const phoneDigitsInput = document.getElementById('phone_digits');
    const phoneEndingSpan = document.querySelector('#verification-modal p strong'); // For displaying ****1234


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

    // --- MODAL VISIBILITY ---
    const showVerificationModal = () => {
        verificationModal.classList.remove('hidden');
        setTimeout(() => {
            verificationModal.classList.remove('opacity-0');
            modalContent.classList.remove('opacity-0', 'scale-95');
            phoneDigitsInput.focus();
        }, 10);
    };
    const hideVerificationModal = () => {
        verificationModal.classList.add('opacity-0');
        modalContent.classList.add('opacity-0', 'scale-95');
        setTimeout(() => {
            verificationModal.classList.add('hidden');
            phoneDigitsInput.value = '';
            clearError(modalErrorMessageDiv);
        }, 300);
    };

    // --- EVENT LISTENERS ---
    // STEP 1: Handle PU Code and PIN submission
    loginForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        clearError(errorMessageDiv);
        setButtonLoadingState(loginBtn, true, 'Proceed to Verification');

        const formData = new FormData(loginForm);
        formData.append('step', '1'); // Tell the backend this is step 1

        try {
            const response = await fetch('core/auth_clerk.php', { method: 'POST', body: formData });
            const data = await response.json();

            if (data.success) {
                // If step 1 is successful, show the modal
                if (phoneEndingSpan && data.phone_ending) {
                    phoneEndingSpan.textContent = `****${data.phone_ending}`;
                }
                showVerificationModal();
            } else {
                displayError(errorMessageDiv, data.message || 'An unknown error occurred.');
            }
        } catch (error) {
            displayError(errorMessageDiv, 'A network error occurred. Please check your connection and try again.');
        } finally {
            setButtonLoadingState(loginBtn, false, 'Proceed to Verification');
        }
    });

    // STEP 2: Handle phone digit verification
    verificationForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        clearError(modalErrorMessageDiv);
        setButtonLoadingState(verifyBtn, true, 'Verify & Sign In');

        const formData = new FormData(verificationForm);
        formData.append('step', '2'); // Tell the backend this is step 2

        try {
            const response = await fetch('core/auth_clerk.php', { method: 'POST', body: formData });
            const data = await response.json();

            if (data.success) {
                // If step 2 is successful, redirect
                verifyBtn.innerHTML = 'Success! Redirecting...';
                window.location.href = 'data-entry.php';
            } else {
                displayError(modalErrorMessageDiv, data.message || 'Verification failed.');
            }
        } catch (error) {
            displayError(modalErrorMessageDiv, 'A network error occurred. Please try again.');
        } finally {
            setButtonLoadingState(verifyBtn, false, 'Verify & Sign In');
        }
    });

    cancelVerificationBtn.addEventListener('click', hideVerificationModal);
});