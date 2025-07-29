<?php
session_start();
include 'include/connect.php';

// Rows per page selection
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$limit = in_array($limit, [10, 25, 50, 100]) ? $limit : 10;

// Current page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = $page < 1 ? 1 : $page;

$offset = ($page - 1) * $limit;

// Get total records
$total_query = "SELECT COUNT(*) as total FROM user_logs";
$total_result = mysqli_query($conn, $total_query);
$total_row = mysqli_fetch_assoc($total_result);
$total_records = $total_row['total'];
$total_pages = ceil($total_records / $limit);

// Fix invalid page number
if ($page > $total_pages && $total_pages > 0) {
    $page = $total_pages;
} elseif ($page < 1) {
    $page = 1;
}

// Fetch paginated logs
$query = "SELECT * FROM user_logs ORDER BY login DESC LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PMS | Admin</title>
    <link rel="stylesheet" href="../vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../vendor/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .table-footer {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 1rem 0;
            gap: 1rem;
            text-align: center;
        }

        .entries-select {
            display: flex;
            align-items: center;
        }

        .entries-select label {
            margin-bottom: 0;
            white-space: nowrap;
        }

        .pagination-wrapper {
            flex-grow: 1;
            text-align: center;
        }
    </style>
</head>

<body>
    <?php include('include/admin_header.php'); ?>

    <div class="main-content">
        <div class="wrap-content container" id="container">
            <section id="page-title">
                <div class="row">
                    <div>
                        <h1 class="main-title">User Logs</h1>
                    </div>
                </div>
            </section>

            <div class="row">
                <div class="col-md-12">
                    <h5 class="over-title">Manage User Logs</h5>

                    <table class="table table-striped table-bordered">
                        <thead class="text-center">
                            <tr>
                                <th>No.</th>
                                <th>Role</th>
                                <th>User Name</th>
                                <th>Login Time</th>
                                <th>Logout Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $cnt = $offset + 1;
                            if (mysqli_num_rows($result) > 0) {
                                while ($row = mysqli_fetch_assoc($result)) {
                                    $logout = $row['logout'] ? htmlspecialchars($row['logout']) : '<span class="text-muted">Still Logged In</span>';
                            ?>
                                    <tr>
                                        <td class="text-center"><?php echo $cnt++; ?></td>
                                        <td class="text-center"><?php echo htmlspecialchars($row['role']); ?></td>
                                        <td class="text-center"><?php echo htmlspecialchars($row['username']); ?></td>
                                        <td class="text-center"><?php echo htmlspecialchars($row['login']); ?></td>
                                        <td class="text-center"><?php echo $logout; ?></td>
                                    </tr>
                                <?php
                                }
                            } else {
                                ?>
                                <tr>
                                    <td colspan="5" class="text-center">No user logs found</td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>

                    <div class="table-footer">
                        <!-- Show Entries -->
                        <div class="entries-select">
                            <form method="GET" class="d-flex align-items-center">
                                <label for="limit" class="mr-2 mb-0 me-2">Show entries:</label>
                                <select name="limit" id="limit" class="form-control ml-2 custom-shadow-select" onchange="this.form.submit()">
                                    <option value="10" <?= $limit == 10 ? 'selected' : '' ?>>10</option>
                                    <option value="25" <?= $limit == 25 ? 'selected' : '' ?>>25</option>
                                    <option value="50" <?= $limit == 50 ? 'selected' : '' ?>>50</option>
                                    <option value="100" <?= $limit == 100 ? 'selected' : '' ?>>100</option>
                                </select>
                                <input type="hidden" name="page" value="<?= $page ?>">
                            </form>
                        </div>

                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <div class="pagination-wrapper">
                                <nav aria-label="Page navigation">
                                    <ul class="pagination mb-0">
                                        <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                            <a class="page-link" href="?limit=<?= $limit ?>&page=<?= $page - 1 ?>">Previous</a>
                                        </li>
                                        <?php
                                        $start = max(1, $page - 2);
                                        $end = min($total_pages, $page + 2);
                                        for ($i = $start; $i <= $end; $i++):
                                        ?>
                                            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                                <a class="page-link" href="?limit=<?= $limit ?>&page=<?= $i ?>">
                                                    <?= $i ?>
                                                </a>
                                            </li>
                                        <?php endfor; ?>
                                        <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                            <a class="page-link" href="?limit=<?= $limit ?>&page=<?= $page + 1 ?>">Next</a>
                                        </li>
                                    </ul>
                                </nav>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Info Text Below -->
                    <div class="text-muted text-center mt-2">
                        Showing <?= $offset + 1 ?> to <?= min($offset + $limit, $total_records) ?> of <?= $total_records ?> entries
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>

</html>