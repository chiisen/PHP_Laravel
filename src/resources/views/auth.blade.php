<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laravel API 認證中心</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #6366f1;
            --primary-hover: #4f46e5;
            --bg-gradient: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%);
            --glass-bg: rgba(255, 255, 255, 0.05);
            --glass-border: rgba(255, 255, 255, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Outfit', sans-serif;
        }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--bg-gradient);
            color: white;
            padding: 20px;
        }

        .container {
            width: 100%;
            max-width: 450px;
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            animation: fadeIn 0.6s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        h1 {
            font-size: 2rem;
            font-weight: 600;
            text-align: center;
            margin-bottom: 8px;
            background: linear-gradient(to right, #818cf8, #c084fc);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        p.subtitle {
            text-align: center;
            color: #94a3b8;
            margin-bottom: 32px;
            font-size: 0.95rem;
        }

        .tabs {
            display: flex;
            background: rgba(0, 0, 0, 0.2);
            padding: 4px;
            border-radius: 12px;
            margin-bottom: 32px;
        }

        .tab {
            flex: 1;
            padding: 10px;
            text-align: center;
            cursor: pointer;
            border-radius: 8px;
            transition: 0.3s;
            font-size: 0.9rem;
            font-weight: 600;
            color: #94a3b8;
        }

        .tab.active {
            background: var(--primary);
            color: white;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-size: 0.85rem;
            color: #94a3b8;
            margin-bottom: 8px;
            margin-left: 4px;
        }

        input {
            width: 100%;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            padding: 12px 16px;
            color: white;
            font-size: 1rem;
            transition: 0.3s;
            outline: none;
        }

        input:focus {
            border-color: var(--primary);
            background: rgba(255, 255, 255, 0.1);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.2);
        }

        button {
            width: 100%;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 14px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 10px;
        }

        button:hover {
            background: var(--primary-hover);
            transform: translateY(-1px);
            box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.4);
        }

        button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .message {
            margin-top: 20px;
            padding: 12px;
            border-radius: 12px;
            font-size: 0.9rem;
            display: none;
        }

        .message.success {
            background: rgba(34, 197, 94, 0.1);
            color: #4ade80;
            border: 1px solid rgba(34, 197, 94, 0.2);
            display: block;
        }

        .message.error {
            background: rgba(239, 68, 68, 0.1);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.2);
            display: block;
        }

        #user-info {
            display: none;
            animation: fadeIn 0.6s ease-out;
        }

        .user-card {
            background: rgba(255, 255, 255, 0.03);
            border-radius: 16px;
            padding: 20px;
            margin-top: 20px;
            border: 1px dashed var(--glass-border);
        }

        .token-box {
            background: #000;
            padding: 10px;
            border-radius: 8px;
            font-family: monospace;
            font-size: 0.75rem;
            word-break: break-all;
            margin-top: 10px;
            color: #4ade80;
        }
    </style>
</head>
<body>

<div class="container" id="auth-container">
    <div id="auth-forms">
        <h1>Welcome Back</h1>
        <p class="subtitle" id="form-subtitle">請輸入您的帳號資訊進行登入</p>

        <div class="tabs">
            <div class="tab active" onclick="switchTab('login')">登入</div>
            <div class="tab" onclick="switchTab('register')">註冊</div>
        </div>

        <div id="login-form">
            <div class="form-group">
                <label>電子郵件</label>
                <input type="email" id="login-email" placeholder="test@example.com" value="test@example.com">
            </div>
            <div class="form-group">
                <label>密碼</label>
                <input type="password" id="login-password" placeholder="••••••••" value="password">
            </div>
            <button onclick="handleLogin()" id="login-btn">立即登入</button>
        </div>

        <div id="register-form" style="display: none;">
            <div class="form-group">
                <label>姓名</label>
                <input type="text" id="reg-name" placeholder="您的姓名">
            </div>
            <div class="form-group">
                <label>電子郵件</label>
                <input type="email" id="reg-email" placeholder="test@example.com">
            </div>
            <div class="form-group">
                <label>密碼</label>
                <input type="password" id="reg-password" placeholder="至少 8 個字元">
            </div>
            <div class="form-group">
                <label>確認密碼</label>
                <input type="password" id="reg-password_confirmation" placeholder="再次輸入密碼">
            </div>
            <button onclick="handleRegister()" id="register-btn">註冊新帳號</button>
        </div>

        <div id="api-message" class="message"></div>
    </div>

    <div id="user-info">
        <h1>認證成功!</h1>
        <p class="subtitle">您現在已透過 Sanctum Token 登入</p>
        
        <div class="user-card">
            <div style="font-size: 0.85rem; color: #94a3b8;">登入帳號</div>
            <div id="display-name" style="font-size: 1.1rem; font-weight: 600; margin: 4px 0;"></div>
            <div id="display-email" style="font-size: 0.9rem; color: #94a3b8;"></div>
            
            <div style="font-size: 0.85rem; color: #94a3b8; margin-top: 16px;">API Token (Bearer)</div>
            <div class="token-box" id="display-token"></div>
        </div>

        <button onclick="handleLogout()" style="background: rgba(239, 68, 68, 0.1); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.2); margin-top: 24px;">登出系統</button>
    </div>
</div>

<script>
    let currentMode = 'login';
    const baseUrl = window.location.origin;

    function switchTab(mode) {
        currentMode = mode;
        const loginForm = document.getElementById('login-form');
        const registerForm = document.getElementById('register-form');
        const tabs = document.querySelectorAll('.tab');
        const title = document.querySelector('h1');
        const subtitle = document.getElementById('form-subtitle');

        if (mode === 'login') {
            loginForm.style.display = 'block';
            registerForm.style.display = 'none';
            tabs[0].classList.add('active');
            tabs[1].classList.remove('active');
            title.innerText = 'Welcome Back';
            subtitle.innerText = '請輸入您的帳號資訊進行登入';
        } else {
            loginForm.style.display = 'none';
            registerForm.style.display = 'block';
            tabs[0].classList.remove('active');
            tabs[1].classList.add('active');
            title.innerText = 'Create Account';
            subtitle.innerText = '加入我們，開啟您的 Laravel 旅程';
        }
        showMessage('', ''); // 清除訊息
    }

    function showMessage(text, type) {
        const msgDiv = document.getElementById('api-message');
        if (!text) {
            msgDiv.style.display = 'none';
            return;
        }
        msgDiv.innerText = text;
        msgDiv.className = 'message ' + type;
        msgDiv.style.display = 'block';
    }

    async function handleLogin() {
        const email = document.getElementById('login-email').value;
        const password = document.getElementById('login-password').value;
        const btn = document.getElementById('login-btn');

        btn.disabled = true;
        btn.innerText = '驗證中...';

        try {
            const response = await fetch(`${baseUrl}/api/login`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ email, password, device_name: 'web-browser' })
            });

            const data = await response.json();

            if (response.ok) {
                localStorage.setItem('sanctum_token', data.token);
                showUserInfo(data.user, data.token);
            } else {
                showMessage(data.message || '登入失敗，請檢查帳密', 'error');
            }
        } catch (error) {
            showMessage('連線錯誤，請確認後端 API 是否運作中', 'error');
        } finally {
            btn.disabled = false;
            btn.innerText = '立即登入';
        }
    }

    async function handleRegister() {
        const name = document.getElementById('reg-name').value;
        const email = document.getElementById('reg-email').value;
        const password = document.getElementById('reg-password').value;
        const password_confirmation = document.getElementById('reg-password_confirmation').value;
        const btn = document.getElementById('register-btn');

        btn.disabled = true;
        btn.innerText = '註冊中...';

        try {
            const response = await fetch(`${baseUrl}/api/register`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ name, email, password, password_confirmation, device_name: 'web-browser' })
            });

            const data = await response.json();

            if (response.ok) {
                localStorage.setItem('sanctum_token', data.token);
                showUserInfo(data.user, data.token);
            } else {
                const errorMsg = data.errors ? Object.values(data.errors).flat().join(' ') : (data.message || '註冊失敗');
                showMessage(errorMsg, 'error');
            }
        } catch (error) {
            showMessage('連線錯誤', 'error');
        } finally {
            btn.disabled = false;
            btn.innerText = '註冊新帳號';
        }
    }

    function showUserInfo(user, token) {
        document.getElementById('auth-forms').style.display = 'none';
        document.getElementById('user-info').style.display = 'block';
        document.getElementById('display-name').innerText = user.name;
        document.getElementById('display-email').innerText = user.email;
        document.getElementById('display-token').innerText = token;
    }

    async function handleLogout() {
        const token = localStorage.getItem('sanctum_token');
        try {
            await fetch(`${baseUrl}/api/logout`, {
                method: 'POST',
                headers: { 
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${token}` 
                }
            });
        } catch (e) {} finally {
            localStorage.removeItem('sanctum_token');
            window.location.reload();
        }
    }

    // 檢查是否已登入
    window.onload = async () => {
        const token = localStorage.getItem('sanctum_token');
        if (token) {
            try {
                const res = await fetch(`${baseUrl}/api/user`, {
                    headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
                });
                if (res.ok) {
                    const user = await res.json();
                    showUserInfo(user, token);
                } else {
                    localStorage.removeItem('sanctum_token');
                }
            } catch (e) {}
        }
    };
</script>

</body>
</html>
