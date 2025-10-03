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

    // Show loading state
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
      // Hide loading state
      submitBtn.classList.remove('loading');
      submitBtn.disabled = false;
    });
  });
}

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

// 2FA form handler
function initTwoFactorForm() {
  const twoFactorForm = document.getElementById('twoFactorForm');
  if (!twoFactorForm) return;

  twoFactorForm.addEventListener('submit', function(e) {
    e.preventDefault();
    const submitBtn = this.querySelector('button[type="submit"]');
    const formData = new FormData(this);

    // Show loading state
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
      // Hide loading state
      submitBtn.classList.remove('loading');
      submitBtn.disabled = false;
    });
  });
}


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

// Handle form errors from server
function handleFormErrors(errors, form) {
  // Clear previous errors
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
          // Display general error messages
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

function initNotificationSystem() {
  const notificationDropdown = document.getElementById('notificationDropdown');
  const notificationList = document.getElementById('notification-list');
  const notificationCount = document.getElementById('notification-count');

  if (!notificationDropdown) return; // Exit if notification elements aren't on the page

  function fetchNotifications() {
    fetch('notifications.php')
      .then(response => response.json())
      .then(data => {
        if (data.error) {
          console.error('Error fetching notifications:', data.error);
          return;
        }

        notificationList.innerHTML = ''; // Clear existing notifications
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
            link.addEventListener('click', (e) => {
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
        const listItem = document.createElement('li');
        listItem.innerHTML = '<a class="dropdown-item text-danger" href="#">Error loading notifications</a>';
        notificationList.appendChild(listItem);
      });
  }

  function markAsRead(notificationId) {
    fetch(`notifications.php?mark_as_read=${notificationId}`)
      .then(response => response.json())
      .then(data => {
        // After marking as read, re-fetch notifications to update the list
        fetchNotifications();
      })
      .catch(error => {
        console.error('Error marking notification as read:', error);
      });
  }

  // Fetch notifications when the page loads
  fetchNotifications();

  // Poll for new notifications every 10 minutes
  setInterval(fetchNotifications, 600000);
}


// Initialize all functionality when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
  initPasswordToggle();
  initPasswordStrength();
  initLoginForm();
  initSignupForm();
  initTwoFactorForm();
  initPasswordConfirmation();
  initNotificationSystem();
  
  // Add smooth scrolling for anchor links
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
      e.preventDefault();
      const target = document.querySelector(this.getAttribute('href'));
      if (target) {
        target.scrollIntoView({
          behavior: 'smooth'
        });
      }
    });
  });
});

// Handle form submission feedback
function showFormFeedback(type, message) {
    const feedbackContainer = document.getElementById('formFeedback');
    if (!feedbackContainer) {
        console.error('No #formFeedback container found on the page.');
        return;
    }

    const alert = document.createElement('div');
    alert.className = `alert alert-${type} alert-dismissible fade show`;
    alert.role = 'alert';
    alert.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;

    // Clear previous feedback and append the new one
    feedbackContainer.innerHTML = '';
    feedbackContainer.appendChild(alert);

    // Auto-remove after 5 seconds if it's not a countdown
    if (!message.includes('Please try again in')) {
        setTimeout(() => {
            if (alert.parentNode) {
                alert.remove();
            }
        }, 5000);
    }
}


// Export functions for potential use in other scripts
window.CrackCartAuth = {
  showFormFeedback
};