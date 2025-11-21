<!-- Auth Modal (Login / Register) -->
<div id="authModal" class="auth-modal" style="display:none;">
    <div class="auth-modal-backdrop" onclick="closeAuthModal()"></div>
    <div class="auth-modal-content">

        <div class="login-wrapper">
            <div class="login-container">
                <button class="auth-modal-close" onclick="closeAuthModal()">&times;</button>
                <div class="login-header">
                    <div class="login-title">Welcome Back</div>
                    <div class="login-subtitle">Sign in or create an account to continue</div>
                </div>

                <!-- Message -->
                <div id="authMessage"
                    style="display:none; padding: 12px; margin-bottom: 16px; border-radius: 4px; font-size: 14px;">
                </div>

                <!-- Login Form -->
                <form id="authLoginForm" class="form" onsubmit="handleLoginSubmit(event)">
                    <div class="input-group">
                        <label for="auth_email"><i class="fas fa-envelope"></i> Email Address</label>
                        <div style="position: relative;">
                            <span class="input-icon"><i class="fas fa-envelope"></i></span>
                            <input type="email" id="auth_email" name="email" placeholder=" " required>
                        </div>
                    </div>

                    <div class="input-group password-group">
                        <label for="auth_password"><i class="fas fa-lock"></i> Password</label>
                        <div style="position: relative;">
                            <span class="input-icon"><i class="fas fa-lock"></i></span>
                            <input type="password" id="auth_password" name="password" placeholder=" " required>
                            <button type="button" class="password-toggle"
                                onclick="togglePasswordVisibility('auth_password')">
                                <i class="fas fa-eye" id="auth_password_icon"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="login-button"><i class="fas fa-sign-in-alt"></i> Login</button>
                    <div class="register-link">
                        <p class="register-link-text">Don't have an account?</p>
                        <button type="button" class="register-button" onclick="switchAuthForm('register')">Create
                            Account</button>
                    </div>
                </form>

                <!-- Register Form -->
                <form id="authRegisterForm" class="form" style="display:none;" onsubmit="handleRegisterSubmit(event)">
                    <div class="input-group">
                        <label for="auth_fullname"><i class="fas fa-user"></i> Full Name</label>
                        <div style="position: relative;">
                            <span class="input-icon"><i class="fas fa-user"></i></span>
                            <input type="text" id="auth_fullname" name="fullname" placeholder=" " required>
                        </div>
                    </div>

                    <div class="input-group">
                        <label for="auth_reg_email"><i class="fas fa-envelope"></i> Email Address</label>
                        <div style="position: relative;">
                            <span class="input-icon"><i class="fas fa-envelope"></i></span>
                            <input type="email" id="auth_reg_email" name="email" placeholder=" " required>
                        </div>
                    </div>

                    <div class="input-group password-group">
                        <label for="auth_reg_password"><i class="fas fa-lock"></i> Password</label>
                        <div style="position: relative;">
                            <span class="input-icon"><i class="fas fa-lock"></i></span>
                            <input type="password" id="auth_reg_password" name="password" placeholder=" " required>
                            <button type="button" class="password-toggle"
                                onclick="togglePasswordVisibility('auth_reg_password')">
                                <i class="fas fa-eye" id="auth_reg_password_icon"></i>
                            </button>
                        </div>
                        <small style="color: #666; font-size: 12px; margin-top: 5px; display: block;">Must be 8-20
                            characters with uppercase, lowercase, number, and special character</small>
                    </div>

                    <div class="input-group password-group">
                        <label for="auth_confirm_password"><i class="fas fa-lock"></i> Confirm Password</label>
                        <div style="position: relative;">
                            <span class="input-icon"><i class="fas fa-lock"></i></span>
                            <input type="password" id="auth_confirm_password" name="confirm_password" placeholder=" "
                                required>
                            <button type="button" class="password-toggle"
                                onclick="togglePasswordVisibility('auth_confirm_password')">
                                <i class="fas fa-eye" id="auth_confirm_password_icon"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="login-button"><i class="fas fa-user-plus"></i> Register</button>
                    <div class="register-link">
                        <p class="register-link-text">Already have an account?</p>
                        <button type="button" class="register-button" onclick="switchAuthForm('login')">Login</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function showAuthModal(view = 'login') {
        const modal = document.getElementById('authModal');
        if (!modal) return;
        modal.style.display = 'flex';
        switchAuthForm(view);
        hideAuthMessage();
    }

    function closeAuthModal() {
        const modal = document.getElementById('authModal');
        if (!modal) return;
        modal.style.display = 'none';
        hideAuthMessage();
    }

    function switchAuthForm(view) {
        const loginForm = document.getElementById('authLoginForm');
        const registerForm = document.getElementById('authRegisterForm');
        if (!loginForm || !registerForm) return;
        hideAuthMessage();
        if (view === 'register') {
            loginForm.style.display = 'none';
            registerForm.style.display = 'block';
        } else {
            loginForm.style.display = 'block';
            registerForm.style.display = 'none';
        }
    }

    function togglePasswordVisibility(inputId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(inputId + '_icon');

        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }

    function showAuthMessage(message, isError = true) {
        const messageDiv = document.getElementById('authMessage');
        const iconHtml = isError ? '<i class="fas fa-exclamation-circle"></i> ' : '<i class="fas fa-check-circle"></i> ';
        messageDiv.innerHTML = iconHtml + message;
        messageDiv.style.display = 'block';
        messageDiv.className = isError ? 'error-message' : 'success-message';
    }

    function hideAuthMessage() {
        const messageDiv = document.getElementById('authMessage');
        if (!messageDiv) return;
        messageDiv.style.display = 'none';
        messageDiv.className = '';
    }

    async function handleLoginSubmit(event) {
        event.preventDefault();

        const form = event.target;
        const formData = new FormData(form);
        formData.append('ajax', 'true');

        const button = form.querySelector('button[type="submit"]');
        button.disabled = true;
        button.textContent = 'Signing in...';

        try {
            const response = await fetch('../login.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                showAuthMessage('Login successful! Redirecting...', false);
                setTimeout(() => {
                    window.location.href = result.redirect;
                }, 500);
            } else {
                showAuthMessage(result.message, true);
                button.disabled = false;
                button.textContent = 'Sign In';
            }
        } catch (error) {
            showAuthMessage('An error occurred. Please try again.', true);
            button.disabled = false;
            button.textContent = 'Sign In';
        }
    }

    async function handleRegisterSubmit(event) {
        event.preventDefault();

        const form = event.target;
        const formData = new FormData(form);
        formData.append('ajax', 'true');

        const button = form.querySelector('button[type="submit"]');
        button.disabled = true;
        button.textContent = 'Creating account...';

        try {
            const response = await fetch('../register.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                showAuthMessage('Account created! Redirecting...', false);
                setTimeout(() => {
                    window.location.href = result.redirect;
                }, 500);
            } else {
                showAuthMessage(result.message, true);
                button.disabled = false;
                button.textContent = 'Sign up';
            }
        } catch (error) {
            showAuthMessage('An error occurred. Please try again.', true);
            button.disabled = false;
            button.textContent = 'Sign up';
        }
    }

    window.showAuthModal = showAuthModal;
</script>