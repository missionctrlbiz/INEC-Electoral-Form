document.addEventListener('DOMContentLoaded', () => {
    // --- Get All Elements ---
    const userModal = document.getElementById('user-modal');
    const deleteModal = document.getElementById('delete-modal');
    const viewModal = document.getElementById('view-modal');
    const notificationModal = document.getElementById('notification-modal'); // New modal
    
    const userForm = document.getElementById('user-form');
    const modalTitle = document.getElementById('modal-title');
    const userIdInput = document.getElementById('user_id');
    const formSubmitBtn = document.getElementById('form-submit-btn');

    // --- Helper functions to show/hide modals ---
    const showModal = (modal) => modal.classList.remove('hidden');
    const hideModal = (modal) => modal.classList.add('hidden');

    // --- START: NEW NOTIFICATION MODAL LOGIC ---
    const notificationIcon = document.getElementById('notification-icon');
    const notificationTitle = document.getElementById('notification-title');
    const notificationMessage = document.getElementById('notification-message');
    const notificationCloseBtn = document.getElementById('notification-close-btn');

    /**
     * Displays a custom notification modal.
     * @param {string} type - 'success' or 'error'.
     * @param {string} title - The title of the message.
     * @param {string} message - The main message content.
     */
    function showNotification(type, title, message) {
        // Set content
        notificationTitle.textContent = title;
        notificationMessage.textContent = message;

        // Set styles based on type
        if (type === 'success') {
            notificationIcon.innerHTML = `<i class="bi bi-check2-circle text-6xl text-green-600"></i>`;
            notificationIcon.className = 'w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-5';
            notificationCloseBtn.className = 'bg-inec-green text-white font-semibold w-full py-3 rounded-lg';
        } else { // error
            notificationIcon.innerHTML = `<i class="bi bi-x-circle text-6xl text-red-600"></i>`;
            notificationIcon.className = 'w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-5';
            notificationCloseBtn.className = 'bg-inec-red text-white font-semibold w-full py-3 rounded-lg';
        }
        showModal(notificationModal);
    }

    notificationCloseBtn.addEventListener('click', () => hideModal(notificationModal));
    // --- END: NEW NOTIFICATION MODAL LOGIC ---

    // --- Dynamic Form Fields Logic (Unchanged) ---
    const roleSelect = document.getElementById('role');
    const clerkFields = document.getElementById('clerk-fields');
    const adminFields = document.getElementById('admin-fields');
    function toggleRoleFields() {
        const isClerk = roleSelect.value === 'clerk';
        clerkFields.style.display = isClerk ? '' : 'none';
        adminFields.style.display = isClerk ? 'none' : '';
        document.getElementById('polling_unit_id').required = isClerk;
    }
    roleSelect.addEventListener('change', toggleRoleFields);


    // --- Show "Add User" Modal ---
    document.getElementById('add-user-btn').addEventListener('click', () => {
        userForm.reset();
        userIdInput.value = '';
        modalTitle.textContent = 'Add New User';
        formSubmitBtn.textContent = 'Create User';
        document.getElementById('pin').placeholder = "Enter 4-Digit PIN (Required)";
        document.getElementById('password').placeholder = "Enter Password (Required)";
        toggleRoleFields();
        showModal(userModal);
    });

    // --- Show "Edit User" Modal ---
    document.querySelectorAll('.edit-user-btn').forEach(button => {
        button.addEventListener('click', async () => {
            const userId = button.dataset.userId;
            const response = await fetch(`api_users.php?action=get&user_id=${userId}`);
            const data = await response.json();

            if (data.success) {
                const user = data.user;
                userForm.reset();
                userIdInput.value = user.id;
                modalTitle.textContent = 'Edit User';
                formSubmitBtn.textContent = 'Save Changes';
                document.getElementById('full_name').value = user.full_name;
                document.getElementById('email').value = user.email;
                document.getElementById('phone_number').value = user.phone_number;
                document.getElementById('role').value = user.role;
                document.getElementById('status').value = user.status;
                document.getElementById('polling_unit_id').value = user.polling_unit_id || '';
                document.getElementById('pin').placeholder = "Leave blank to keep current PIN";
                document.getElementById('password').placeholder = "Leave blank to keep current password";
                
                toggleRoleFields();
                showModal(userModal);
            } else {
                // MODIFICATION: Use custom notification instead of alert()
                showNotification('error', 'Fetch Error', data.message);
            }
        });
    });
    
    // --- Show "View User" Modal (Unchanged) ---
    document.querySelectorAll('.view-user-btn').forEach(button => {
        button.addEventListener('click', async () => {
            const userId = button.dataset.userId;
            const response = await fetch(`api_users.php?action=get&user_id=${userId}`);
            const data = await response.json();
            if (data.success) {
                const user = data.user;
                document.getElementById('view-modal-title').textContent = `Details for ${user.full_name}`;
                
                let statusBadge = user.status === 'active' 
                    ? `<span class="bg-green-100 text-green-800 text-sm font-medium px-3 py-1 rounded-full">Active</span>`
                    : `<span class="bg-red-100 text-red-800 text-sm font-medium px-3 py-1 rounded-full">Inactive</span>`;
                let roleBadge = user.role === 'admin' 
                    ? `<span class="bg-blue-100 text-blue-800 text-sm font-medium px-3 py-1 rounded-full">Admin</span>`
                    : `<span class="bg-slate-100 text-slate-800 text-sm font-medium px-3 py-1 rounded-full">Clerk</span>`;
                let puInfo = user.role === 'clerk' ? `
                    <div class="flex justify-between py-2 border-b">
                        <span class="font-medium text-slate-500">Polling Unit</span>
                        <span class="font-semibold text-slate-800">${user.pu_name || 'Not Assigned'}</span>
                    </div>` : '';

                document.getElementById('view-modal-body').innerHTML = `
                    <div class="space-y-3">
                        <div class="flex justify-between py-2 border-b"><span class="font-medium text-slate-500">Full Name</span> <span class="font-semibold text-slate-800">${user.full_name}</span></div>
                        <div class="flex justify-between py-2 border-b"><span class="font-medium text-slate-500">Email</span> <span class="font-semibold text-slate-800">${user.email}</span></div>
                        <div class="flex justify-between py-2 border-b"><span class="font-medium text-slate-500">Phone</span> <span class="font-semibold text-slate-800">${user.phone_number}</span></div>
                        ${puInfo}
                        <div class="flex justify-between py-2 border-b"><span class="font-medium text-slate-500">Role</span> <div>${roleBadge}</div></div>
                        <div class="flex justify-between py-2"><span class="font-medium text-slate-500">Status</span> <div>${statusBadge}</div></div>
                    </div>
                `;
                showModal(viewModal);
            }
        });
    });

    // --- Show "Delete User" Modal (Unchanged) ---
    let userIdToDelete = null;
    document.querySelectorAll('.delete-user-btn').forEach(button => {
        button.addEventListener('click', () => {
            userIdToDelete = button.dataset.userId;
            showModal(deleteModal);
        });
    });

    // --- Handle FORM SUBMISSION (Create/Update) ---
    userForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(userForm);
        formData.append('action', 'save');

        const response = await fetch('api_users.php', { method: 'POST', body: formData });
        const result = await response.json();

        if (result.success) {
            hideModal(userModal);
            // MODIFICATION: Use custom notification and only reload page on success
            showNotification('success', 'Success!', result.message);
            notificationCloseBtn.addEventListener('click', () => {
                location.reload();
            }, { once: true }); // Reload only after user clicks "OK"
        } else {
            // MODIFICATION: Use custom notification for failure
            showNotification('error', 'Save Failed', result.message);
        }
    });

    // --- Handle DELETE CONFIRMATION ---
    document.getElementById('confirm-delete-btn').addEventListener('click', async () => {
        if (userIdToDelete) {
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('user_id', userIdToDelete);

            const response = await fetch('api_users.php', { method: 'POST', body: formData });
            const result = await response.json();

            if (result.success) {
                hideModal(deleteModal);
                 // MODIFICATION: Use custom notification and only reload page on success
                showNotification('success', 'Success!', result.message);
                notificationCloseBtn.addEventListener('click', () => {
                    location.reload();
                }, { once: true });
            } else {
                 // MODIFICATION: Use custom notification for failure
                hideModal(deleteModal); // Hide the delete confirmation first
                showNotification('error', 'Delete Failed', result.message);
            }
        }
    });

    // --- Close Modal Buttons (Unchanged) ---
    document.getElementById('modal-close-btn').addEventListener('click', () => hideModal(userModal));
    document.getElementById('view-modal-close-btn').addEventListener('click', () => hideModal(viewModal));
    document.getElementById('cancel-delete-btn').addEventListener('click', () => hideModal(deleteModal));
});