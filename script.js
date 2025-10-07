// Password visibility toggle
function initPasswordToggle() {
  const toggleButtons = document.querySelectorAll('[id^="toggle"]');
  
  toggleButtons.forEach(button => {
    button.addEventListener('click', function() {
      const passwordField = this.parentElement.querySelector('input[type="password"], input[type="text"]');
      const icon = this.querySelector('i');
      
      if (passwordField.type === 'password') {
        passwordField.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
      } else {
        passwordField.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
      }
    });
  });
}

// Password strength checker
function checkPasswordStrength(password) {
  let strength = 0;
  let feedback = '';
  
  if (password.length >= 8) strength += 1;
  if (password.match(/[a-z]/)) strength += 1;
  if (password.match(/[A-Z]/)) strength += 1;
  if (password.match(/[0-9]/)) strength += 1;
  if (password.match(/[^a-zA-Z0-9]/)) strength += 1;
  
  switch (strength) {
    case 0:
    case 1:
      feedback = 'Very Weak';
      return { strength: 20, class: 'strength-weak', text: feedback };
    case 2:
      feedback = 'Weak';
      return { strength: 40, class: 'strength-weak', text: feedback };
    case 3:
      feedback = 'Fair';
      return { strength: 60, class: 'strength-fair', text: feedback };
    case 4:
      feedback = 'Good';
      return { strength: 80, class: 'strength-good', text: feedback };
    case 5:
      feedback = 'Strong';
      return { strength: 100, class: 'strength-strong', text: feedback };
    default:
      return { strength: 0, class: 'strength-weak', text: 'Very Weak' };
  }
}

// Initialize password strength checker
function initPasswordStrength() {
  const passwordField = document.getElementById('signupPassword');
  const strengthBar = document.getElementById('passwordStrength');
  const strengthText = document.getElementById('passwordHelp');
  
  if (passwordField && strengthBar && strengthText) {
    passwordField.addEventListener('input', function() {
      const password = this.value;
      const result = checkPasswordStrength(password);
      
      strengthBar.style.width = result.strength + '%';
      strengthBar.className = `progress-bar ${result.class}`;
      strengthText.textContent = `Password strength: ${result.text}`;
    });
  }
}

// Login form handler
function initLoginForm() {
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
      body: formData,
      credentials: 'include' // ensure session persistence
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
    window.location.href = '2fa_page.php';
} else if (data.success) {
    window.location.href = 'dashboard.php';
}

    })
    .catch(() => {
      showFormFeedback('danger', 'An unexpected error occurred. Please try again.');
    })
    .finally(() => {
      submitBtn.classList.remove('loading');
      submitBtn.disabled = false;
    });
  });
}

function handleLockout(message, form) {
  const submitBtn = form.querySelector('button[type="submit"]');
  const emailField = form.querySelector('[name="email"]');
  const passwordField = form.querySelector('[name="password"]');

  const remaining = parseInt(message.match(/\d+/)?.[0]);
  if (isNaN(remaining)) return;

  submitBtn.disabled = true;
  emailField.disabled = true;
  passwordField.disabled = true;

  let countdown = remaining;
  const intervalId = setInterval(() => {
    const minutes = Math.floor(countdown / 60);
    const seconds = countdown % 60;
    const timeLeft = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
    
    showFormFeedback('warning', `Too many failed attempts. Please try again in ${timeLeft}.`);
    
    if (countdown <= 0) {
      clearInterval(intervalId);
      submitBtn.disabled = false;
      emailField.disabled = false;
      passwordField.disabled = false;
      showFormFeedback('success', 'You can now try to log in again.');
    }
    countdown--;
  }, 1000);
}

// 2FA form handler
/**function initTwoFactorForm() {
  const form = document.getElementById('twoFactorForm');
  if (!form) {
    console.error("❌ #twoFactorForm not found.");
    return;
  }

  form.addEventListener('submit', async e => {
    e.preventDefault();
    const submitBtn = form.querySelector('button[type="submit"]');
    const formData = new FormData(form);

    submitBtn.disabled = true;

    try {
      const response = await fetch('verify_2fa.php', {
        method: 'POST',
        body: formData,
        credentials: 'include'
      });
      const data = await response.json();
      console.log("2FA Response:", data);

      if (data.status && data.status.toLowerCase() === 'success') {
        console.log("✅ SUCCESS BLOCK REACHED");
        alert("✅ Verification successful! Redirecting now...");
        setTimeout(() => {
          window.location.replace('dashboard.php');
        }, 1500);
      } else {
        console.warn("⚠️ Not success:", data);
        showFormFeedback('danger', data.message || 'Verification failed.');
      }
    } catch (err) {
      console.error("2FA Fetch Error:", err);
      showFormFeedback('danger', 'An unexpected error occurred.');
    } finally {
      submitBtn.disabled = false;
    }
  });
}**/



// Signup form handler
function initSignupForm() {
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
      body: formData,
      credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
      if (data.error) {
        handleFormErrors(data.error, this);
      } else if (data.success) {
        showFormFeedback('success', 'Account created successfully! Redirecting to login...');
        setTimeout(() => window.location.href = 'login.php', 3000);
      }
    })
    .catch(() => {
      showFormFeedback('danger', 'An unexpected error occurred. Please try again.');
    })
    .finally(() => {
      submitBtn.classList.remove('loading');
      submitBtn.disabled = false;
    });
  });
}

// Handle form errors from server
function handleFormErrors(errors, form) {
  form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
  form.querySelectorAll('.invalid-feedback').forEach(el => el.remove());

  if (Array.isArray(errors)) {
    errors.forEach(error => {
      if (error.field) {
        const inputField = form.querySelector(`[name="${error.field}"]`);
        if (inputField) {
          inputField.classList.add('is-invalid');
          const errorDiv = document.createElement('div');
          errorDiv.className = 'invalid-feedback';
          errorDiv.textContent = error.message;
          inputField.parentNode.appendChild(errorDiv);
        }
      } else {
        showFormFeedback('danger', error.message);
      }
    });
  } else if (errors.message) {
    showFormFeedback('danger', errors.message);
  }
}

// Real-time password confirmation validation
function initPasswordConfirmation() {
  const password = document.getElementById('signupPassword');
  const confirmPassword = document.getElementById('confirmPassword');
  
  if (password && confirmPassword) {
    function checkPasswordMatch() {
      if (confirmPassword.value === '') {
        confirmPassword.classList.remove('is-valid', 'is-invalid');
        return;
      }
      
      if (password.value === confirmPassword.value) {
        confirmPassword.classList.remove('is-invalid');
        confirmPassword.classList.add('is-valid');
      } else {
        confirmPassword.classList.remove('is-valid');
        confirmPassword.classList.add('is-invalid');
      }
    }
    
    password.addEventListener('input', checkPasswordMatch);
    confirmPassword.addEventListener('input', checkPasswordMatch);
  }
}

// Notification system
function initNotificationSystem() {
  const notificationDropdown = document.getElementById('notificationDropdown');
  const notificationList = document.getElementById('notification-list');
  const notificationCount = document.getElementById('notification-count');
  if (!notificationDropdown) return;

  function fetchNotifications() {
    fetch('notifications.php', { credentials: 'same-origin' })
      .then(response => response.json())
      .then(data => {
        notificationList.innerHTML = '';
        if (data.error) {
          console.error('Notification error:', data.error);
          return;
        }

        if (data.length > 0) {
          notificationCount.textContent = data.length;
          notificationCount.style.display = 'block';

          data.forEach(notification => {
            const listItem = document.createElement('li');
            const link = document.createElement('a');
            link.classList.add('dropdown-item');
            link.href = '#';
            link.innerHTML = `
              <div class="d-flex justify-content-between">
                <small>${notification.MESSAGE}</small>
                <small class="text-muted">${new Date(notification.CREATED_AT).toLocaleTimeString()}</small>
              </div>
            `;
            link.addEventListener('click', e => {
              e.preventDefault();
              markAsRead(notification.NOTIFICATION_ID);
            });
            listItem.appendChild(link);
            notificationList.appendChild(listItem);
          });
        } else {
          notificationCount.style.display = 'none';
          const listItem = document.createElement('li');
          listItem.innerHTML = '<a class="dropdown-item text-muted" href="#">No new notifications</a>';
          notificationList.appendChild(listItem);
        }
      })
      .catch(error => {
        console.error('Fetch error:', error);
      });
  }

  function markAsRead(notificationId) {
    fetch(`notifications.php?mark_as_read=${notificationId}`, { credentials: 'same-origin' })
      .then(() => fetchNotifications())
      .catch(error => console.error('Error marking notification as read:', error));
  }

  fetchNotifications();
  setInterval(fetchNotifications, 600000);
}

// Initialize all functionality
document.addEventListener('DOMContentLoaded', function() {
  initPasswordToggle();
  initPasswordStrength();
  initLoginForm();
  initSignupForm();
  //initTwoFactorForm();
  initPasswordConfirmation();
  initNotificationSystem();
  
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
      e.preventDefault();
      const target = document.querySelector(this.getAttribute('href'));
      if (target) {
        target.scrollIntoView({ behavior: 'smooth' });
      }
    });
  });
});

// Display alerts
function showFormFeedback(type, message) {
  const feedbackContainer = document.getElementById('formFeedback');
  if (!feedbackContainer) return console.error('No #formFeedback found.');

  const alert = document.createElement('div');
  alert.className = `alert alert-${type} alert-dismissible fade show`;
  alert.role = 'alert';
  alert.innerHTML = `
    ${message}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  `;

  feedbackContainer.innerHTML = '';
  feedbackContainer.appendChild(alert);

  if (!message.includes('Please try again in')) {
    setTimeout(() => alert.remove(), 5000);
  }
}

// Export functions
window.CrackCartAuth = { showFormFeedback };
