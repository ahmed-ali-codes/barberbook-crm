<?php
require_once '../includes/auth.php';
require_once '../includes/storage.php';

require_login();

// Handle Actions (Confirm, Complete, Cancel, Delete)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $action = $_GET['action'];
    
    if ($action === 'delete') {
        delete_appointment($id);
    } else {
        $status_map = [
            'confirm' => 'Confirmed',
            'complete' => 'Completed',
            'cancel' => 'Cancelled',
            'pending' => 'Pending'
        ];
        if (isset($status_map[$action])) {
            update_appointment_status($id, $status_map[$action]);
        }
    }
    
    if (isset($_GET['ajax'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'action' => $action]);
        exit;
    }
    
    header('Location: appointments.php');
    exit;
}

$appointments = get_appointments();

// Filtering
$filter_stylist = $_GET['filter_stylist'] ?? '';
$filter_service = $_GET['filter_service'] ?? '';
$filter_type = $_GET['filter_type'] ?? '';
$filter_date = $_GET['filter_date'] ?? '';
$filter_status = $_GET['filter_status'] ?? '';

if ($filter_stylist || $filter_service || $filter_type || $filter_date || $filter_status) {
    $appointments = array_filter($appointments, function($app) use ($filter_stylist, $filter_service, $filter_type, $filter_date, $filter_status) {
        $match = true;
        if ($filter_stylist && $app['stylist'] !== $filter_stylist) $match = false;
        if ($filter_service && $app['service_type'] !== $filter_service) $match = false;
        if ($filter_type && isset($app['appointment_type']) && $app['appointment_type'] !== $filter_type) $match = false;
        if ($filter_date && $app['date'] !== $filter_date) $match = false;
        if ($filter_status && $app['status'] !== $filter_status) $match = false;
        return $match;
    });
}

// Pagination
$per_page = 50;
$total_records = count($appointments);
$total_pages = ceil($total_records / $per_page);
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$start_index = ($page - 1) * $per_page;

$paginated_appointments = array_slice($appointments, $start_index, $per_page);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointments | <?= CLINIC_NAME ?></title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        .details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            font-size: 0.9rem;
            color: var(--text-muted);
            margin-bottom: 10px;
        }
        .details-grid strong {
            color: var(--off-white);
        }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="container">
        <a href="dashboard.php" class="navbar-brand">
            <span style="font-size: 1.5rem; color: var(--gold);">&#x2702;</span> Admin
        </a>
        <input type="checkbox" id="nav-toggle" class="nav-toggle">
        <label for="nav-toggle" class="nav-toggle-label"><span></span></label>
        <ul class="navbar-nav">
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="appointments.php" class="active">Appointments</a></li>
            <li><a href="dashboard.php?logout=1" class="btn-action" onclick="return confirm('Are you sure you want to log out?');">Logout</a></li>
        </ul>
    </div>
</nav>

<div class="container" style="margin-top: 30px;">
    
    <div class="filter-section">
        <form method="GET" action="appointments.php" style="display: flex; gap: 15px; flex-wrap: wrap; width: 100%; align-items: flex-end;">
            <div class="form-group">
                <label>Stylist</label>
                <select name="filter_stylist">
                    <option value="">All Stylists</option>
                    <?php foreach ($STYLISTS as $stylist): ?>
                        <option value="<?= htmlspecialchars($stylist) ?>" <?= $filter_stylist === $stylist ? 'selected' : '' ?>><?= htmlspecialchars($stylist) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Service Type</label>
                <select name="filter_service">
                    <option value="">All Services</option>
                    <?php foreach ($SERVICE_TYPES as $service): ?>
                        <option value="<?= htmlspecialchars($service) ?>" <?= $filter_service === $service ? 'selected' : '' ?>><?= htmlspecialchars($service) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Appt Type</label>
                <select name="filter_type">
                    <option value="">All Types</option>
                    <option value="Walk-in" <?= $filter_type === 'Walk-in' ? 'selected' : '' ?>>Walk-in</option>
                    <option value="Pre-booked" <?= $filter_type === 'Pre-booked' ? 'selected' : '' ?>>Pre-booked</option>
                </select>
            </div>
            <div class="form-group">
                <label>Date</label>
                <input type="date" name="filter_date" value="<?= htmlspecialchars($filter_date) ?>">
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="filter_status">
                    <option value="">All Statuses</option>
                    <option value="Pending" <?= $filter_status === 'Pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="Confirmed" <?= $filter_status === 'Confirmed' ? 'selected' : '' ?>>Confirmed</option>
                    <option value="Completed" <?= $filter_status === 'Completed' ? 'selected' : '' ?>>Completed</option>
                    <option value="Cancelled" <?= $filter_status === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                </select>
            </div>
            <div class="form-group" style="min-width: auto;">
                <button type="submit" class="btn btn-primary" style="padding: 12px 20px;">Filter</button>
                <a href="appointments.php" class="btn btn-action" style="padding: 12px 20px; border: 1px solid var(--text-muted); color: var(--text-muted);">Reset</a>
            </div>
        </form>
    </div>

    <div class="card">
        <h3 style="color: var(--gold); margin-bottom: 20px;">All Appointments (<?= $total_records ?>)</h3>
        
        <?php if (empty($paginated_appointments)): ?>
            <p style="text-align:center; color: var(--text-muted); padding: 40px 0;">No appointments found matching criteria.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 15%; min-width: 120px;">Date / Time</th>
                            <th style="width: 30%; min-width: 200px;">Details</th>
                            <th style="width: 25%; min-width: 180px;">Client Info</th>
                            <th style="width: 15%; min-width: 120px;">Status</th>
                            <th style="width: 15%; min-width: 130px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($paginated_appointments as $app): ?>
                            <tr class="table-row" data-status="<?= strtolower(htmlspecialchars($app['status'])) ?>">
                                <td>
                                    <strong><?= htmlspecialchars($app['date']) ?></strong><br>
                                    <span style="color: var(--text-muted);"><?= htmlspecialchars($app['time']) ?></span><br>
                                    <small><?= htmlspecialchars($app['duration_estimate']) ?></small>
                                </td>
                                <td>
                                    <div class="details-grid">
                                        <div><strong>Service:</strong> <br><?= htmlspecialchars($app['service_type']) ?></div>
                                        <div><strong>Stylist:</strong> <br><?= htmlspecialchars($app['stylist']) ?></div>
                                        <div style="grid-column: span 2;"><strong>Type:</strong> <?= htmlspecialchars($app['appointment_type'] ?? 'N/A') ?></div>
                                        <?php if(!empty($app['notes'])): ?>
                                        <div style="grid-column: span 2;"><strong>Notes:</strong> <em><?= htmlspecialchars($app['notes']) ?></em></div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($app['name']) ?></strong><br>
                                    <a href="tel:<?= htmlspecialchars($app['phone']) ?>"><?= htmlspecialchars($app['phone']) ?></a><br>
                                    <a href="mailto:<?= htmlspecialchars($app['email']) ?>"><?= htmlspecialchars($app['email']) ?></a>
                                </td>
                                <td>
                                    <span class="status-badge status-<?= strtolower($app['status']) ?>">
                                        <?= htmlspecialchars($app['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button class="dropdown-btn">Actions &#x25BC;</button>
                                        <div class="dropdown-content">
                                            <a href="appointments.php?action=pending&id=<?= urlencode($app['id']) ?>" title="Pending" class="action-pending">&#x23F3; Pending</a>
                                            <a href="appointments.php?action=confirm&id=<?= urlencode($app['id']) ?>" title="Confirm" class="action-confirm">&#x2713; Confirm</a>
                                            <a href="appointments.php?action=complete&id=<?= urlencode($app['id']) ?>" title="Complete" class="action-complete">&#x2714; Complete</a>
                                            <a href="appointments.php?action=cancel&id=<?= urlencode($app['id']) ?>" title="Cancel" class="action-cancel">&#x2715; Cancel</a>
                                            <a href="appointments.php?action=delete&id=<?= urlencode($app['id']) ?>" class="delete" title="Delete">&#x1F5D1; Delete</a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?= $i ?>&filter_stylist=<?= urlencode($filter_stylist) ?>&filter_service=<?= urlencode($filter_service) ?>&filter_type=<?= urlencode($filter_type) ?>&filter_date=<?= urlencode($filter_date) ?>&filter_status=<?= urlencode($filter_status) ?>" class="<?= $i === $page ? 'active' : '' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('click', function(e) {
    // Mobile menu toggle
    var toggle = document.getElementById('nav-toggle');
    if (toggle && toggle.checked && !e.target.closest('.navbar')) {
        toggle.checked = false;
    }
    
    // Actions Dropdown logic
    const btn = e.target.closest('.dropdown-btn');
    
    // Close all open dropdowns first, unless clicking inside the current one
    document.querySelectorAll('.dropdown-content.show').forEach(m => {
        if (btn && m === btn.nextElementSibling) return;
        m.classList.remove('show');
    });
    
    if (btn) {
        e.preventDefault(); // prevent form submit or scroll
        const menu = btn.nextElementSibling;
        
        if (menu.classList.contains('show')) {
            menu.classList.remove('show');
        } else {
            menu.classList.add('show');
            
            // Calculate position
            const rect = btn.getBoundingClientRect();
            const menuRect = menu.getBoundingClientRect();
            
            // Default to opening down
            let topPos = rect.bottom + 5;
            
            // If it goes off the bottom of the screen, open it UP instead!
            if (rect.bottom + menuRect.height > window.innerHeight && rect.top > menuRect.height) {
                topPos = rect.top - menuRect.height - 5;
            }
            
            menu.style.top = topPos + 'px';
            
            // Align to right edge of button
            menu.style.left = (rect.right - menuRect.width) + 'px';
        }
    }
    
    // AJAX for Action Links
    const link = e.target.closest('.dropdown-content a');
    if (link) {
        e.preventDefault();
        
        // If delete, confirm first
        if (link.classList.contains('delete') && !confirm('Are you sure you want to delete this appointment?')) {
            return;
        }
        
        const url = link.getAttribute('href');
        const row = link.closest('tr');
        
        // Add loading state
        link.style.opacity = '0.5';
        
        fetch(url + '&ajax=1')
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (data.action === 'delete') {
                        row.remove();
                    } else {
                        const statusMap = {
                            'confirm': 'Confirmed',
                            'complete': 'Completed',
                            'cancel': 'Cancelled',
                            'pending': 'Pending'
                        };
                        const newStatus = statusMap[data.action];
                        const badge = row.querySelector('.status-badge');
                        if (badge) {
                            badge.className = 'status-badge status-' + newStatus.toLowerCase();
                            badge.textContent = newStatus;
                        }
                        // Instantly update the row data attribute so CSS hides/shows correct dropdown options
                        row.setAttribute('data-status', newStatus.toLowerCase());
                    }
                    link.closest('.dropdown-content').classList.remove('show');
                }
            })
            .catch(err => {
                console.error('AJAX Error:', err);
                window.location.href = url; // Fallback to normal navigation
            })
            .finally(() => {
                link.style.opacity = '1';
            });
    }
});
</script>
</body>
</html>
