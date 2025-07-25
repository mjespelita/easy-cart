if ('serviceWorker' in navigator) {
    window.addEventListener('load', function () {
        navigator.serviceWorker.register('../install/serviceWorkerRegister.js')
            .then(reg => console.log('[SW] Registered'))
            .catch(err => console.log(`Service worker error: ${err}`));
    });
}
