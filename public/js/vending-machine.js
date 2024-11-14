document.addEventListener('DOMContentLoaded', function () {
    const resultAlert = document.getElementById('result-alert');
    const successSound = document.getElementById('success-sound');

    if (resultAlert) {
        successSound.play();
    }
});

document.addEventListener('DOMContentLoaded', function () {
    const resultAlert = document.getElementById('result-alert');
    const successSound = document.getElementById('success-sound');

    if (resultAlert) {
        successSound.play();
    }

    const productCodes = document.querySelectorAll('.product-code');
    const itemCodeInput = document.getElementById('item_code');

    productCodes.forEach(function (codeElement) {
        codeElement.addEventListener('click', function () {
            const code = codeElement.textContent.trim();
            itemCodeInput.value = code;
            itemCodeInput.focus(); // Optional: focuses the input field
        });
    });
});
