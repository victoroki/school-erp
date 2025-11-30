<!DOCTYPE html>
<html>
<head>
    <title>Tooltip Test</title>
    <link rel="stylesheet" href="{{ asset('css/sidebar-enhanced.css') }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .test-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .instructions {
            background: white;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .test-sidebar {
            width: 70px;
            background: #2b3a4a;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            overflow: visible;
        }
        .test-nav {
            padding: 10px 0;
        }
        .test-nav-item {
            margin: 5px 0;
        }
        .test-nav-link {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 15px 10px;
            color: #c2c7d0;
            text-decoration: none;
            position: relative;
        }
        .test-nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        .test-icon {
            font-size: 1.2rem;
            width: 24px;
            text-align: center;
        }
        .main-content {
            margin-left: 90px;
        }
        .note {
            background: #e7f3ff;
            border-left: 4px solid #007bff;
            padding: 15px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="test-container">
        <div class="instructions">
            <h1>Tooltip Functionality Test</h1>
            <p>This page tests the tooltip functionality for the collapsed sidebar.</p>
            <ol>
                <li>Hover over the icons in the sidebar on the left</li>
                <li>You should see tooltips with the full text appearing to the right</li>
                <li>The tooltips should not be cut off and should be clearly visible</li>
            </ol>
        </div>
        
        <div class="note">
            <strong>Note:</strong> This is a simulation of the collapsed sidebar. In the actual application, 
            the sidebar will automatically switch to this collapsed state when the toggle button is clicked.
        </div>
    </div>
    
    <div class="test-sidebar sidebar-collapse">
        <nav class="test-nav">
            <div class="test-nav-item">
                <a href="#" class="test-nav-link" data-tooltip="Dashboard">
                    <i class="test-icon fas fa-tachometer-alt"></i>
                </a>
            </div>
            <div class="test-nav-item">
                <a href="#" class="test-nav-link" data-tooltip="User Management">
                    <i class="test-icon fas fa-users-cog"></i>
                </a>
            </div>
            <div class="test-nav-item">
                <a href="#" class="test-nav-link" data-tooltip="Academic Management">
                    <i class="test-icon fas fa-graduation-cap"></i>
                </a>
            </div>
            <div class="test-nav-item">
                <a href="#" class="test-nav-link" data-tooltip="Student Management">
                    <i class="test-icon fas fa-user-graduate"></i>
                </a>
            </div>
            <div class="test-nav-item">
                <a href="#" class="test-nav-link" data-tooltip="Examination">
                    <i class="test-icon fas fa-clipboard-list"></i>
                </a>
            </div>
        </nav>
    </div>
    
    <div class="main-content">
        <h2>Main Content Area</h2>
        <p>This is where the main content would appear in your application.</p>
        <p>When you hover over the sidebar icons on the left, you should see tooltips with the full menu item text.</p>
    </div>
    
    <!-- Font Awesome for icons -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/js/all.min.js"></script>
</body>
</html>