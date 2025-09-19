document.addEventListener('DOMContentLoaded', () => {
    // --- Get All Elements ---
    const userModal = document.getElementById('user-modal');
    const deleteModal = document.getElementById('delete-modal');
    const viewModal = document.getElementById('view-modal');
    
    const userForm = document.getElementById('user-form');
    const modalTitle = document.getElementById('modal-title');
    const userIdInput = document.getElementById('user_id');
    const formSubmitBtn = document.getElementById('form-submit-btn');

    // --- Helper functions to show/hide modals ---
    const showModal = (modal) => modal.classList.remove('hidden');
    const hideModal = (modal) => modal.classList.add('hidden');

    // --- Dynamic Form Fields Logic ---
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
            
            // Fetch user data from the server
            const response = await fetch(`api_users.php?action=get&user_id=${userId}`);
            const data = await response.json();

            if (data.success) {
                const user = data.user;
                // Populate the form
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
                alert(data.message);
            }
        });
    });
    
    // --- Show "View User" Modal ---
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

    // --- Show "Delete User" Modal ---
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
        formData.append('action', 'save'); // Add action for the API

        const response = await fetch('api_users.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();

        if (result.success) {
            hideModal(userModal);
            window.location.reload();
        } else {
            alert(`Error: ${result.message}`);
        }
    });

    // --- Handle DELETE CONFIRMATION ---
    document.getElementById('confirm-delete-btn').addEventListener('click', async () => {
        if (userIdToDelete) {
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('user_id', userIdToDelete);

            const response = await fetch('api_users.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();

            if (result.success) {
                hideModal(deleteModal);
                window.location.reload();
            } else {
                alert(`Error: ${result.message}`);
            }
        }
    });

    // --- Close Modal Buttons ---
    document.getElementById('modal-close-btn').addEventListener('click', () => hideModal(userModal));
    document.getElementById('view-modal-close-btn').addEventListener('click', () => hideModal(viewModal));
    document.getElementById('cancel-delete-btn').addEventListener('click', () => hideModal(deleteModal));
});