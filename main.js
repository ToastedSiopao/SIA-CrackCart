// main.js

import { initLoginForm, initSignupForm, initTwoFactorForm } from './auth.js';
import { initPasswordToggle, initPasswordStrength, initPasswordConfirmation, initNotificationSystem } from './ui.js';
import { initSidebar } from './sidebar.js';

document.addEventListener('DOMContentLoaded', () => {
    // Initialize all modules
    initLoginForm();
    initSignupForm();
    initTwoFactorForm();
    initPasswordToggle();
    initPasswordStrength();
    initPasswordConfirmation();
    initNotificationSystem();
    initSidebar();

    // Smooth scrolling for anchor links
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
