<?php http_response_code(200); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Cytonn Task Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2563eb;
            --primary-dark: #1d4ed8;
            --secondary-color: #64748b;
            --success-color: #059669;
            --warning-color: #d97706;
            --danger-color: #dc2626;
        }

        * {
            font-family: 'Inter', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
            overflow: hidden;
            max-width: 900px;
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            min-height: 600px;
        }

        .login-image {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: white;
            padding: 3rem;
            position: relative;
            overflow: hidden;
        }

        .login-image::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="20" cy="20" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="80" cy="40" r="1.5" fill="rgba(255,255,255,0.1)"/><circle cx="40" cy="80" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="90" cy="90" r="0.8" fill="rgba(255,255,255,0.1)"/></svg>');
            background-size: 100px 100px;
            animation: float 20s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }

        .brand-logo {
            font-size: 3rem;
            margin-bottom: 1rem;
            position: relative;
            z-index: 1;
        }

        .brand-title {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            position: relative;
            z-index: 1;
        }

        .brand-subtitle {
            font-size: 1rem;
            opacity: 0.9;
            text-align: center;
            position: relative;
            z-index: 1;
        }

        .login-form {
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .form-title {
            font-size: 2rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }

        .form-subtitle {
            color: var(--secondary-color);
            margin-bottom: 2rem;
        }

        .form-floating {
            margin-bottom: 1.5rem;
        }

        .form-control {
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 1rem;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .form-label {
            color: var(--secondary-color);
            font-weight: 500;
        }

        .btn-login {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            border: none;
            padding: 1rem;
            font-weight: 600;
            font-size: 1rem;
            border-radius: 12px;
            transition: all 0.3s ease;
            margin-bottom: 1.5rem;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(37, 99, 235, 0.4);
        }

        .alert {
            border: none;
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }

        .demo-credentials {
            background-color: #f8fafc;
            border-radius: 12px;
            padding: 1.5rem;
            margin-top: 1.5rem;
        }

        .demo-title {
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
        }

        .credential-item {
            background: white;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 0.75rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .credential-label {
            font-weight: 600;
            color: var(--primary-color);
            font-size: 0.875rem;
            margin-bottom: 0.25rem;
        }

        .credential-value {
            font-family: 'Courier New', monospace;
            font-size: 0.875rem;
            color: #374151;
        }

        @media (max-width: 768px) {
            .login-container {
                grid-template-columns: 1fr;
                max-width: 400px;
            }

            .login-image {
                display: none;
            }

            .login-form {
                padding: 2rem;
            }

            body {
                padding: 1rem;
            }
        }

        .loading-spinner {
            display: none;
        }

        .btn-login.loading .loading-spinner {
            display: inline-block;
        }

        .btn-login.loading .btn-text {
            display: none;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- Left Side - Branding -->
        <div class="login-image">
            <i class="fas fa-tasks brand-logo"></i>
            <h1 class="brand-title">Cytonn Tasks</h1>
            <p class="brand-subtitle">
                Streamline your workflow with our comprehensive task management solution. 
                Organize, prioritize, and collaborate effectively.
            </p>
        </div>

        <!-- Right Side - Login Form -->
        <div class="login-form">
            <h2 class="form-title">Welcome Back</h2>
            <p class="form-subtitle">Please sign in to your account</p>

            <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i>
                Invalid email or password. Please try again.
            </div>
            <?php endif; ?>

            <?php if (isset($_GET['message'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i>
                <?= htmlspecialchars($_GET['message']) ?>
            </div>
            <?php endif; ?>

            <form action="auth/login.php" method="POST" id="loginForm">
                <div class="form-floating">
                    <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com" required>
                    <label for="email">
                        <i class="fas fa-envelope me-2"></i>Email Address
                    </label>
                </div>

                <div class="form-floating">
                    <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                    <label for="password">
                        <i class="fas fa-lock me-2"></i>Password
                    </label>
                </div>

                <button type="submit" class="btn btn-primary btn-login w-100">
                    <span class="btn-text">
                        <i class="fas fa-sign-in-alt me-2"></i>Sign In
                    </span>
                    <span class="loading-spinner">
                        <i class="fas fa-spinner fa-spin me-2"></i>Signing in...
                    </span>
                </button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form submission with loading state
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.classList.add('loading');
        });

        // Form validation
        const form = document.getElementById('loginForm');
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');

        function validateEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }

        form.addEventListener('submit', function(e) {
            let isValid = true;

            // Reset previous validation styles
            emailInput.classList.remove('is-invalid');
            passwordInput.classList.remove('is-invalid');

            // Validate email
            if (!validateEmail(emailInput.value)) {
                emailInput.classList.add('is-invalid');
                isValid = false;
            }

            // Validate password
            if (passwordInput.value.length < 1) {
                passwordInput.classList.add('is-invalid');
                isValid = false;
            }

            if (!isValid) {
                e.preventDefault();
                const submitBtn = this.querySelector('button[type="submit"]');
                submitBtn.classList.remove('loading');
            }
        });
    </script>
</body>
</html>
