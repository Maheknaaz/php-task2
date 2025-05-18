<?php
require_once 'config.php';

// Error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Initialize variables with sanitization
$search = isset($_GET['search']) ? trim($conn->real_escape_string($_GET['search'])) : '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Get total records count with search
$sql_count = "SELECT COUNT(*) as total FROM tbl_customers";
$where = [];
$params = [];
$types = '';

if (!empty($search)) {
    $where[] = "(first_name LIKE ? OR last_name LIKE ? OR city LIKE ? OR country LIKE ?)";
    $search_param = "%$search%";
    $params = array_fill(0, 4, $search_param);
    $types = str_repeat('s', count($params));
}

if (!empty($where)) {
    $sql_count .= " WHERE " . implode(' AND ', $where);
}

$stmt_count = $conn->prepare($sql_count);
if (!$stmt_count) {
    die("Error preparing statement: " . $conn->error);
}

if (!empty($params)) {
    $stmt_count->bind_param($types, ...$params);
}

$stmt_count->execute();
$result_count = $stmt_count->get_result();
$total_rows = $result_count->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);

// Get customer data with sorting
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'id';
$order = isset($_GET['order']) && strtolower($_GET['order']) == 'desc' ? 'DESC' : 'ASC';

// Validate sort column
$allowed_sort = ['id', 'first_name', 'last_name', 'city', 'country', 'mobile_number', 'date_n_time'];
$sort = in_array($sort, $allowed_sort) ? $sort : 'id';

// Main query - CHANGED FROM tbl_customers TO customers
$sql = "SELECT * FROM tbl_customers";
if (!empty($where)) {
    $sql .= " WHERE " . implode(' AND ', $where);
}
$sql .= " ORDER BY $sort $order LIMIT ? OFFSET ?";

$params[] = $limit;
$params[] = $offset;
$types .= 'ii';

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}

$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$customers = $result->fetch_all(MYSQLI_ASSOC);

// Close statements
$stmt_count->close();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ultimez Customer Directory</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #f8f9fc;
            --accent-color:rgb(250, 115, 198);
            --text-color: #5a5c69;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fc;
            color: var(--text-color);
        }
        
        .header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            border-radius: 0 0 20px 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        
        .search-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .table-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            padding: 1.5rem;
            overflow: hidden;
        }
        
        .table thead th {
            background-color: var(--primary-color);
            color: white;
            border: none;
            font-weight: 500;
            position: sticky;
            top: 0;
        }
        
        .table th.sortable {
            cursor: pointer;
            transition: all 0.3s;
            position: relative;
            padding-right: 30px;
        }
        
        .table th.sortable:hover {
            background-color: var(--accent-color);
        }
        
        .table th.sortable i {
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
        }
        
        .table tbody tr {
            transition: all 0.2s;
        }
        
        .table tbody tr:hover {
            background-color: rgba(78, 115, 223, 0.05);
            transform: translateX(2px);
        }
        
        .pagination .page-item.active .page-link {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .pagination .page-link {
            color: var(--primary-color);
        }
        
        .no-results {
            padding: 3rem;
            text-align: center;
            color: #6c757d;
        }
        
        .no-results i {
            font-size: 3rem;
            color: #dee2e6;
            margin-bottom: 1rem;
        }
        
        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
            border-left: 4px solid var(--primary-color);
        }
        
        .stats-value {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .stats-label {
            font-size: 0.8rem;
            color: #858796;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .country-flag {
            width: 20px;
            height: 15px;
            margin-right: 8px;
            vertical-align: middle;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1><i class="bi bi-people-fill"></i> Customer Directory</h1>
                    <p class="mb-0">Manage and explore your customer database</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <span class="badge bg-light text-dark">
                        <i class="bi bi-database"></i> <?php echo number_format($total_rows); ?> Records
                    </span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="container">
        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-value"><?php echo number_format($total_rows); ?></div>
                    <div class="stats-label">Total Customers</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-value"><?php echo number_format($total_pages); ?></div>
                    <div class="stats-label">Total Pages</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-value"><?php echo date('M j, Y'); ?></div>
                    <div class="stats-label">Last Updated</div>
                </div>
            </div>
        </div>
        
        <!-- Search Card -->
        <div class="search-card">
            <form method="get" action="">
                <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort); ?>">
                <input type="hidden" name="order" value="<?php echo htmlspecialchars($order); ?>">
                <div class="row">
                    <div class="col-md-8 mb-2">
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" class="form-control form-control-lg" 
                                   placeholder="Search customers by name, city or country..." 
                                   value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex">
                            <button type="submit" class="btn btn-primary btn-lg me-2 flex-grow-1">
                                <i class="bi bi-search"></i> Search
                            </button>
                            <a href="?" class="btn btn-outline-secondary btn-lg">
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Results Card -->
        <div class="table-card">
            <?php if (!empty($customers)) { ?>
                <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>SL.No</th>
                                <th class="sortable" onclick="sortTable('first_name')">
                                    First Name 
                                    <?php if ($sort == 'first_name') echo $order == 'ASC' ? '<i class="bi bi-caret-up-fill"></i>' : '<i class="bi bi-caret-down-fill"></i>'; ?>
                                </th>
                                <th class="sortable" onclick="sortTable('last_name')">
                                    Last Name
                                    <?php if ($sort == 'last_name') echo $order == 'ASC' ? '<i class="bi bi-caret-up-fill"></i>' : '<i class="bi bi-caret-down-fill"></i>'; ?>
                                </th>
                                <th class="sortable" onclick="sortTable('city')">
                                    City
                                    <?php if ($sort == 'city') echo $order == 'ASC' ? '<i class="bi bi-caret-up-fill"></i>' : '<i class="bi bi-caret-down-fill"></i>'; ?>
                                </th>
                                <th class="sortable" onclick="sortTable('country')">
                                    Country
                                    <?php if ($sort == 'country') echo $order == 'ASC' ? '<i class="bi bi-caret-up-fill"></i>' : '<i class="bi bi-caret-down-fill"></i>'; ?>
                                </th>
                                <th>Mobile</th>
                                <th class="sortable" onclick="sortTable('date_n_time')">
                                    Date & Time
                                    <?php if ($sort == 'date_n_time') echo $order == 'ASC' ? '<i class="bi bi-caret-up-fill"></i>' : '<i class="bi bi-caret-down-fill"></i>'; ?>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($customers as $index => $customer) { ?>
                                <tr class="animate__animated animate__fadeIn">
                                    <td><?php echo $index + 1 + $offset; ?></td>
                                    <td><?php echo htmlspecialchars($customer['first_name']); ?></td>
                                    <td><?php echo htmlspecialchars($customer['last_name']); ?></td>
                                    <td>
                                        <i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($customer['city']); ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark">
                                            <?php echo htmlspecialchars($customer['country']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="tel:<?php echo htmlspecialchars($customer['mobile_number']); ?>" class="text-decoration-none">
                                            <i class="bi bi-telephone"></i> <?php echo htmlspecialchars($customer['mobile_number']); ?>
                                        </a>
                                    </td>
                                    <td>
                                        <small><?php echo date('M j, Y g:i A', strtotime($customer['date_n_time'])); ?></small>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1) { ?>
                    <nav aria-label="Page navigation">
                        <ul class="pagination">
                            <?php if ($page > 1) { ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=1&search=<?php echo urlencode($search); ?>&sort=<?php echo $sort; ?>&order=<?php echo $order; ?>" aria-label="First">
                                        <i class="bi bi-chevron-double-left"></i>
                                    </a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page-1; ?>&search=<?php echo urlencode($search); ?>&sort=<?php echo $sort; ?>&order=<?php echo $order; ?>" aria-label="Previous">
                                        <i class="bi bi-chevron-left"></i>
                                    </a>
                                </li>
                            <?php } ?>
                            
                            <?php 
                            // Show page numbers
                            $start = max(1, $page - 2);
                            $end = min($total_pages, $page + 2);
                            
                            if ($start > 1) {
                                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                            }
                            
                            for ($i = $start; $i <= $end; $i++) { 
                                $active = ($i == $page) ? 'active' : '';
                            ?>
                                <li class="page-item <?php echo $active; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&sort=<?php echo $sort; ?>&order=<?php echo $order; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php } ?>
                            
                            <?php if ($end < $total_pages) { ?>
                                <li class="page-item disabled"><span class="page-link">...</span></li>
                            <?php } ?>
                            
                            <?php if ($page < $total_pages) { ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page+1; ?>&search=<?php echo urlencode($search); ?>&sort=<?php echo $sort; ?>&order=<?php echo $order; ?>" aria-label="Next">
                                        <i class="bi bi-chevron-right"></i>
                                    </a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $total_pages; ?>&search=<?php echo urlencode($search); ?>&sort=<?php echo $sort; ?>&order=<?php echo $order; ?>" aria-label="Last">
                                        <i class="bi bi-chevron-double-right"></i>
                                    </a>
                                </li>
                            <?php } ?>
                        </ul>
                    </nav>
                <?php } ?>
            <?php } else { ?>
                <div class="no-results">
                    <i class="bi bi-people"></i>
                    <h4>No customers found</h4>
                    <p>Try adjusting your search or filter to find what you're looking for.</p>
                    <a href="?" class="btn btn-primary">
                        <i class="bi bi-arrow-counterclockwise"></i> Reset Search
                    </a>
                </div>
            <?php } ?>
        </div>
        
        <footer class="text-center mt-5 mb-4 text-muted">
            <p>Ultimez Customer Directory &copy; <?php echo date('Y'); ?></p>
        </footer>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function sortTable(column) {
            const url = new URL(window.location.href);
            const sort = url.searchParams.get('sort');
            const order = url.searchParams.get('order');
            
            let newOrder = 'asc';
            if (sort === column && order === 'asc') {
                newOrder = 'desc';
            }
            
            url.searchParams.set('sort', column);
            url.searchParams.set('order', newOrder);
            window.location.href = url.toString();
        }
        
        // Add animation to table rows
        document.addEventListener('DOMContentLoaded', function() {
            const rows = document.querySelectorAll('tbody tr');
            rows.forEach((row, index) => {
                row.style.animationDelay = `${index * 0.05}s`;
            });
        });
    </script>
</body>
</html>