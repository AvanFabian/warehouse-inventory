// Prevent Multiple Form Submissions and Button Spam Clicking

document.addEventListener('DOMContentLoaded', function () {
    // Track form submission states
    const submittedForms = new WeakSet();

    // Handle all forms
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function (e) {
            // Check if form was already submitted
            if (submittedForms.has(form)) {
                e.preventDefault();
                return false;
            }

            // Mark form as submitted
            submittedForms.add(form);

            // Disable all submit buttons in this form
            const submitButtons = form.querySelectorAll('button[type="submit"], input[type="submit"]');
            submitButtons.forEach(button => {
                button.disabled = true;

                // Store original content
                const originalContent = button.innerHTML || button.value;
                button.dataset.originalContent = originalContent;

                // Show loading state
                if (button.tagName === 'BUTTON') {
                    button.innerHTML = `
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Processing...
                    `;
                } else {
                    button.value = 'Processing...';
                }

                // Add visual feedback
                button.style.opacity = '0.6';
                button.style.cursor = 'not-allowed';
            });

            // Re-enable if form validation fails or after timeout
            setTimeout(() => {
                if (!form.checkValidity()) {
                    submittedForms.delete(form);
                    submitButtons.forEach(button => {
                        button.disabled = false;
                        button.style.opacity = '1';
                        button.style.cursor = 'pointer';

                        // Restore original content
                        if (button.dataset.originalContent) {
                            if (button.tagName === 'BUTTON') {
                                button.innerHTML = button.dataset.originalContent;
                            } else {
                                button.value = button.dataset.originalContent;
                            }
                        }
                    });
                }
            }, 100);
        });
    });

    // Prevent double-click on all buttons (not just submit buttons)
    const clickCounts = new WeakMap();

    document.querySelectorAll('button, a.btn, input[type="button"]').forEach(element => {
        element.addEventListener('click', function (e) {
            // Skip if it's a submit button (already handled above)
            if (this.type === 'submit') {
                return;
            }

            // Get current timestamp
            const now = Date.now();
            const lastClick = clickCounts.get(this) || 0;

            // Prevent clicks within 500ms (0.5 seconds)
            if (now - lastClick < 500) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }

            // Update last click time
            clickCounts.set(this, now);

            // Add visual feedback for action buttons
            if (this.classList.contains('btn-danger') ||
                this.classList.contains('bg-red-600') ||
                this.classList.contains('bg-danger')) {

                // Temporarily disable
                const originalDisabled = this.disabled;
                this.disabled = true;
                this.style.opacity = '0.6';

                setTimeout(() => {
                    this.disabled = originalDisabled;
                    this.style.opacity = '1';
                }, 500);
            }
        });
    });

    // Prevent rapid deletion clicks
    document.querySelectorAll('form[method="post"]').forEach(form => {
        const methodInput = form.querySelector('input[name="_method"]');
        if (methodInput && methodInput.value === 'DELETE') {
            form.addEventListener('submit', function (e) {
                const submitButton = form.querySelector('button[type="submit"]');
                if (submitButton) {
                    submitButton.disabled = true;
                    submitButton.style.opacity = '0.5';

                    // Store original text
                    const originalText = submitButton.textContent;
                    submitButton.innerHTML = `
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Deleting...
                    `;
                }
            });
        }
    });

    // Debounce function for search inputs
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Apply debounce to search inputs
    document.querySelectorAll('input[type="search"], input[name="search"]').forEach(input => {
        const form = input.closest('form');
        if (form) {
            let debounceTimer;
            input.addEventListener('input', function () {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    // Auto-submit search after 500ms of no typing
                    // Only if form doesn't have other required fields
                    const requiredFields = form.querySelectorAll('[required]');
                    if (requiredFields.length === 0 || (requiredFields.length === 1 && requiredFields[0] === input)) {
                        // Don't auto-submit, just prevent rapid typing submission
                    }
                }, 500);
            });
        }
    });

    // Add loading indicator helper function
    window.showButtonLoading = function (button, loadingText = 'Processing...') {
        if (button) {
            button.disabled = true;
            button.dataset.originalContent = button.innerHTML || button.value;

            if (button.tagName === 'BUTTON') {
                button.innerHTML = `
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    ${loadingText}
                `;
            }
        }
    };

    // Add restore button helper function
    window.restoreButton = function (button) {
        if (button && button.dataset.originalContent) {
            button.disabled = false;
            if (button.tagName === 'BUTTON') {
                button.innerHTML = button.dataset.originalContent;
            } else {
                button.value = button.dataset.originalContent;
            }
            delete button.dataset.originalContent;
        }
    };

    console.log('✅ Button spam protection loaded');
});

// Add CSS for disabled state
const style = document.createElement('style');
style.textContent = `
    button:disabled,
    input[type="submit"]:disabled,
    input[type="button"]:disabled {
        cursor: not-allowed !important;
        opacity: 0.6 !important;
    }
    
    button:disabled:hover,
    input[type="submit"]:disabled:hover {
        transform: none !important;
    }
    
    .processing {
        pointer-events: none;
        opacity: 0.6;
    }
`;
document.head.appendChild(style);
