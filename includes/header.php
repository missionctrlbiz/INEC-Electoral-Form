<!DOCTYPE html> 
<html lang="en" class="scroll-smooth"> 
    <head> 
        <meta charset="UTF-8"> 
        <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
        <title>INEC - Electoral Result Management System</title>         
        <script src="https://cdn.tailwindcss.com"></script>         
        <link rel="preconnect" href="https://fonts.googleapis.com"> 
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin> 
        <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@300;400;500;600&family=Syne:wght@600;700;800&display=swap" rel="stylesheet"> 
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"> 
        <!-- Link to the new external stylesheet -->         
        <link rel="stylesheet" href="assets/css/style.css"> 
        <link rel="icon" type="image/webp" href="assets/images/favicon.webp"> 
        <script>
    tailwind.config = {
      theme: { extend: { fontFamily: { sans: ['Lexend', 'sans-serif'], display: ['Syne', 'sans-serif'], }, colors: { 'inec-green': '#006A4E', 'inec-red': '#D40028', } } }
    }
    </script>         
    </head>     
    <body class="bg-white font-sans text-slate-700"> 
        <div class="container mx-auto px-6"> 
            <header class="sticky top-0 z-40 bg-white"> 
                <nav class="flex items-center justify-between flex-wrap py-4 border-b border-slate-200"> <a href="index.php" class="flex items-center">  <img src="assets/images/INEC-Logo.png" alt="INEC Logo" class="h-14 w-auto">  </a> 
                    <div class="flex items-center gap-4"> <a href="#learn" class="bg-slate-100 font-semibold inline-block px-6 py-3 rounded-lg text-slate-800 hover:bg-slate-200 transition-colors">Wow More</a> <a href="#start" class="bg-inec-green font-semibold inline-block px-6 py-3 rounded-lg text-white hover:opacity-90 transition-opacity shadow-md">Get Started</a> 
                    </div>                     
                </nav>                 
            </header>
