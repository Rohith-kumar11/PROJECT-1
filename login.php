<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Investment Platform</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 94vh;
            display: flex;
            flex-direction: column;
        }

        .top-section {
            padding: 2rem;
            color: white;
            text-align: center;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .logo {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .title {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .bottom-sheet {
            background: white;
            border-radius: 24px 24px 0 0;
            padding: 2rem;
            position: relative;
            box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.1);
        }

        .tab-container {
            display: flex;
            margin-bottom: 2rem;
            position: relative;
            border-radius: 12px;
            background: #f5f5f5;
            padding: 0.3rem;
        }

        .tab-btn {
            flex: 1;
            padding: 0.8rem;
            border: none;
            border-radius: 10px;
            font-size: 0.9rem;
            font-weight: 600;
            color: #666;
            background: none;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .tab-btn.active {
            background: white;
            color: #764ba2;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .input-group {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .input-group i {
            position: absolute;
            left: 1rem;
            top: 1rem;
            color: #666;
        }

        .input-group input {
            width: 100%;
            padding: 1rem 1rem 1rem 3rem;
            border: 1px solid #ddd;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .input-group input:focus {
            border-color: #764ba2;
            box-shadow: 0 0 0 2px rgba(118, 75, 162, 0.1);
            outline: none;
        }

        .login-btn {
            width: 100%;
            padding: 1rem;
            border: none;
            border-radius: 12px;
            background: #764ba2;
            color: white;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .login-btn:hover {
            background: #663c8f;
        }

        .signup-link {
            text-align: center;
            margin-top: 1.5rem;
            color: #666;
        }

        .signup-link a {
            color: #764ba2;
            text-decoration: none;
            font-weight: 600;
        }

        .error-message {
            background: #fee2e2;
            color: #dc2626;
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1rem;
            display: none;
        }
    </style>
</head>
<body>
    <div class="top-section">
        <div class="logo">
            <i class="fas fa-handshake"></i>
        </div>
        <h1 class="title">Welcome Back</h1>
        <p class="subtitle">Login to connect with investors and startups</p>
    </div>

    <div class="bottom-sheet">
        <div class="tab-container">
            <button class="tab-btn active" data-type="investor">
                <i class="fas fa-chart-line"></i> Investor
            </button>
            <button class="tab-btn" data-type="entrepreneur">
                <i class="fas fa-lightbulb"></i> Entrepreneur
            </button>
        </div>

        <div class="error-message" id="errorMessage">
            Invalid username or password
        </div>

        <form id="loginForm" action="auth.php" method="POST">
            <input type="hidden" name="userType" id="userType" value="investor">
            
            <div class="input-group">
                <i class="fas fa-user"></i>
                <input type="text" name="username" placeholder="Username" required>
            </div>

            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" placeholder="Password" required>
            </div>

            <button type="submit" class="login-btn">
                <i class="fas fa-sign-in-alt"></i> Login
            </button>

            <div class="signup-link">
                New to platform? <a href="signup.php">Create Account</a>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tabBtns = document.querySelectorAll('.tab-btn');
            const userTypeInput = document.getElementById('userType');
            const urlParams = new URLSearchParams(window.location.search);
            
            if (urlParams.get('error')) {
                document.getElementById('errorMessage').style.display = 'block';
            }
            
            tabBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    tabBtns.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    userTypeInput.value = this.dataset.type;
                });
            });
        });
    </script>
</body>
</html>