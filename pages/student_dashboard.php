<?php
session_start();
require_once '../includes/db.php';

// Redirect if not logged in or not a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

// Handle search query
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'none'; // Default filter is 'none'

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>

<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand">Student Dashboard</a>

            <!-- Toggle Button for Mobile -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Navbar Links -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container">
        <h1 class="my-4">Welcome, Student!</h1>

        <!-- Search Bar -->
        <form method="GET" class="mb-4">
            <div class="search-container">
                <input type="text" class="form-control search-input" name="search"
                    placeholder="Search by subject or teacher..." value="<?php echo htmlspecialchars($search); ?>">
                <select class="form-select filter-select" name="filter">
                    <option value="none" <?php echo ($filter === 'none') ? 'selected' : ''; ?>>None</option>
                    <option value="subject" <?php echo ($filter === 'subject') ? 'selected' : ''; ?>>Subject</option>
                    <option value="teacher" <?php echo ($filter === 'teacher') ? 'selected' : ''; ?>>Teacher</option>
                </select>
                <button type="submit" class="btn btn-primary">Search</button>
            </div>
        </form>

        <!-- Available PDFs -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Available Lessons</h5>
            </div>
            <div class="card-body">
                <?php
                // Fetch all PDFs with teacher's name, filtered by search query
                $query = "SELECT pdf_files.*, users.name AS teacher_name FROM pdf_files JOIN users ON pdf_files.teacher_id = users.id";
                if (!empty($search) && $filter !== 'none') {
                    if ($filter === 'subject') {
                        $query .= " WHERE pdf_files.subject_name LIKE :search";
                    } elseif ($filter === 'teacher') {
                        $query .= " WHERE users.name LIKE :search";
                    }
                }
                $stmt = $pdo->prepare($query);

                if (!empty($search) && $filter !== 'none') {
                    $stmt->execute(['search' => "%$search%"]);
                } else {
                    $stmt->execute();
                }
                $pdfs = $stmt->fetchAll();

                if ($pdfs): ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Subject & Lesson Name</th>
                                <th>Teacher Name</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pdfs as $pdf): ?>
                            <tr>
                                <td><?php echo $pdf['subject_name']; ?></td>
                                <td><?php echo $pdf['teacher_name']; ?></td>
                                <td>
                                    <a href="<?php echo $pdf['file_path']; ?>" download
                                        class="btn btn-primary btn-sm">Download</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <p class="text-muted">No Lessons available at the moment...</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>