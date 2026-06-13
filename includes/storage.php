<?php
// Storage functionality using flat-file JSON

define('DATA_DIR', __DIR__ . '/../data');
define('DATA_FILE', DATA_DIR . '/appointments.json');

// Initialize data directory and file
function init_storage() {
    if (!is_dir(DATA_DIR)) {
        mkdir(DATA_DIR, 0755, true);
    }
    
    // Protect data directory from direct web access
    $htaccess = DATA_DIR . '/.htaccess';
    if (!file_exists($htaccess)) {
        file_put_contents($htaccess, "Order allow,deny\nDeny from all");
    }

    if (!file_exists(DATA_FILE)) {
        file_put_contents(DATA_FILE, json_encode([]));
    }
}

// Read all appointments
function get_appointments() {
    init_storage();
    $data = file_get_contents(DATA_FILE);
    return json_decode($data, true) ?: [];
}

// Save all appointments
function save_appointments($appointments) {
    init_storage();
    return file_put_contents(DATA_FILE, json_encode($appointments, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// Add a new appointment
function add_appointment($data) {
    $appointments = get_appointments();
    
    $appointment = [
        'id' => uniqid('bb_'),
        'created_at' => date('Y-m-d H:i:s'),
        'name' => sanitize_input($data['name']),
        'phone' => sanitize_input($data['phone']),
        'email' => sanitize_input($data['email']),
        'date' => sanitize_input($data['date']),
        'time' => sanitize_input($data['time']),
        'service_type' => sanitize_input($data['service_type']),
        'stylist' => sanitize_input($data['stylist']),
        'appointment_type' => sanitize_input($data['appointment_type']), // Walk-in or Pre-booked
        'duration_estimate' => sanitize_input($data['duration_estimate'] ?? '30 min'),
        'notes' => sanitize_input($data['notes'] ?? ''),
        'status' => 'Pending' // Pending, Confirmed, Completed, Cancelled
    ];
    
    // Insert at beginning
    array_unshift($appointments, $appointment);
    save_appointments($appointments);
    
    return $appointment['id'];
}

// Update appointment status
function update_appointment_status($id, $status) {
    $appointments = get_appointments();
    $found = false;
    foreach ($appointments as &$app) {
        if ($app['id'] === $id) {
            $app['status'] = sanitize_input($status);
            $found = true;
            break;
        }
    }
    if ($found) {
        save_appointments($appointments);
        return true;
    }
    return false;
}

// Delete an appointment
function delete_appointment($id) {
    $appointments = get_appointments();
    $initial_count = count($appointments);
    $appointments = array_filter($appointments, function($app) use ($id) {
        return $app['id'] !== $id;
    });
    
    if (count($appointments) < $initial_count) {
        // Re-index array
        $appointments = array_values($appointments);
        save_appointments($appointments);
        return true;
    }
    return false;
}

// Get appointment by ID
function get_appointment($id) {
    $appointments = get_appointments();
    foreach ($appointments as $app) {
        if ($app['id'] === $id) {
            return $app;
        }
    }
    return null;
}

// Utility: Sanitize input
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Initialization on load
init_storage();
?>
