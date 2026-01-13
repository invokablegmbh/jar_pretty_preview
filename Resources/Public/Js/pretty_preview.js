function runPrettyPreviewAjax() {
    const containers = document.querySelectorAll('[data-pretty-ajax-loader-uid]');
    if (containers.length === 0) {
        return;
    }
    const uids = Array.from(containers).map(c => c.getAttribute('data-pretty-ajax-loader-uid'));
    const url = TYPO3.settings.ajaxUrls['prettypreview-load-preview-content'] + '&uids=' + encodeURIComponent(uids.join(','));

    (async function () {
        try {
            const request = await fetch(url);
            const data = await request.json();
            uids.forEach(function(uid) {
                const container = document.querySelector('[data-pretty-ajax-loader-uid="' + uid + '"]');
                if (container && data.result && data.result[uid]) {
                    container.outerHTML = data.result[uid];
                    imageLoadedJavascript(uid);
                }
            });
        } catch (e) {
            console.error('PrettyPreview AJAX error:', e);
        }
    })();
}

function imageLoadedJavascript(uid) {
    if (!uid) return;
    const images = document.querySelectorAll('[data-pretty-content-uid="' + uid + '"] .j77preview-image img');
    if (images.length) {
        images.forEach(function(image) {
            const imageLoadedHandler = function() {
                image.classList.add('loaded');
            };
            if (image.complete) {
                imageLoadedHandler();
            } else {
                image.addEventListener('load', imageLoadedHandler);
                image.addEventListener('error', imageLoadedHandler);
            }
        });
    }
}

function imageLoadedJavascriptAll() {
    let containers = document.querySelectorAll('[data-pretty-content-uid]');
    const uids = Array.from(containers).map(c => c.getAttribute('data-pretty-content-uid'));
    uids.forEach(function(uid) {
        imageLoadedJavascript(uid);
    });
}

runPrettyPreviewAjax();
imageLoadedJavascriptAll();