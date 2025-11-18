// Price Input Formatter - Format numbers with thousand separators while typing

document.addEventListener('DOMContentLoaded', function () {
    // Find all price input fields
    const priceInputs = document.querySelectorAll('input[name="purchase_price"], input[name="selling_price"]');

    priceInputs.forEach(input => {
        // Change input type from number to text to allow formatting
        input.type = 'text';
        input.inputMode = 'numeric'; // Show numeric keyboard on mobile
        input.pattern = '[0-9.,]*'; // Allow only numbers, commas, and dots

        // Format on load if value exists
        if (input.value && input.value !== '0') {
            input.value = formatNumber(input.value);
        }

        // Format while typing
        input.addEventListener('input', function (e) {
            let value = e.target.value;

            // Remove all non-numeric characters except dots and commas
            value = value.replace(/[^\d.,]/g, '');

            // Remove existing dots and commas (we'll add them back)
            value = value.replace(/[.,]/g, '');

            // If empty, set to 0
            if (value === '') {
                value = '0';
            }

            // Format with thousand separators
            e.target.value = formatNumber(value);
        });

        // On blur, ensure it's formatted correctly
        input.addEventListener('blur', function (e) {
            let value = e.target.value.replace(/[.,]/g, '');
            if (value === '' || value === '0') {
                e.target.value = '0';
            } else {
                e.target.value = formatNumber(value);
            }
        });

        // Before form submit, remove formatting so server gets clean number
        const form = input.closest('form');
        if (form) {
            form.addEventListener('submit', function (e) {
                priceInputs.forEach(priceInput => {
                    // Store original value
                    const formatted = priceInput.value;
                    // Remove all dots and commas for submission
                    priceInput.value = formatted.replace(/[.,]/g, '');
                });
            });
        }
    });

    function formatNumber(value) {
        // Convert to number and back to remove leading zeros
        const num = parseInt(value, 10);
        if (isNaN(num)) return '0';

        // Format with dots as thousand separator (Indonesian style)
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    console.log('✅ Price formatter loaded');
});
