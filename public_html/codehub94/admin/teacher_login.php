<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Login</title>
    <script src="https://cdn.jsdelivr.net/npm/tsparticles@2.11.1/tsparticles.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        html, body { height: 100%; width: 100%; position: relative; overflow: hidden; background: #000; }

        #tsparticles {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            z-index: 0;
        }

        .error-message {
            position: absolute;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(220, 53, 69, 0.95);
            color: white;
            padding: 0.8rem 1.2rem;
            border-radius: 5px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.5s ease;
            z-index: 999;
        }

        .login-container {
            position: relative;
            z-index: 1;
            background: rgba(255, 255, 255, 0.97);
            padding: 2.5rem 2rem;
            border-radius: 12px;
            box-shadow: 0 12px 40px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 350px;
            text-align: center;
            margin: auto;
            top: 50%;
            transform: translateY(-50%);
        }

        .login-container h2 {
            margin-bottom: 0.5rem;
            font-size: 1.8rem;
            color: #333;
            font-weight: 700;
        }

        .login-container p {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 1.8rem;
        }

        .form-group { margin-bottom: 1.4rem; text-align: left; }
        .form-group label {
            display: block;
            margin-bottom: 0.4rem;
            font-weight: 600;
            font-size: 0.85rem;
            color: #333;
        }

        .form-control {
            width: 100%;
            padding: 0.7rem 1rem;
            border: none;
            border-bottom: 2px solid #ddd;
            background: transparent;
            font-size: 1rem;
            transition: border-color 0.3s, box-shadow 0.3s;
        }

        .form-control:focus {
            border-color: #ec4899;
            outline: none;
            box-shadow: 0 2px 8px rgba(236,72,153,0.3);
        }

        .btn-login {
            width: 100%;
            background: linear-gradient(45deg, #ec4899, #6366f1);
            color: #fff;
            border: none;
            padding: 0.8rem;
            border-radius: 50px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s, transform 0.2s;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(236,72,153,0.4);
        }

        .forgot-password {
            display: block;
            margin-top: 0.8rem;
            font-size: 0.85rem;
            color: #6366f1;
            text-decoration: none;
            transition: color 0.3s;
        }

        .forgot-password:hover { color: #4338ca; }
    </style>
</head>
<body>
    <div id="tsparticles"></div>

    <div class="error-message" id="errorBox"></div>

    <div class="login-container">
        <h2>Teacher Login</h2>
        <p>Use your mobile number and password to login</p>

        <form action="teacher_login_process.php" method="post">
            <div class="form-group">
                <label for="mobile"><i class="fas fa-user"></i> Mobile Number</label>
                <input type="text" class="form-control" id="mobile" name="mobile" placeholder="Enter your mobile number" required>
            </div>
            
            <div class="form-group">
                <label for="password"><i class="fas fa-lock"></i> Password</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="Enter password" required>
            </div>
            
            <button type="submit" class="btn-login">Sign In</button>
            <a href="#" class="forgot-password">Forgot password?</a>
        </form>
    </div>

    <script>
        tsParticles.load("tsparticles", {
            background: { color: "#000" },
            fpsLimit: 60,
            particles: {
                number: { value: 100, density: { enable: true, area: 800 } },
                color: { value: ["#ec4899", "#6366f1"] },
                links: { enable: true, color: "#ffffff", distance: 150, opacity: 0.5, width: 1 },
                move: { enable: true, speed: 2, direction: "none", outModes: "bounce" },
                size: { value: 3 },
                opacity: { value: 0.7 }
            },
            interactivity: {
                events: { onHover: { enable: true, mode: "repulse" }, onClick: { enable: true, mode: "push" } },
                modes: { repulse: { distance: 100 }, push: { quantity: 4 } }
            }
        });

        function showError(message) {
            const errorBox = document.getElementById('errorBox');
            errorBox.innerHTML = "<i class='fas fa-exclamation-circle'></i> " + message;
            errorBox.style.opacity = 1;
            setTimeout(() => { errorBox.style.opacity = 0; }, 3000);
        }
    </script>

    <?php
        if(isset($_GET['err']) && $_GET['err'] == "true") {
            echo "<script>window.onload = () => { showError('Access Denied: Invalid Credentials'); };</script>";
        }
        else if(isset($_GET['msg']) && $_GET['msg'] == "true") {
            echo "<script>window.onload = () => { showError('Unauthorized access'); };</script>";
        }
        else if(isset($_GET['logout']) && $_GET['logout'] == "true") {
            echo "<script>window.onload = () => { showError('Logged out'); };</script>";
        }
    ?>
</body>
</html>
