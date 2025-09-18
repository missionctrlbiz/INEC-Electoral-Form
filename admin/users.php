<?php
require_once '../core/db_connect.php';
// require_admin(); // Protect the page

// Fetch all users from the database to display in the table
$users_query = $conn->query("SELECT id, full_name, email, role, status FROM users ORDER BY role, full_name");

require_once '../includes/admin_header.php';
?>

<header class="flex justify-between items-center mb-8">
    <h1 class="font-display text-3xl font-bold text-gray-800">User Management</h1>
    <a href="#" class="bg-inec-green text-white font-semibold py-2 px-4 rounded-md hover:opacity-90 transition">
        <i class="bi bi-plus-circle mr-2"></i> Add New User
    </a>
</header>

<div class="bg-white rounded-lg shadow overflow-x-auto">
    <table class="w-full text-sm text-left text-gray-500">
        <thead class="text-xs text-gray-700 uppercase bg-gray-100">
            <tr>
                <th class="px-6 py-3">User Name</th>
                <th class="px-6 py-3">Email</th>
                <th class="px-6 py-3">Role</th>
                <th class="px-6 py-3">Status</th>
                <th class="px-6 py-3 text-center">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($users_query && $users_query->num_rows > 0): ?>
                <?php while($user = $users_query->fetch_assoc()): ?>
                    <tr class="bg-white border-b hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium text-slate-900"><?php echo htmlspecialchars($user['full_name']); ?></td>
                        <td class="px-6 py-4"><?php echo htmlspecialchars($user['email']); ?></td>
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
                            <button class="text-gray-500 hover:text-gray-700 p-1" title="Edit User">
                                <i class="bi bi-pencil-fill text-lg"></i>
                            </button>
                            <button class="text-red-600 hover:text-red-800 p-1" title="Delete User">
                                <i class="bi bi-trash-fill text-lg"></i>
                            </button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr class="bg-white border-b">
                    <td colspan="5" class="px-6 py-4 text-center text-slate-500">No users found in the database.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
    // This script makes sure the correct sidebar link is highlighted
    document.querySelectorAll('.nav-link').forEach(link => {
        if (link.href.includes('users.php')) {
            link.classList.add('active');
        } else {
            link.classList.remove('active');
        }
    });
</script>

<?php require_once '../includes/admin_footer.php'; ?>