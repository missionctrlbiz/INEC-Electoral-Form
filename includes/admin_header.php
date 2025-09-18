<!DOCTYPE html> 
<html lang="en"> 
    <head> 
        <meta charset="UTF-8"> 
        <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
        <title>INEC Admin Portal</title>         
        <script src="https://cdn.tailwindcss.com"></script>         
        <link rel="preconnect" href="https://fonts.googleapis.com"> 
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin> 
        <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@300;400;500;600&family=Syne:wght@600;700;800&display=swap" rel="stylesheet"> 
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"> 
        <!-- Note the ../ path to go up one directory -->         
        <link rel="stylesheet" href="../assets/css/style.css"> 
        <link rel="icon" type="image/webp" href="../assets/images/favicon.webp"> 
        <script>
    tailwind.config = {
      theme: { extend: { fontFamily: { sans: ['Lexend', 'sans-serif'], display: ['Syne', 'sans-serif'], }, colors: { 'inec-green': '#006A4E', 'inec-red': '#D40028', } } }
    }
    </script>         
    </head>     
    <body class="bg-slate-100 font-sans"> 
        <div class="flex min-h-screen"> 
            <aside class="w-64 bg-slate-800 text-white flex flex-col"> 
                <div class="p-6 text-center border-b border-slate-700"> <a href="dashboard.php"><img src="../assets/images/INEC-Logo.png" alt="INEC Logo" class="h-14 w-auto mx-auto"></a> 
                </div>                 
                <nav class="flex-1 px-4 py-4"> <a class="nav-link flex items-center p-3 rounded-lg hover:bg-slate-700 transition-colors mt-2" href="dashboard.php"><i class="bi bi-speedometer2 mr-3 text-lg"></i> Dashboard</a> <a class="nav-link flex items-center p-3 rounded-lg hover:bg-slate-700 transition-colors mt-2" href="results.php"><i class="bi bi-table mr-3 text-lg"></i> Detailed Results</a> <a class="nav-link flex items-center p-3 rounded-lg hover:bg-slate-700 transition-colors mt-2" href="users.php"><i class="bi bi-people mr-3 text-lg"></i> User Management</a> 
                </nav>                 
                <div class="p-4 border-t border-slate-700"> <a class="flex items-center p-3 rounded-lg hover:bg-slate-700 transition-colors" href="logout.php"><i class="bi bi-box-arrow-left mr-3 text-lg"></i> Logout</a> 
                </div>                 
            </aside>             
            <main class="flex-1 p-8">
