<?php
require_once 'config/database.php';

// Initialize database
initializeDatabase();

echo "<!DOCTYPE html>
<html lang='id'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Initialize Database - SMA Negeri 6 Surakarta</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        .success {
            color: #28a745;
            font-weight: bold;
        }
        .btn {
            display: inline-block;
            background-color: #004080;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 4px;
            margin: 10px;
        }
        .btn:hover {
            background-color: #0055aa;
        }
    </style>
</head>
<body>
    <div class='container'>
        <h2>Database Initialization Complete</h2>
        <p class='success'>Database and tables have been successfully created/updated!</p>
        <p>You can now access the application:</p>
        <a href='index.php' class='btn'>Go to Main Application</a>
        <a href='laporan.php' class='btn'>Go to Reports Section</a>
    </div>
</body>
</html>";
?>