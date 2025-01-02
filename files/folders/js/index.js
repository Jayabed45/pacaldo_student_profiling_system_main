function toggleForm() {
    const loginSection = document.getElementById('login-section');
    const registerSection = document.getElementById('register-section');

    if (loginSection.style.display === 'none') {
        loginSection.style.display = 'flex';
        registerSection.style.display = 'none';
    } else {
        loginSection.style.display = 'none';
        registerSection.style.display = 'flex';
    }
}