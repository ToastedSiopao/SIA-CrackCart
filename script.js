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

// Form validation
function validateEmail(email) {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return emailRegex.test(email);
}

function validatePhone(phone) {
  const phoneRegex = /^[\+]?[1-9][\d]{0,15}$/;
  return phone === '' || phoneRegex.test(phone.replace(/\D/g, ''));
}

function validatePassword(password) {
  return password.length >= 8;
}

// Login form handler
function initLoginForm() {
  const loginForm = document.getElementById('loginForm');
  
  if (loginForm) {
    loginForm.addEventListener('submit', function(e) {
      e.preventDefault();
      
      const email = document.getElementById('loginEmail').value;
      const password = document.getElementById('loginPassword').value;
      const submitBtn = this.querySelector('button[type="submit"]');
      
      // Reset validation
      this.classList.remove('was-validated');
      
      // Validate fields
      let isValid = true;
      
      if (!validateEmail(email)) {
        document.getElementById('loginEmail').classList.add('is-invalid');
        isValid = false;
      } else {
        document.getElementById('loginEmail').classList.remove('is-invalid');
        document.getElementById('loginEmail').classList.add('is-valid');
      }
      
      if (!password) {
        document.getElementById('loginPassword').classList.add('is-invalid');
        isValid = false;
      } else {
        document.getElementById('loginPassword').classList.remove('is-invalid');
        document.getElementById('loginPassword').classList.add('is-valid');
      }
      
      if (isValid) {
        // Show loading state
        submitBtn.classList.add('loading');
        submitBtn.disabled = true;
        
        // Simulate login process
        setTimeout(() => {
          alert('Login successful! (This is a demo)');
          submitBtn.classList.remove('loading');
          submitBtn.disabled = false;
        }, 2000);
      } else {
        this.classList.add('was-validated');
      }
    });
  }
}

// Signup form handler
function initSignupForm() {
  const signupForm = document.getElementById('signupForm');
  
  if (signupForm) {
    signupForm.addEventListener('submit', function(e) {
      e.preventDefault();
      
      const fullName = document.getElementById('fullName').value;
      const email = document.getElementById('signupEmail').value;
      const phone = document.getElementById('phone').value;
      const password = document.getElementById('signupPassword').value;
      const confirmPassword = document.getElementById('confirmPassword').value;
      const agreeTerms = document.getElementById('agreeTerms').checked;
      const submitBtn = this.querySelector('button[type="submit"]');
      
      // Reset validation
      this.classList.remove('was-validated');
      
      // Validate fields
      let isValid = true;
      
      if (!fullName.trim()) {
        document.getElementById('fullName').classList.add('is-invalid');
        isValid = false;
      } else {
        document.getElementById('fullName').classList.remove('is-invalid');
        document.getElementById('fullName').classList.add('is-valid');
      }
      
      if (!validateEmail(email)) {
        document.getElementById('signupEmail').classList.add('is-invalid');
        isValid = false;
      } else {
        document.getElementById('signupEmail').classList.remove('is-invalid');
        document.getElementById('signupEmail').classList.add('is-valid');
      }
      
      if (!validatePhone(phone)) {
        document.getElementById('phone').classList.add('is-invalid');
        isValid = false;
      } else {
        document.getElementById('phone').classList.remove('is-invalid');
        document.getElementById('phone').classList.add('is-valid');
      }
      
      if (!validatePassword(password)) {
        document.getElementById('signupPassword').classList.add('is-invalid');
        isValid = false;
      } else {
        document.getElementById('signupPassword').classList.remove('is-invalid');
        document.getElementById('signupPassword').classList.add('is-valid');
      }
      
      if (password !== confirmPassword) {
        document.getElementById('confirmPassword').classList.add('is-invalid');
        isValid = false;
      } else {
        document.getElementById('confirmPassword').classList.remove('is-invalid');
        document.getElementById('confirmPassword').classList.add('is-valid');
      }
      
      if (!agreeTerms) {
        document.getElementById('agreeTerms').classList.add('is-invalid');
        isValid = false;
      } else {
        document.getElementById('agreeTerms').classList.remove('is-invalid');
        document.getElementById('agreeTerms').classList.add('is-valid');
      }
      
      if (isValid) {
        // Show loading state
        submitBtn.classList.add('loading');
        submitBtn.disabled = true;
        
        // Create user object based on database schema
        const userData = {
          full_name: fullName,
          email: email,
          phone: phone || null,
          password: password, // In real app, this should be hashed
          role: 'customer', // Default role
          status: 'active' // Default status
        };
        
        // Simulate signup process
        setTimeout(() => {
          alert('Account created successfully! (This is a demo)\n\nUser data:\n' + JSON.stringify(userData, null, 2));
          submitBtn.classList.remove('loading');
          submitBtn.disabled = false;
          
          // Redirect to login page
          window.location.href = 'index.html';
        }, 2000);
      } else {
        this.classList.add('was-validated');
      }
    });
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

// Real-time email validation
function initEmailValidation() {
  const emailFields = document.querySelectorAll('input[type="email"]');
  
  emailFields.forEach(field => {
    field.addEventListener('input', function() {
      if (this.value === '') {
        this.classList.remove('is-valid', 'is-invalid');
        return;
      }
      
      if (validateEmail(this.value)) {
        this.classList.remove('is-invalid');
        this.classList.add('is-valid');
      } else {
        this.classList.remove('is-valid');
        this.classList.add('is-invalid');
      }
    });
  });
}

// Phone number formatting
function initPhoneFormatting() {
  const phoneField = document.getElementById('phone');
  
  if (phoneField) {
    phoneField.addEventListener('input', function() {
      // Remove all non-digit characters
      let value = this.value.replace(/\D/g, '');
      
      // Format phone number (US format)
      if (value.length >= 6) {
        value = value.replace(/(\d{3})(\d{3})(\d+)/, '($1) $2-$3');
      } else if (value.length >= 3) {
        value = value.replace(/(\d{3})(\d+)/, '($1) $2');
      }
      
      this.value = value;
      
      // Validate
      if (this.value === '' || validatePhone(this.value)) {
        this.classList.remove('is-invalid');
        this.classList.add('is-valid');
      } else {
        this.classList.remove('is-valid');
        this.classList.add('is-invalid');
      }
    });
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
  initPasswordConfirmation();
  initEmailValidation();
  initPhoneFormatting();
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
  const alert = document.createElement('div');
  alert.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
  alert.style.top = '100px';
  alert.style.right = '20px';
  alert.style.zIndex = '1060';
  alert.innerHTML = `
    ${message}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  `;
  
  document.body.appendChild(alert);
  
  // Auto-remove after 5 seconds
  setTimeout(() => {
    if (alert.parentNode) {
      alert.remove();
    }
  }, 5000);
}

// Export functions for potential use in other scripts
window.CrackCartAuth = {
  validateEmail,
  validatePhone,
  validatePassword,
  checkPasswordStrength,
  showFormFeedback
};
