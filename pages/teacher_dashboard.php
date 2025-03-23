<?php
// pages/teacher_dashboard.php
session_start();
require_once '../includes/db.php';

// Redirect if not logged in or not a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: login.php");
    exit();
}

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['pdf'])) {
    $subject_name = $_POST['subject_name'];
    $teacher_id = $_SESSION['user_id'];
    $upload_dir = '../assets/uploads/';
    $file_name = basename($_FILES['pdf']['name']);
    $file_path = $upload_dir . $file_name;

    // Validate file type
    if (pathinfo($file_name, PATHINFO_EXTENSION) === 'pdf') {
        move_uploaded_file($_FILES['pdf']['tmp_name'], $file_path);

        // Save metadata to database
        $stmt = $pdo->prepare("INSERT INTO pdf_files (teacher_id, subject_name, file_path) VALUES (?, ?, ?)");
        $stmt->execute([$teacher_id, $subject_name, $file_path]);
    } else {
        $error = "Only PDF files are allowed.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard</title>
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
        <h1 class="my-4">Welcome, Teacher!</h1>

        <!-- Upload PDF Form -->
        <div class="mb-3">
            <form method="POST" enctype="multipart/form-data" id="uploadForm">
                <div class="mb-3">
                    <label for="subject_name" class="form-label">Subject Name</label>
                    <input type="text" class="form-control" id="subject_name" name="subject_name" required>
                    <div class="invalid-feedback" id="subjectNameError">Please enter a subject name.</div>
                </div>
                <div class="mb-3">
                    <label for="pdf" class="form-label">Upload PDF</label>
                    <input type="file" class="form-control" id="pdf" name="pdf" accept=".pdf" required>
                    <div class="invalid-feedback" id="pdfError">Please select a valid PDF file.</div>
                </div>
                <button type="button" class="btn btn-primary" id="openUploadModal">Upload PDF</button>
            </form>
        </div>

        <!-- Confirmation Modal -->
        <div class="modal fade" id="uploadConfirmationModal" tabindex="-1"
            aria-labelledby="uploadConfirmationModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="uploadConfirmationModalLabel">Confirm Upload</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        Are you sure you want to upload this Lesson?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="confirmUpload">Upload</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Uploaded PDFs -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Uploaded Lessons</h5>
            </div>
            <div class="card-body">
                <?php
                // Fetch uploaded PDFs
                $stmt = $pdo->prepare("SELECT * FROM pdf_files WHERE teacher_id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $pdfs = $stmt->fetchAll();

                if ($pdfs): ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Subject & Lesson Names</th>
                                <th>File</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pdfs as $pdf): ?>
                            <tr>
                                <td><?php echo $pdf['subject_name']; ?></td>
                                <td><a href="<?php echo $pdf['file_path']; ?>" target="_blank">View PDF</a></td>
                                <td>
                                    <form method="POST" action="delete_pdf.php"
                                        onsubmit="return confirm('Are you sure you want to delete this file?');">
                                        <input type="hidden" name="pdf_id" value="<?php echo $pdf['id']; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <p class="text-muted">No Lessons uploaded yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    const uploadConfirmationModal = new bootstrap.Modal(document.getElementById('uploadConfirmationModal'));
    const openUploadModalButton = document.getElementById('openUploadModal');
    const confirmUploadButton = document.getElementById('confirmUpload');
    const uploadForm = document.getElementById('uploadForm');

    // Get references to the input fields and error messages
    const subjectNameInput = document.getElementById('subject_name');
    const pdfInput = document.getElementById('pdf');
    const subjectNameError = document.getElementById('subjectNameError');
    const pdfError = document.getElementById('pdfError');

    openUploadModalButton.addEventListener('click', () => {
        const subjectName = subjectNameInput.value.trim();
        const pdfFile = pdfInput.files[0];

        let isValid = true;

        // Validate subject name
        if (!subjectName) {
            subjectNameInput.classList.add('is-invalid');
            subjectNameError.style.display = 'block';
            isValid = false;
        } else {
            subjectNameInput.classList.remove('is-invalid');
            subjectNameError.style.display = 'none';
        }

        // Validate PDF file
        if (!pdfFile) {
            pdfInput.classList.add('is-invalid');
            pdfError.textContent = "Please select a PDF file.";
            pdfError.style.display = 'block';
            isValid = false;
        } else if (pdfFile.type !== 'application/pdf') {
            pdfInput.classList.add('is-invalid');
            pdfError.textContent = "Only PDF files are allowed.";
            pdfError.style.display = 'block';
            isValid = false;
        } else {
            pdfInput.classList.remove('is-invalid');
            pdfError.style.display = 'none';
        }

        // If inputs are valid, show the confirmation modal
        if (isValid) {
            uploadConfirmationModal.show();
        }
    });

    // Submit the form when the user confirms the upload
    confirmUploadButton.addEventListener('click', () => {
        uploadForm.submit();
    });
    </script>
</body>

</html>