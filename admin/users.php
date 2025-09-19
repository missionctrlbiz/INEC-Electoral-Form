<?php
require_once '../core/db_connect.php';
// Protect the page: Ensure only logged-in admins can access it.
require_admin();

// Fetch all users from the database to display in the table
$users_query = $conn->query("SELECT u.id, u.full_name, u.email, u.role, u.status, pu.name as pu_name 
                             FROM users u 
                             LEFT JOIN polling_units pu ON u.polling_unit_id = pu.id 
                             ORDER BY u.role, u.full_name");

// Fetch all polling units for the dropdown in the modal
$polling_units_query = $conn->query("SELECT id, name, pu_code FROM polling_units ORDER BY name ASC");
$polling_units = $polling_units_query->fetch_all(MYSQLI_ASSOC);
?>

<?php require_once '../includes/admin_footer.php'; ?>
<?php require_once '../includes/admin_header.php'; ?>

<main class="flex-1 p-6 bg-slate-100">
    <header class="flex justify-between items-center mb-6">
        <h1 class="font-display text-3xl font-bold text-slate-900">User Management</h1>
        <button id="add-user-btn" class="bg-inec-green text-white font-semibold py-2 px-4 rounded-lg hover:opacity-90 transition shadow">
            <i class="bi bi-plus-circle mr-2"></i> Add New User
        </button>
    </header>

    <div class="bg-white rounded-xl shadow-lg overflow-x-auto">
        <table class="w-full text-sm text-left text-slate-600">
            <thead class="text-xs text-slate-700 uppercase bg-slate-100">
                <tr>
                    <th class="px-6 py-3">User Name</th>
                    <th class="px-6 py-3">Email / Polling Unit</th>
                    <th class="px-6 py-3">Role</th>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3 text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($users_query && $users_query->num_rows > 0): ?>
                    <?php while($user = $users_query->fetch_assoc()): ?>
                        <tr class="bg-white border-b hover:bg-slate-50">
                            <td class="px-6 py-4 font-bold text-slate-800"><?php echo htmlspecialchars($user['full_name']); ?></td>
                            <td class="px-6 py-4">
                                <?php echo htmlspecialchars($user['email']); ?>
                                <?php if ($user['role'] === 'clerk' && $user['pu_name']): ?>
                                    <span class="block text-xs text-slate-500"><?php echo htmlspecialchars($user['pu_name']); ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <?php if ($user['role'] === 'admin'): ?>
                                    <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded-full">Admin</span>
                                <?php else: ?>
                                    <span class="bg-slate-100 text-slate-800 text-xs font-medium px-2.5 py-0.5 rounded-full">Clerk</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <?php if ($user['status'] === 'active'): ?>
                                    <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded-full">Active</span>
                                <?php else: ?>
                                    <span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded-full">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 space-x-2 text-center">
                                <button class="view-user-btn text-blue-600 hover:text-blue-800 p-1" title="View User" data-user-id="<?php echo $user['id']; ?>">
                                    <i class="bi bi-eye-fill text-lg"></i>
                                </button>
                                <button class="edit-user-btn text-slate-600 hover:text-slate-800 p-1" title="Edit User" data-user-id="<?php echo $user['id']; ?>">
                                    <i class="bi bi-pencil-fill text-lg"></i>
                                </button>
                                <button class="delete-user-btn text-red-600 hover:text-red-800 p-1" title="Delete User" data-user-id="<?php echo $user['id']; ?>">
                                    <i class="bi bi-trash-fill text-lg"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr class="bg-white border-b"><td colspan="5" class="px-6 py-4 text-center text-slate-500">No users found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<!-- Add/Edit User Modal -->
<div id="user-modal" class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50 hidden">
    <div class="bg-slate-50 rounded-xl shadow-2xl w-full max-w-lg max-h-[90vh] flex flex-col">
        <header class="p-4 border-b bg-white rounded-t-xl flex justify-between items-center">
            <h3 id="modal-title" class="font-display font-bold text-xl text-slate-800">Add New User</h3>
            <button id="modal-close-btn" class="text-slate-500 hover:text-slate-800 text-2xl">&times;</button>
        </header>
        <form id="user-form"> <!-- The form tag should wrap the content and the submit button -->
            <div class="p-6 overflow-y-auto">
                <input type="hidden" name="user_id" id="user_id">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label for="full_name" class="block text-sm font-medium text-slate-700 mb-1">Full Name</label>
                        <input type="text" name="full_name" id="full_name" class="w-full p-2 border border-slate-300 rounded-md" required>
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-slate-700 mb-1">Email Address</label>
                        <input type="email" name="email" id="email" class="w-full p-2 border border-slate-300 rounded-md" required>
                    </div>
                    <div>
                        <label for="phone_number" class="block text-sm font-medium text-slate-700 mb-1">Phone Number</label>
                        <input type="tel" name="phone_number" id="phone_number" class="w-full p-2 border border-slate-300 rounded-md" required>
                    </div>
                    <div>
                        <label for="role" class="block text-sm font-medium text-slate-700 mb-1">Role</label>
                        <select name="role" id="role" class="w-full p-2 border border-slate-300 rounded-md" required>
                            <option value="clerk">Clerk</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                     <div>
                        <label for="status" class="block text-sm font-medium text-slate-700 mb-1">Status</label>
                        <select name="status" id="status" class="w-full p-2 border border-slate-300 rounded-md" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    
                    <div id="clerk-fields" class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="polling_unit_id" class="block text-sm font-medium text-slate-700 mb-1">Assigned Polling Unit</label>
                            <select name="polling_unit_id" id="polling_unit_id" class="w-full p-2 border border-slate-300 rounded-md">
                                <option value="">Select a Polling Unit...</option>
                                <?php foreach($polling_units as $pu): ?>
                                <option value="<?php echo $pu['id']; ?>"><?php echo htmlspecialchars($pu['name'] . " ({$pu['pu_code']})"); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                         <div>
                            <label for="pin" class="block text-sm font-medium text-slate-700 mb-1">4-Digit PIN</label>
                            <input type="password" name="pin" id="pin" class="w-full p-2 border border-slate-300 rounded-md" maxlength="4" placeholder="Enter or update PIN">
                        </div>
                    </div>
                    
                    <div id="admin-fields" class="md:col-span-2 hidden">
                         <label for="password" class="block text-sm font-medium text-slate-700 mb-1">Password</label>
                         <input type="password" name="password" id="password" class="w-full p-2 border border-slate-300 rounded-md" placeholder="Enter or update password">
                    </div>
                </div>
            </div>
            <footer class="p-4 bg-white border-t rounded-b-xl flex justify-end">
                 <!-- NOTE: The submit button MUST be inside the <form> tags -->
                 <button type="submit" id="form-submit-btn" class="bg-inec-green text-white font-semibold py-2 px-6 rounded-lg hover:opacity-90">Save User</button>
            </footer>
        </form> <!-- ****** THE MISSING CLOSING TAG IS HERE ****** -->
    </div>
</div>

<!-- View User Modal (No changes needed) -->
<div id="view-modal" class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg">
        <header class="p-4 border-b flex justify-between items-center">
            <h3 id="view-modal-title" class="font-display font-bold text-xl text-slate-800">User Details</h3>
            <button id="view-modal-close-btn" class="text-slate-500 hover:text-slate-800 text-2xl">&times;</button>
        </header>
        <div id="view-modal-body" class="p-6"></div>
    </div>
</div>

<!-- Delete Confirmation Modal (No changes needed) -->
<div id="delete-modal" class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-xl shadow-2xl p-8 text-center w-full max-w-md">
        <h2 class="font-display font-bold text-2xl text-slate-800 mb-2">Are you sure?</h2>
        <p class="text-slate-600 mb-8">This action cannot be undone. All data associated with this user will be permanently deleted.</p>
        <div class="flex justify-center gap-4">
            <button id="cancel-delete-btn" type="button" class="bg-slate-200 font-semibold w-full py-3 rounded-lg">Cancel</button>
            <button id="confirm-delete-btn" class="bg-inec-red text-white font-semibold w-full py-3 rounded-lg">Yes, Delete</button>
        </div>
    </div>
</div>
<!-- Notification Modal (for Success/Error messages) -->
<div id="notification-modal" class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-[60] hidden">
    <div class="bg-white rounded-xl shadow-2xl p-8 text-center w-full max-w-sm">
        <div id="notification-icon" class="w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-5">
            <!-- Icon will be set by JS -->
        </div>
        <h2 id="notification-title" class="font-display font-bold text-2xl text-slate-800 mb-2"></h2>
        <p id="notification-message" class="text-slate-600 mb-8"></p>
        <button id="notification-close-btn" class="bg-slate-700 text-white font-semibold w-full py-3 rounded-lg">OK</button>
    </div>
</div>

<script src="../assets/js/users.js"></script>

<?php require_once '../includes/admin_footer.php'; ?>