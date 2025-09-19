document.addEventListener('DOMContentLoaded', () => {
    // --- Element Selections ---
    const addPartyBtn = document.getElementById('add-party-btn');
    const responseMessageDiv = document.getElementById('response-message');

    // Add/Edit Modal Elements
    const partyModal = document.getElementById('party-modal');
    const partyModalContent = document.getElementById('party-modal-content');
    const modalCloseBtn = document.getElementById('modal-close-btn');
    const modalCancelBtn = document.getElementById('modal-cancel-btn');
    const partyForm = document.getElementById('party-form');
    const modalTitle = document.getElementById('modal-title');
    const modalSubmitBtn = document.getElementById('modal-submit-btn');
    const modalErrorDiv = document.getElementById('modal-error');
    const logoPreview = document.getElementById('logo-preview');
    const logoInput = document.getElementById('logo');
    
    // Delete Modal Elements
    const deleteModal = document.getElementById('delete-modal');
    const deleteModalContent = document.getElementById('delete-modal-content');
    const deleteCancelBtn = document.getElementById('delete-cancel-btn');
    const deleteForm = document.getElementById('delete-form');
    const partyNameToDeleteSpan = document.getElementById('party-name-to-delete');
    
    // --- Utility Functions ---
    const showResponseMessage = (message, isSuccess) => {
        responseMessageDiv.textContent = message;
        responseMessageDiv.className = `p-4 mb-4 text-sm rounded-lg ${isSuccess ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}`;
        responseMessageDiv.classList.remove('hidden');
        setTimeout(() => responseMessageDiv.classList.add('hidden'), 5000);
    };

    // --- Modal Control Functions ---
    const showModal = (modal, content) => {
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            content.classList.remove('opacity-0', 'scale-95');
        }, 10);
    };

    const hideModal = (modal, content) => {
        modal.classList.add('opacity-0');
        content.classList.add('opacity-0', 'scale-95');
        setTimeout(() => modal.classList.add('hidden'), 300);
    };
    
    const setupAddModal = () => {
        partyForm.reset();
        modalTitle.textContent = 'Add New Party';
        modalSubmitBtn.textContent = 'Add Party';
        document.getElementById('party_id').value = '';
        logoPreview.src = '../assets/images/INEC-Logo.png';
        modalErrorDiv.classList.add('hidden');
        showModal(partyModal, partyModalContent);
    };

    const setupEditModal = async (partyId) => {
        partyForm.reset();
        modalErrorDiv.classList.add('hidden');
        try {
            const response = await fetch(`api_get_party.php?id=${partyId}`);
            if (!response.ok) throw new Error('Network response was not ok');
            const data = await response.json();

            if (data.success) {
                modalTitle.textContent = 'Edit Political Party';
                modalSubmitBtn.textContent = 'Save Changes';
                document.getElementById('party_id').value = data.party.id;
                document.getElementById('name').value = data.party.name;
                document.getElementById('acronym').value = data.party.acronym;
                logoPreview.src = data.party.logo_path ? `../${data.party.logo_path}` : '../assets/images/INEC-Logo.png';
                showModal(partyModal, partyModalContent);
            } else {
                showResponseMessage(data.message, false);
            }
        } catch (error) {
            showResponseMessage('Failed to fetch party data.', false);
        }
    };

    // --- Event Listeners ---
    addPartyBtn.addEventListener('click', setupAddModal);
    modalCloseBtn.addEventListener('click', () => hideModal(partyModal, partyModalContent));
    modalCancelBtn.addEventListener('click', () => hideModal(partyModal, partyModalContent));
    
    logoInput.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (file) {
            logoPreview.src = URL.createObjectURL(file);
        }
    });

    partyForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        modalSubmitBtn.disabled = true;
        modalSubmitBtn.textContent = 'Saving...';
        
        const formData = new FormData(partyForm);
        
        try {
            const response = await fetch('api_manage_party.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();

            if (result.success) {
                hideModal(partyModal, partyModalContent);
                showResponseMessage(result.message, true);
                setTimeout(() => window.location.reload(), 1500); // Reload to see changes
            } else {
                modalErrorDiv.textContent = result.message;
                modalErrorDiv.classList.remove('hidden');
            }
        } catch (error) {
            modalErrorDiv.textContent = 'A network error occurred.';
            modalErrorDiv.classList.remove('hidden');
        } finally {
            modalSubmitBtn.disabled = false;
            // Text will be reset on reload or next modal open
        }
    });
    
    // --- Edit and Delete Button Delegation ---
    document.body.addEventListener('click', (e) => {
        const editBtn = e.target.closest('.edit-btn');
        const deleteBtn = e.target.closest('.delete-btn');

        if (editBtn) {
            const partyId = editBtn.dataset.id;
            setupEditModal(partyId);
        }
        
        if (deleteBtn) {
            document.getElementById('party_id_to_delete').value = deleteBtn.dataset.id;
            partyNameToDeleteSpan.textContent = deleteBtn.dataset.name;
            showModal(deleteModal, deleteModalContent);
        }
    });
    
    deleteCancelBtn.addEventListener('click', () => hideModal(deleteModal, deleteModalContent));
    
    deleteForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const deleteBtn = deleteForm.querySelector('button[type="submit"]');
        deleteBtn.disabled = true;
        deleteBtn.textContent = 'Deleting...';
        
        const formData = new FormData(deleteForm);

        try {
            const response = await fetch('api_delete_party.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();

            if (result.success) {
                hideModal(deleteModal, deleteModalContent);
                showResponseMessage(result.message, true);
                document.getElementById(`party-row-${formData.get('party_id')}`).remove();
            } else {
                 hideModal(deleteModal, deleteModalContent);
                 showResponseMessage(result.message, false);
            }
        } catch (error) {
            hideModal(deleteModal, deleteModalContent);
            showResponseMessage('A network error occurred.', false);
        } finally {
             deleteBtn.disabled = false;
             deleteBtn.textContent = 'Yes, Delete';
        }
    });
});