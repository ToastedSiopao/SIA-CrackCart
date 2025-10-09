// ui.js

export function showFormFeedback(type, message) {
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

    feedbackContainer.innerHTML = '';
    feedbackContainer.appendChild(alert);

    if (!message.includes('Please try again in')) {
        setTimeout(() => {
            if (alert.parentNode) {
                alert.remove();
            }
        }, 5000);
    }
}

export function handleFormErrors(errors, form) {
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

export function initPasswordToggle() {
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

export function initPasswordStrength() {
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

export function initNotificationSystem() {
  const notificationDropdown = document.getElementById('notificationDropdown');
  const notificationList = document.getElementById('notification-list');
  const notificationCount = document.getElementById('notification-count');

  if (!notificationDropdown) return;

  function fetchNotifications() {
    fetch('notifications.php')
      .then(response => response.json())
      .then(data => {
        if (data.error) {
          console.error('Error fetching notifications:', data.error);
          return;
        }

        notificationList.innerHTML = '';
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
        fetchNotifications();
      })
      .catch(error => {
        console.error('Error marking notification as read:', error);
      });
  }

  fetchNotifications();
  setInterval(fetchNotifications, 600000);
}

export function initPasswordConfirmation() {
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