/**
 * REGIDESO - Scripts personnalisés
 */

// Confirmation avant suppression
function confirmDelete(url) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cet enregistrement ? Cette action est irréversible.')) {
        window.location.href = url;
    }
    return false;
}

// Auto-hide des alertes flash
$(document).ready(function() {
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 4000);
});

// Partager la page courante
function sharePage() {
    var pageTitle = document.title || 'REGIDESO';
    var pageUrl = window.location.href;

    // Essayer l'API Web Share native (mobile)
    if (navigator.share) {
        navigator.share({
            title: pageTitle,
            url: pageUrl
        }).catch(function() {
            // Fallback si refusé
            showShareFallback(pageTitle, pageUrl);
        });
    } else {
        showShareFallback(pageTitle, pageUrl);
    }
}

// Fallback partage - copier le lien
function showShareFallback(title, url) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(url).then(function() {
            showQuickNotif('Lien copié dans le presse-papiers !');
        });
    } else {
        var input = document.createElement('input');
        input.value = url;
        document.body.appendChild(input);
        input.select();
        document.execCommand('copy');
        document.body.removeChild(input);
        showQuickNotif('Lien copié !');
    }
}

// Notification rapide
function showQuickNotif(msg) {
    var notif = document.createElement('div');
    notif.innerHTML = '<i class="fas fa-check-circle mr-2"></i>' + msg;
    notif.style.cssText = 'position:fixed;top:20px;right:20px;z-index:99999;background:linear-gradient(135deg,#11998e,#38ef7d);color:white;padding:14px 24px;border-radius:12px;font-weight:600;font-size:0.9rem;box-shadow:0 8px 30px rgba(17,153,142,0.4);animation:fadeIn 0.3s ease;font-family:Inter,sans-serif;';
    document.body.appendChild(notif);
    setTimeout(function() {
        notif.style.transition = 'opacity 0.5s, transform 0.5s';
        notif.style.opacity = '0';
        notif.style.transform = 'translateY(-10px)';
        setTimeout(function() { notif.remove(); }, 500);
    }, 2500);
}
