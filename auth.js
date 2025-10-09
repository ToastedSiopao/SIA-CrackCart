// auth.js

import { showFormFeedback, handleFormErrors } from './ui.js';

function handleLockout(message, form) {
    const submitBtn = form.querySelector('button[type="submit"]');
    const emailField = form.querySelector('[name="email"]');
    const passwordField = form.querySelector('[name="password"]');

    // Extract remaining seconds from the message
    const remaining = parseInt(message.match(/\d+/)[0]);
    if (isNaN(remaining)) return;

    // Disable form fields
    submitBtn.disabled = true;
    emailField.disabled = true;
    passwordField.disabled = true;

    // Start countdown
    let countdown = remaining;
    const intervalId = setInterval(() => {
        const minutes = Math.floor(countdown / 60);
        const seconds = countdown % 60;
        const timeLeft = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        
        showFormFeedback('warning', `Too many failed attempts. Please try again in ${timeLeft}.`);
        
        if (countdown <= 0) {
            clearInterval(intervalId);
            // Re-enable form fields
            submitBtn.disabled = false;
            emailField.disabled = false;
            passwordField.disabled = false;
            showFormFeedback('success', 'You can now try to log in again.');
        }
        countdown--;
    }, 1000);
}

export function initLoginForm() {
  const loginForm = document.getElementById('loginForm');
  if (!loginForm) return;

  loginForm.addEventListener('submit', function(e) {
    e.preventDefault();
    const submitBtn = this.querySelector('button[type="submit"]');
    const formData = new FormData(this);

    submitBtn.classList.add('loading');
    submitBtn.disabled = true;

    fetch('login_process.php', {
      method: 'POST',
      body: formData
    })
    .then(async response => {
        const data = await response.json();
        if (!response.ok) {
            if (response.status === 429) {
                handleLockout(data.error.message, loginForm);
            } else {
                handleFormErrors(data.error, loginForm);
            }
            return;
        }

        if (data.success && data.two_factor) {
            window.location.href = '2fa.php';
        } else if (data.success) {
            window.location.href = 'dashboard.php'; 
        }
    })
    .catch(error => {
      showFormFeedback('danger', 'An unexpected error occurred. Please try again.');
    })
    .finally(() => {
      submitBtn.classList.remove('loading');
      submitBtn.disabled = false;
    });
  });
}

export function initTwoFactorForm() {
  const twoFactorForm = document.getElementById('twoFactorForm');
  if (!twoFactorForm) return;

  twoFactorForm.addEventListener('submit', function(e) {
    e.preventDefault();
    const submitBtn = this.querySelector('button[type="submit"]');
    const formData = new FormData(this);

    submitBtn.classList.add('loading');
    submitBtn.disabled = true;

    fetch('verify_2fa.php', {
      method: 'POST',
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      if (data.error) {
        handleFormErrors(data.error, this);
      } else if (data.success) {
        window.location.href = 'dashboard.php';
      }
    })
    .catch(error => {
      showFormFeedback('danger', 'An unexpected error occurred. Please try again.');
    })
    .finally(() => {
      submitBtn.classList.remove('loading');
      submitBtn.disabled = false;
    });
  });
}

export function initSignupForm() {
  const signupForm = document.getElementById('signupForm');
  if (!signupForm) return;

  signupForm.addEventListener('submit', function(e) {
    e.preventDefault();
    const submitBtn = this.querySelector('button[type="submit"]');
    const formData = new FormData(this);
    
    submitBtn.classList.add('loading');
    submitBtn.disabled = true;

    fetch('signup_process.php', {
      method: 'POST',
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      if (data.error) {
        handleFormErrors(data.error, this);
      } else if (data.success) {
        showFormFeedback('success', 'Account created successfully! Redirecting to login...');
        setTimeout(() => {
          window.location.href = 'login.php';
        }, 3000);
      }
    })
    .catch(error => {
      showFormFeedback('danger', 'An unexpected error occurred. Please try again.');
    })
    .finally(() => {
      submitBtn.classList.remove('loading');
      submitBtn.disabled = false;
    });
  });
}