function showRegister() {
  document.getElementById('login-form').classList.add('hidden');
  document.getElementById('register-form').classList.remove('hidden');
}

function showLogin() {
  document.getElementById('register-form').classList.add('hidden');
  document.getElementById('login-form').classList.remove('hidden');
}

document.getElementById('loginForm').addEventListener('submit', async (e) => {
  e.preventDefault();
  const email = document.getElementById('login-email').value;
  const password = document.getElementById('login-password').value;

  try {
    const response = await fetch('/api/login', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ email, password })
    });
    const data = await response.json();
    if (response.ok) {
      alert('Login successful! Welcome ' + data.name);
    } else {
      alert(data.message || 'Login failed');
    }
  } catch (error) {
    alert('Error: ' + error.message);
  }
});

document.getElementById('registerForm').addEventListener('submit', async (e) => {
  e.preventDefault();
  const name = document.getElementById('reg-name').value;
  const email = document.getElementById('reg-email').value;
  const password = document.getElementById('reg-password').value;
  const phone = document.getElementById('reg-phone').value || null;
  const address = document.getElementById('reg-address').value || null;
  const role = document.getElementById('reg-role').value;

  try {
    const response = await fetch('/api/register', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ name, email, password, phone, address, role })
    });
    const data = await response.json();
    if (response.ok) {
      alert('Registration successful! Please login.');
      showLogin();
    } else {
      alert(data.message || 'Registration failed');
    }
  } catch (error) {
    alert('Error: ' + error.message);
  }
});