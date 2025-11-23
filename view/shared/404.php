<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Not Found - B.A.S.U.R.A. Rewards</title>
    <link rel="stylesheet" href="css/public/home.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Poppins', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .error-container {
            background: #ffffff;
            border-radius: 20px;
            padding: 3rem 2rem;
            text-align: center;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
            border: 1px solid rgba(0, 0, 0, 0.05);
            max-width: 500px;
            width: 90%;
        }
        
        .error-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }
        
        .error-code {
            font-size: 5rem;
            font-weight: 800;
            color: #2c3e50;
            margin-bottom: 0.5rem;
            line-height: 1;
        }
        
        .error-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 1rem;
        }
        
        .error-description {
            font-size: 1rem;
            color: #6c757d;
            margin-bottom: 2rem;
            line-height: 1.6;
        }
        
        .back-button {
            display: inline-block;
            padding: 15px 30px;
            background: #2336b6;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }
        
        .back-button:hover {
            background: #1a237e;
            transform: translateY(-1px);
            color: white;
            text-decoration: none;
        }
        
        .back-button:active {
            transform: translateY(0);
        }
        
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">🚫</div>
        <div class="error-code">404</div>
        <div class="error-title">Oops! Page Not Found</div>
        <div class="error-description">
            The page you're looking for seems to have wandered off into the digital void. 
            Don't worry, even the best explorers sometimes take a wrong turn!
        </div>
        <a href="/CAPSTONE-MAIN/?command=home" class="back-button">Take Me Home</a>
    </div>
</body>
</html>
