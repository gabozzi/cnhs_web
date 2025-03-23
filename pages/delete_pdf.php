<?php
// pages/delete_pdf.php
session_start();
require_once '../includes/db.php';

// Redirect if not logged in or not a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: login.php");
    exit();
}

// Check if the request is valid (POST method and PDF ID is provided)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pdf_id'])) {
    $pdf_id = $_POST['pdf_id'];
    $teacher_id = $_SESSION['user_id'];

    // Fetch the file path from the database
    $stmt = $pdo->prepare("SELECT file_path FROM pdf_files WHERE id = ? AND teacher_id = ?");
    $stmt->execute([$pdf_id, $teacher_id]);
    $pdf = $stmt->fetch();

    if ($pdf) {
        // Delete the file from the uploads directory
        if (file_exists($pdf['file_path'])) {
            unlink($pdf['file_path']); // Delete the file
        }

        // Delete the record from the database
        $stmt = $pdo->prepare("DELETE FROM pdf_files WHERE id = ?");
        $stmt->execute([$pdf_id]);

        // Redirect back to the teacher dashboard with a success message
        header("Location: teacher_dashboard.php?delete_success=1");
        exit();
    } else {
        // Redirect back to the teacher dashboard with an error message
        header("Location: teacher_dashboard.php?delete_error=1");
        exit();
    }
} else {
    // Invalid request, redirect to the teacher dashboard
    header("Location: teacher_dashboard.php");
    exit();
}
?>