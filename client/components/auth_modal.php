<!-- Auth Modal (Login / Register) -->
<div id="authModal" class="auth-modal" style="display:none;">
    <div class="auth-modal-backdrop" onclick="closeAuthModal()"></div>
    <div class="auth-modal-content">
        <button class="auth-modal-close" onclick="closeAuthModal()">&times;</button>

        <div id="authForms">
            <!-- Login Form -->
            <form id="authLoginForm" method="POST" action="../login.php" class="auth-form">
                <h2>Sign In</h2>
                <div class="input-group">
                    <label for="auth_email">Email Address</label>
                    <input type="email" id="auth_email" name="email" required>
                </div>
                <div class="input-group">
                    <label for="auth_password">Password</label>
                    <input type="password" id="auth_password" name="password" required>
                </div>
                <button type="submit" class="login-button">Sign In</button>
                <p class="auth-switch">Don't have an account? <a href="#"
                        onclick="switchAuthForm('register'); return false;">Create Account</a></p>
            </form>

            <!-- Register Form -->
            <form id="authRegisterForm" method="POST" action="../register.php" class="auth-form" style="display:none;">
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
                    <input type="password" id="auth_reg_password" name="password" required>
                </div>
                <div class="input-group">
                    <label for="auth_confirm_password">Confirm Password</label>
                    <input type="password" id="auth_confirm_password" name="confirm_password" required>
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
    }

    function closeAuthModal() {
        const modal = document.getElementById('authModal');
        if (!modal) return;
        modal.style.display = 'none';
    }

    function switchAuthForm(view) {
        const loginForm = document.getElementById('authLoginForm');
        const registerForm = document.getElementById('authRegisterForm');
        if (!loginForm || !registerForm) return;
        if (view === 'register') {
            loginForm.style.display = 'none';
            registerForm.style.display = 'block';
        } else {
            loginForm.style.display = 'block';
            registerForm.style.display = 'none';
        }
    }

    window.showAuthModal = showAuthModal;
</script>