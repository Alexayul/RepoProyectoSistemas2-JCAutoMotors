document.getElementById('eye-icon').addEventListener('click', function() {
    const passwordField = document.getElementById('password');
    const eyeIcon = document.getElementById('eye-icon');

    if (passwordField.type === "password") {
        passwordField.type = "text";
        eyeIcon.innerHTML = '<i class="bi bi-eye-slash"></i>';
    } else {
        passwordField.type = "password";
        eyeIcon.innerHTML = '<i class="bi bi-eye"></i>';
    }
});

setTimeout(() => {
    const alert = document.getElementById('errorAlert');
    if (alert) {
        alert.style.animation = 'slideOut 0.5s forwards';
        setTimeout(() => alert.remove(), 500);
    }
}, 5000);

function closeAlert() {
    const alert = document.getElementById('errorAlert');
    if (alert) {
        alert.style.animation = 'slideOut 0.5s forwards';
        setTimeout(() => alert.remove(), 500);
    }
}

document.getElementById('to-register-btn').addEventListener('click', function() {
    window.location.href = 'registro.php';
});
function closeAlert() {

    const alert = document.getElementById('errorAlert');
if (alert) {
alert.style.animation = 'slideOut 0.5s forwards';
setTimeout(() => alert.remove(), 500);
}
}