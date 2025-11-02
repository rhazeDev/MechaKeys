<!-- Auth Modal (Login / Register) -->
<div id="authModal" class="auth-modal" style="display:none;">
    <div class="auth-modal-backdrop" onclick="closeAuthModal()"></div>
    <div class="auth-modal-content">
        <button class="auth-modal-close" onclick="closeAuthModal()">&times;</button>

        <div id="authForms">
            <!-- Error/Success Message -->
            <div id="authMessage" style="display:none; padding: 12px; margin-bottom: 16px; border-radius: 4px; font-size: 14px;"></div>

            <!-- Login Form -->
            <form id="authLoginForm" class="auth-form" onsubmit="handleLoginSubmit(event)">
                <h2>Sign In</h2>
                <div class="input-group">
                    <label for="auth_email">Email Address</label>
                    <input type="email" id="auth_email" name="email" required>
                </div>
                <div class="input-group">
                    <label for="auth_password">Password</label>
                    <div class="password-input-wrapper">
                        <input type="password" id="auth_password" name="password" required>
                        <button type="button" class="toggle-password" onclick="togglePasswordVisibility('auth_password')">
                            <i class="fas fa-eye" id="auth_password_icon"></i>
                        </button>
                    </div>
                </div>
                <button type="submit" class="login-button">Sign In</button>
                <p class="auth-switch">Don't have an account? <a href="#"
                        onclick="switchAuthForm('register'); return false;">Create Account</a></p>
            </form>

            <!-- Register Form -->
            <form id="authRegisterForm" class="auth-form" style="display:none;" onsubmit="handleRegisterSubmit(event)">
                <h2>Create Account</h2>
                <div class="input-group">
                    <label for="auth_fullname">Full Name</label>
                    <input type="text" id="auth_fullname" name="fullname" required>
                </div>
                <div class="input-group">
                    <label for="auth_reg_email">Email Address</label>
                    <input type="email" id="auth_reg_email" name="email" required>
                </div>
                <div class="input-group">
                    <label for="auth_reg_password">Password</label>
                    <div class="password-input-wrapper">
                        <input type="password" id="auth_reg_password" name="password" required>
                        <button type="button" class="toggle-password" onclick="togglePasswordVisibility('auth_reg_password')">
                            <i class="fas fa-eye" id="auth_reg_password_icon"></i>
                        </button>
                    </div>
                </div>
                <div class="input-group">
                    <label for="auth_confirm_password">Confirm Password</label>
                    <div class="password-input-wrapper">
                        <input type="password" id="auth_confirm_password" name="confirm_password" required>
                        <button type="button" class="toggle-password" onclick="togglePasswordVisibility('auth_confirm_password')">
                            <i class="fas fa-eye" id="auth_confirm_password_icon"></i>
                        </button>
                    </div>
                </div>
                <button type="submit" class="login-button">Sign up</button>
                <p class="auth-switch">Already have an account? <a href="#"
                        onclick="switchAuthForm('login'); return false;">Sign In</a></p>
            </form>
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
        messageDiv.textContent = message;
        messageDiv.style.display = 'block';
        messageDiv.style.backgroundColor = isError ? '#fee' : '#efe';
        messageDiv.style.color = isError ? '#c33' : '#3a3';
        messageDiv.style.border = `1px solid ${isError ? '#fcc' : '#cfc'}`;
    }

    function hideAuthMessage() {
        const messageDiv = document.getElementById('authMessage');
        messageDiv.style.display = 'none';
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