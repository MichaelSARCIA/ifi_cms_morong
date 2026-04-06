<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="refresh" content="0;url=/login">
    <title>Session Expired</title>
    <style>
        body {
            font-family: sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            background: #f3f4f6;
        }
        .box {
            text-align: center;
            color: #374151;
        }
        p { color: #6b7280; font-size: 14px; }
        a {
            color: #b91c1c;
            text-decoration: underline;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="box">
        <h2>Session Expired</h2>
        <p>Your session has expired. Redirecting to the login page...</p>
        <p><a href="/login">Click here if you are not redirected.</a></p>
    </div>
    <script>
        // Immediate redirect, no waiting
        window.location.replace('/login');
    </script>
</body>
</html>
