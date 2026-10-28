<?php
/**
 * RESIDESO - Fonctions utilitaires
 */

/**
 * Nettoie les entrées utilisateur
 */
function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Pagination - calcule l'offset et le nombre total de pages
 */
function paginate($pdo, $table, $where = '', $params = [], $per_page = 10) {
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    
    $sql_count = "SELECT COUNT(*) FROM $table $where";
    $stmt = $pdo->prepare($sql_count);
    $stmt->execute($params);
    $total = $stmt->fetchColumn();
    
    $total_pages = max(1, ceil($total / $per_page));
    $page = min($page, $total_pages);
    $offset = ($page - 1) * $per_page;
    
    return [
        'page'        => $page,
        'per_page'    => $per_page,
        'total'       => $total,
        'total_pages' => $total_pages,
        'offset'      => $offset,
    ];
}

/**
 * Affiche les liens de pagination
 */
function display_pagination($pagination, $search = '') {
    if ($pagination['total_pages'] <= 1) return;
    
    $page = $pagination['page'];
    $total_pages = $pagination['total_pages'];
    $search_param = $search ? '&search=' . urlencode($search) : '';
    
    echo '<nav><ul class="pagination pagination-sm justify-content-center mt-3">';
    
    // Bouton précédent
    $disabled = ($page <= 1) ? 'disabled' : '';
    echo '<li class="page-item ' . $disabled . '">';
    echo '<a class="page-link" href="?page=' . ($page - 1) . $search_param . '">&laquo;</a>';
    echo '</li>';
    
    // Pages
    $start = max(1, $page - 2);
    $end = min($total_pages, $page + 2);
    
    if ($start > 1) {
        echo '<li class="page-item"><a class="page-link" href="?page=1' . $search_param . '">1</a></li>';
        if ($start > 2) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
    }
    
    for ($i = $start; $i <= $end; $i++) {
        $active = ($i === $page) ? 'active' : '';
        echo '<li class="page-item ' . $active . '">';
        echo '<a class="page-link" href="?page=' . $i . $search_param . '">' . $i . '</a>';
        echo '</li>';
    }
    
    if ($end < $total_pages) {
        if ($end < $total_pages - 1) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
        echo '<li class="page-item"><a class="page-link" href="?page=' . $total_pages . $search_param . '">' . $total_pages . '</a></li>';
    }
    
    // Bouton suivant
    $disabled = ($page >= $total_pages) ? 'disabled' : '';
    echo '<li class="page-item ' . $disabled . '">';
    echo '<a class="page-link" href="?page=' . ($page + 1) . $search_param . '">&raquo;</a>';
    echo '</li>';
    
    echo '</ul></nav>';
    echo '<p class="text-center text-muted small">Affichage page ' . $page . ' sur ' . $total_pages . ' (' . $pagination['total'] . ' résultats)</p>';
}

/**
 * Formater une date
 */
function format_date($date, $format = 'd/m/Y') {
    if (empty($date)) return '—';
    return date($format, strtotime($date));
}

/**
 * Formater un montant
 */
function format_montant($montant) {
    return number_format($montant, 0, ',', ' ') . ' FBu';
}
