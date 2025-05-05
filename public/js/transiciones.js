
document.addEventListener('DOMContentLoaded', function() {

    const loginContainer = document.querySelector('.login-container');
    const toRegisterBtn = document.getElementById('to-register-btn');
    const registerLink = document.querySelector('.no-account a');
    const toLoginBtn = document.getElementById('to-login-btn');
    const loginLink = document.getElementById('to-login-link');
    const isLoginPage = window.location.pathname === '/' || window.location.pathname === '/index.html';
    const isRegisterPage = window.location.pathname === '/registro' || window.location.pathname === '/registro.html';
    function transitionToRegister(e) {
        e.preventDefault();
    
        loginContainer.classList.add('transitioning');
        loginContainer.classList.add('flip-to-register');

        setTimeout(() => {
            window.location.href = '/registro';
        }, 300);
    }
    
    function transitionToLogin(e) {
        e.preventDefault();
        
        loginContainer.classList.add('transitioning');
        loginContainer.classList.add('flip-to-login');

        setTimeout(() => {
            window.location.href = '/';
        }, 300); 
    }
    
    if (isLoginPage && toRegisterBtn) {
        toRegisterBtn.addEventListener('click', transitionToRegister);
        if (registerLink) registerLink.addEventListener('click', transitionToRegister);
    }
    
    if (isRegisterPage && toLoginBtn) {
        toLoginBtn.addEventListener('click', transitionToLogin);
        if (loginLink) loginLink.addEventListener('click', transitionToLogin);
    }

    const comingFromTransition = sessionStorage.getItem('pageTransition');
    if (comingFromTransition) {
        loginContainer.classList.add('transitioning-in');
        if (isLoginPage) {
            loginContainer.classList.add('flip-from-register');
        } else if (isRegisterPage) {
            loginContainer.classList.add('flip-from-login');
        }
        
        setTimeout(() => {
            loginContainer.classList.remove('transitioning-in', 'flip-from-register', 'flip-from-login');
        }, 700);
        
        sessionStorage.removeItem('pageTransition');
    }
    
    function setTransitionState() {
        sessionStorage.setItem('pageTransition', 'true');
    }
    
    if (toRegisterBtn) toRegisterBtn.addEventListener('click', setTransitionState);
    if (registerLink) registerLink.addEventListener('click', setTransitionState);
    if (toLoginBtn) toLoginBtn.addEventListener('click', setTransitionState);
    if (loginLink) loginLink.addEventListener('click', setTransitionState);
});