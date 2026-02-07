<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="mobile-web-app-capable" content="yes">
    <link rel="icon" type="image/svg+xml" href="https://Sol-0203.com/favicon.ico">
    <title>Sol-0203 Portal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary: #2196F3;
            --secondary: #1976D2;
            --accent: #0D47A1;
            --text: #212121;
            --text-light: #757575;
            --white: #FFFFFF;
            --glass: rgba(255, 255, 255, 0.2);
            --glass-border: rgba(255, 255, 255, 0.3);
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Roboto', sans-serif;
            -webkit-tap-highlight-color: transparent;
        }
        html, body {
            height: 100%;
            width: 100%;
            margin: 0;
        }
        body {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 1.5rem;
            position: fixed;
            top: 0;
            left: 0;
            overflow: hidden;
            touch-action: none;
        }
        .background-animation {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            overflow: hidden;
        }
        .particles {
            position: absolute;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, transparent 20%, var(--glass) 20%, transparent 30%);
            background-size: 75px 75px;
            opacity: 0.15;
            animation: rotate 50s linear infinite;
        }
        @keyframes rotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .particle {
            position: absolute;
            background: rgba(255, 255, 255, 0.4);
            border-radius: 50%;
            pointer-events: none;
            animation: float 12s infinite ease-in-out;
        }
        @keyframes float {
            0% { transform: translateY(0) scale(1); opacity: 0.3; }
            50% { opacity: 0.7; }
            100% { transform: translateY(-120vh) scale(0.7); opacity: 0.2; }
        }
        .gradient-overlay {
            position: absolute;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, rgba(33, 150, 243, 0.15), rgba(13, 71, 161, 0.15), rgba(33, 150, 243, 0.15));
            background-size: 200%;
            z-index: 1;
            animation: gradientFlow 25s ease-in-out infinite;
        }
        @keyframes gradientFlow {
            0% { background-position: 0% 50%; }
            50% { background-position: 200% 50%; }
            100% { background-position: 0% 50%; }
        }
        .shape {
            position: absolute;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 20%;
            pointer-events: none;
            animation: drift 20s infinite ease-in-out;
        }
        @keyframes drift {
            0% { transform: translate(0, 0) rotate(0deg); opacity: 0.3; }
            50% { transform: translate(50px, -50px) rotate(180deg); opacity: 0.5; }
            100% { transform: translate(0, 0) rotate(360deg); opacity: 0.3; }
        }
        .login-container {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            padding: 2.5rem;
            border-radius: 1.2rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            width: 100%;
            max-width: 420px;
            border: 1px solid var(--glass-border);
            position: relative;
            z-index: 2;
            animation: fadeIn 0.6s ease-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .brand-wrapper {
            text-align: center;
            margin-bottom: 2rem;
        }
        .brand-wrapper h2 {
            color: var(--text);
            font-size: 1.8rem;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        .brand-wrapper p {
            color: var(--text-light);
            font-size: 0.95rem;
            margin-top: 0.3rem;
        }
        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.6rem;
            color: var(--text);
            font-size: 0.95rem;
            font-weight: 500;
        }
        .form-control {
            width: 100%;
            padding: 1rem;
            border: 1px solid rgba(0, 0, 0, 0.15);
            border-radius: 0.8rem;
            font-size: 1rem;
            background: rgba(255, 255, 255, 0.8);
            transition: all 0.3s ease;
        }
        .form-control:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(33, 150, 243, 0.2);
            background: var(--white);
        }
        .btn-login {
            width: 100%;
            padding: 1rem;
            border: none;
            border-radius: 0.8rem;
            background: linear-gradient(90deg, var(--primary), var(--accent));
            color: var(--white);
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .btn-login:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(33, 150, 243, 0.3);
        }
        .btn-login:active {
            transform: translateY(0);
        }
        .support-section {
            margin-top: 2rem;
            text-align: center;
            padding-top: 1.5rem;
            border-top: 1px solid rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
        }
        .support-text {
            color: var(--text-light);
            font-size: 0.85rem;
        }
        .support-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 48px;
            height: 48px;
            background: url('https://telegram.org/img/t_logo.png') no-repeat center;
            background-size: 32px;
            border-radius: 50%;
            transition: all 0.3s ease;
            border: 2px solid rgba(255, 255, 255, 0.3);
        }
        .support-link:hover {
            transform: scale(1.15);
            border-color: var(--primary);
        }
        .footer {
            position: fixed;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100%;
            max-width: 100%;
            text-align: center;
            padding: 1rem;
            color: var(--white);
            font-size: 0.85rem;
            z-index: 2;
            background: rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
        }
        .footer a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }
        .footer a:hover {
            color: var(--accent);
        }
        @media (max-width: 768px) {
            .login-container {
                max-width: 90%;
                padding: 2rem;
            }
            .brand-wrapper h2 {
                font-size: 1.6rem;
            }
            .form-control {
                padding: 0.9rem;
                font-size: 0.95rem;
            }
            .btn-login {
                padding: 0.9rem;
                font-size: 0.95rem;
            }
            .footer {
                font-size: 0.8rem;
                padding: 0.8rem;
            }
        }
        @media (max-width: 480px) {
            .login-container {
                padding: 1.5rem;
                max-width: 95%;
            }
            .brand-wrapper h2 {
                font-size: 1.4rem;
            }
            .brand-wrapper p {
                font-size: 0.85rem;
            }
            .form-group {
                margin-bottom: 1.2rem;
            }
            .form-control {
                padding: 0.8rem;
                font-size: 0.9rem;
            }
            .btn-login {
                padding: 0.8rem;
                font-size: 0.9rem;
            }
            .support-text {
                font-size: 0.8rem;
            }
            .support-link {
                width: 42px;
                height: 42px;
                background-size: 28px;
            }
            .footer {
                font-size: 0.75rem;
                padding: 0.6rem;
            }
        }
    </style>
</head>
<body>
    <div class="background-animation">
        <div class="particles"></div>
        <div class="gradient-overlay"></div>
    </div>
    <div class="login-container">
        <div class="brand-wrapper">
            <h2>Sol-0203 Portal</h2>
            <p>Secure Access Panel</p>
        </div>
        <form action="maulyikarisalu.php" method="post">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" class="form-control" id="username" name="username" required
                       placeholder="Enter username" value="">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" class="form-control" id="password" name="password" required
                       placeholder="Enter password" value="">
            </div>
            <button type="submit" class="btn-login">
                <span>Sign In</span>
                <i class="fas fa-arrow-right"></i>
            </button>
        </form>
        <div class="support-section">
            <div class="support-text">Have a query? Contact the developer.</div>
            <a href="https://t.me/Sol-0203" target="_blank" class="support-link"></a>
        </div>
    </div>
    <div class="footer">
        &copy; <span id="year"></span> <a href="#">Sol-0203.com</a> | All Rights Reserved
    </div>
    <script>
        document.getElementById('year').textContent = new Date().getFullYear();
        // Prevent pinch zoom and scrolling
        document.addEventListener('touchmove', (e) => e.preventDefault(), { passive: false });
        document.addEventListener('gesturestart', (e) => e.preventDefault());
        document.addEventListener('gesturechange', (e) => e.preventDefault());
        document.addEventListener('gestureend', (e) => e.preventDefault());
        // Particle and shape animation
        function createBackgroundElements() {
            const container = document.querySelector('.background-animation');
            const particleCount = 25;
            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                particle.style.width = `${Math.random() * 5 + 3}px`;
                particle.style.height = particle.style.width;
                particle.style.left = `${Math.random() * 100}vw`;
                particle.style.top = `${Math.random() * 100}vh`;
                particle.style.animationDuration = `${Math.random() * 8 + 10}s`;
                particle.style.animationDelay = `${Math.random() * 4}s`;
                container.appendChild(particle);
            }
            const shapeCount = 8;
            for (let i = 0; i < shapeCount; i++) {
                const shape = document.createElement('div');
                shape.className = 'shape';
                const size = Math.random() * 30 + 20;
                shape.style.width = `${size}px`;
                shape.style.height = `${size}px`;
                shape.style.left = `${Math.random() * 100}vw`;
                shape.style.top = `${Math.random() * 100}vh`;
                shape.style.animationDuration = `${Math.random() * 15 + 15}s`;
                shape.style.animationDelay = `${Math.random() * 5}s`;
                shape.style.transform = `rotate(${Math.random() * 360}deg)`;
                container.appendChild(shape);
            }
        }
        window.addEventListener('load', createBackgroundElements);
        // Check if admin is already logged in
        fetch('check_session.php')
            .then(response => response.json())
            .then(data => {
                if (data.isLoggedIn && data.role === 'admin') {
                    window.location.href = 'dashboard.php';
                }
            })
            .catch(error => console.error('Error checking session:', error));
    </script>
</body>
</html>