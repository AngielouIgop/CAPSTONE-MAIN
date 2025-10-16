<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Not Found - B.A.S.U.R.A. Rewards</title>
    <link rel="stylesheet" href="css/home.css">
    <style>
        .error-page {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 60vh;
            text-align: center;
            padding: 2rem;
        }
        .error-code {
            font-size: 6rem;
            font-weight: bold;
            color: #31326F;
            margin-bottom: 1rem;
        }
        .error-message {
            font-size: 1.5rem;
            color: #666;
            margin-bottom: 2rem;
        }
        .error-description {
            font-size: 1rem;
            color: #888;
            margin-bottom: 2rem;
            max-width: 500px;
        }
        .back-button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #31326F;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        .back-button:hover {
            background-color: #4FB7B3;
        }
    </style>
</head>
<body>
    <div class="error-page">
        <div class="error-code">404</div>
        <div class="error-message">Page Not Found</div>
        <div class="error-description">
            The page you're looking for doesn't exist or has been moved. 
            Please check the URL or return to the homepage.
        </div>
        <a href="?command=home" class="back-button">Go Home</a>
    </div>
</body>
</html>
