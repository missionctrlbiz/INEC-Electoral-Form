<?php
require_once '../core/db_connect.php';
// Security: Ensure only admins can access this page.
require_admin();

// Fetch all parties for display.
$parties_query = $conn->query("SELECT id, name, acronym, logo_path FROM political_parties ORDER BY acronym");

require_once '../includes/admin_header.php';
?>
<main class="flex-1 p-6 bg-slate-100">
    <header class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
        <div>
            <h1 class="font-display text-3xl font-bold text-slate-900">Political Party Management</h1>
        </div>
        <button id="add-party-btn" class="bg-inec-green text-white font-semibold py-2 px-4 rounded-lg hover:opacity-90 transition mt-4 md:mt-0 shadow-sm">
            <i class="bi bi-plus-circle mr-2"></i> Add New Party
        </button>
    </header>

    <div id="response-message" class="hidden p-4 mb-4 text-sm rounded-lg" role="alert"></div>

    <div class="bg-white rounded-xl shadow-lg overflow-x-auto">
        <table class="w-full text-sm text-left text-slate-600">
            <thead class="text-xs text-slate-700 uppercase bg-slate-100">
                <tr>
                    <th class="px-6 py-3">Logo</th>
                    <th class="px-6 py-3">Party Name</th>
                    <th class="px-6 py-3">Acronym</th>
                    <th class="px-6 py-3 text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($parties_query && $parties_query->num_rows > 0): ?>
                    <?php while($party = $parties_query->fetch_assoc()): ?>
                        <tr id="party-row-<?php echo $party['id']; ?>" class="bg-white border-b hover:bg-slate-50">
                            <td class="px-6 py-4">
                                <img src="../<?php echo htmlspecialchars($party['logo_path'] ?? 'assets/images/parties/party.png'); ?>" alt="<?php echo htmlspecialchars($party['acronym']); ?>" class="h-10 w-10 object-contain">
                            </td>
                            <td class="px-6 py-4 font-medium text-slate-900"><?php echo htmlspecialchars($party['name']); ?></td>
                            <td class="px-6 py-4"><?php echo htmlspecialchars($party['acronym']); ?></td>
                            <td class="px-6 py-4 space-x-2 text-center">
                                <button class="edit-btn text-slate-600 hover:text-inec-green p-1" title="Edit Party"
                                        data-id="<?php echo $party['id']; ?>">
                                    <i class="bi bi-pencil-square text-lg"></i>
                                </button>
                                <button class="delete-btn text-slate-600 hover:text-inec-red p-1" title="Delete Party"
                                        data-id="<?php echo $party['id']; ?>"
                                        data-name="<?php echo htmlspecialchars($party['name']); ?>">
                                    <i class="bi bi-trash-fill text-lg"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                     <tr><td colspan="4" class="text-center py-10 text-slate-500">No political parties found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<!-- Add/Edit Party Modal -->
<div id="party-modal" class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50 hidden transition-opacity duration-300 opacity-0">
    <div id="party-modal-content" class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-lg transform transition-all duration-300 scale-95 opacity-0">
        <div class="flex justify-between items-center border-b pb-3 mb-4">
            <h3 id="modal-title" class="font-display font-bold text-xl text-slate-800">Add New Party</h3>
            <button id="modal-close-btn" class="text-slate-500 hover:text-slate-800 text-2xl">&times;</button>
        </div>
        
        <div id="modal-error" class="hidden bg-red-100 border-l-4 border-red-500 text-red-700 p-3 mb-4 text-sm"></div>

        <form id="party-form" enctype="multipart/form-data">
            <input type="hidden" name="party_id" id="party_id">
            <div class="space-y-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-slate-700 mb-1">Party Name</label>
                    <input type="text" id="name" name="name" required class="w-full p-2 border border-slate-300 rounded-md focus:ring-2 focus:ring-inec-green">
                </div>
                <div>
                    <label for="acronym" class="block text-sm font-medium text-slate-700 mb-1">Acronym</label>
                    <input type="text" id="acronym" name="acronym" required class="w-full p-2 border border-slate-300 rounded-md focus:ring-2 focus:ring-inec-green">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Party Logo</label>
                    <div class="flex items-center gap-4">
                        <img id="logo-preview" src="../assets/images/parties/party.png" class="h-16 w-16 object-contain border p-1 rounded-md bg-slate-50">
                        <input type="file" id="logo" name="logo" accept="image/png, image/jpeg, image/webp" class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-inec-green file:text-white hover:file:opacity-90">
                    </div>
                    <p class="text-xs text-slate-500 mt-1">Optional. Recommended size: 100x100px. Max 1MB.</p>
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-4">
                <button type="button" id="modal-cancel-btn" class="bg-slate-200 font-semibold px-4 py-2 rounded-lg hover:bg-slate-300">Cancel</button>
                <button type="submit" id="modal-submit-btn" class="bg-inec-green text-white font-semibold px-6 py-2 rounded-lg hover:opacity-90">Add Party</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="delete-modal" class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50 hidden transition-opacity duration-300 opacity-0">
    <div id="delete-modal-content" class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-sm text-center transform transition-all duration-300 scale-95 opacity-0">
        <h3 class="font-display font-bold text-xl text-slate-800">Are you sure?</h3>
        <p class="text-slate-600 my-4">Do you really want to delete the party "<strong id="party-name-to-delete"></strong>"? This action cannot be undone.</p>
        <div class="flex justify-center gap-4">
            <button id="delete-cancel-btn" class="bg-slate-200 font-semibold w-full py-2 rounded-lg hover:bg-slate-300">Cancel</button>
            <form id="delete-form">
                <input type="hidden" name="party_id" id="party_id_to_delete">
                <button type="submit" class="bg-inec-red text-white font-semibold w-full py-2 rounded-lg hover:opacity-90">Yes, Delete</button>
            </form>
        </div>
    </div>
</div>

<script src="party_management.js"></script>

<?php require_once '../includes/admin_footer.php'; ?>