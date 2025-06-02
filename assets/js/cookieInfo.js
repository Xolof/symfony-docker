'use strict';

(() => {
    const cookieButton = document.querySelector('.cookieInfoButton');
    const cookieBanner = document.querySelector('.cookieInfo');

    if (cookieButton) {
        cookieButton.addEventListener('click', () => {
            fetch('/confirm-cookie-info');
            cookieBanner.style.display = 'none';
        });
    }
})();
