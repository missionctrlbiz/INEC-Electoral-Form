<?php
// Include the database connection which also starts the session
require_once 'core/db_connect.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect logged-in clerks to the data entry page
if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'clerk') {
    header('Location: data-entry.php');
    exit;
}
// Redirect logged-in admins to their dashboard
if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header('Location: admin/dashboard.php');
    exit;
}

// If no one is logged in, the script continues and loads the public page.
// The header file contains the top part of the HTML.
require_once 'includes/header.php';
?>

<main>
    <!-- HERO SECTION -->
    <div class="py-24 lg:py-32"> 
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center"> 
            <div class="text-left"> 
                <p class="font-semibold text-inec-green uppercase tracking-widest mb-3">Official Electoral Portal</p>
                <h1 class="font-display font-bold mb-6 text-5xl md:text-6xl text-slate-900 leading-tight">Integrity in <br>Every Result.</h1> 
                <p class="mb-10 text-xl text-slate-600 leading-relaxed">The definitive platform for secure, real-time submission and transparent verification of polling unit results across the nation.</p> 
            </div>
            <div class="flex justify-center"> 
                <img src="assets/images/Election-Portal.png" alt="Election Results Portal Illustration" class="w-full max-w-lg drop-shadow-lg" />
            </div>                     
        </div>                 
    </div>

    <!-- Two-Card Portal Section -->
    <div id="start" class="bg-slate-50 py-20 -mx-6 px-6 rounded-lg">
        <div class="container mx-auto">
            <div class="text-center mb-12">
                <h2 class="font-display font-bold text-4xl text-slate-900 mb-3">Welcome to the Election Portal</h2>
                <p class="text-lg text-slate-500 max-w-3xl mx-auto">This secure platform allows authorized personnel to submit election results and administrators to view comprehensive election data.</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-5xl mx-auto">
                <!-- Card 1: Submit Election Results -->
                <div class="bg-white border border-slate-200 rounded-lg p-8 text-center hover:shadow-xl hover:-translate-y-2 transition-all duration-300 flex flex-col">
                    <h3 class="font-display text-2xl font-bold text-slate-800 mb-3">Submit Election Results</h3>
                    <p class="text-slate-500 mb-6">Enter polling unit results through our secure 4-step process.</p>
                    <div class="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-6"><i class="bi bi-file-earmark-text-fill text-4xl text-blue-600"></i></div>
                    <ul class="text-left space-y-2 text-slate-600 mb-8 flex-grow">
                        <li><i class="bi bi-check-circle-fill text-inec-green mr-2"></i> Step 1: Polling Unit Information</li>
                        <li><i class="bi bi-check-circle-fill text-inec-green mr-2"></i> Step 2: Voter & Ballot Statistics</li>
                        <li><i class="bi bi-check-circle-fill text-inec-green mr-2"></i> Step 3: Political Party Scores</li>
                        <li><i class="bi bi-check-circle-fill text-inec-green mr-2"></i> Step 4: Review & Attach Document</li>
                    </ul>
                    <a href="polling-unit-login.php" class="bg-inec-green font-semibold inline-block px-8 py-3 rounded-md text-white hover:opacity-90 transition-opacity">Begin Data Entry</a>
                </div>
                <!-- Card 2: Admin Dashboard -->
                <div class="bg-white border border-slate-200 rounded-lg p-8 text-center hover:shadow-xl hover:-translate-y-2 transition-all duration-300 flex flex-col">
                    <h3 class="font-display text-2xl font-bold text-slate-800 mb-3">Admin Dashboard</h3>
                    <p class="text-slate-500 mb-6">View comprehensive election results and analytics.</p>
                    <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6"><i class="bi bi-speedometer2 text-4xl text-inec-red"></i></div>
                     <ul class="text-left space-y-2 text-slate-600 mb-8 flex-grow">
                        <li><i class="bi bi-check-circle-fill text-inec-red mr-2"></i> Real-time election analytics</li>
                        <li><i class="bi bi-check-circle-fill text-inec-red mr-2"></i> Detailed result verification</li>
                        <li><i class="bi bi-check-circle-fill text-inec-red mr-2"></i> User and access management</li>
                        <li><i class="bi bi-check-circle-fill text-inec-red mr-2"></i> Export and reporting tools</li>
                    </ul>
                    <a href="admin/index.php" class="bg-slate-700 font-semibold inline-block px-8 py-3 rounded-md text-white hover:bg-slate-800 transition-colors">Access Admin Portal</a>
                </div>
            </div>
        </div>
    </div>
    <!-- Four-Column Feature Section -->
    <div id="learn" class="py-20">
        <div class="container mx-auto">
            <div class="text-center mb-12">
                <h2 class="font-display font-bold text-4xl text-slate-900 mb-3">A Platform Built for Integrity and Speed</h2>
                <p class="text-lg text-slate-500 max-w-2xl mx-auto">Our system provides end-to-end tools for a seamless electoral process.</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8"> 
                <div class="w-full p-4 text-center"> <i class="bi bi-shield-lock-fill text-inec-green text-5xl inline-block mb-4"></i>
                    <h3 class="font-display font-bold mb-2 text-slate-800 text-xl">Secure Data Entry</h3> 
                    <p class="text-sm text-slate-500">A multi-step, validated form ensures polling unit data is entered accurately and completely.</p>
                </div>
                <div class="w-full p-4 text-center"> <i class="bi bi-bar-chart-line-fill text-inec-green text-5xl inline-block mb-4"></i>
                    <h3 class="font-display font-bold mb-2 text-slate-800 text-xl">Real-time Analytics</h3> 
                    <p class="text-sm text-slate-500">The admin dashboard aggregates all submitted data instantly, providing live insights and totals.</p>
                </div>
                <div class="w-full p-4 text-center"> <i class="bi bi-card-checklist text-inec-green text-5xl inline-block mb-4"></i>
                    <h3 class="font-display font-bold mb-2 text-slate-800 text-xl">Visual Verification</h3> 
                    <p class="text-sm text-slate-500">Administrators can directly compare entered data side-by-side with uploaded result sheets.</p>
                </div>
                <div class="w-full p-4 text-center"> <i class="bi bi-people-fill text-inec-green text-5xl inline-block mb-4"></i>
                    <h3 class="font-display font-bold mb-2 text-slate-800 text-xl">User Management</h3> 
                    <p class="text-sm text-slate-500">Control access to the system with role-based permissions for authorized personnel.</p>
                </div>                 
            </div>
        </div>
    </div>
</main>
<?php require_once 'includes/footer.php'; ?>
