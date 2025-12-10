<?php
// Download CSV template for bulk upload
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="grades_template.csv"');

$output = fopen('php://output', 'w');

// Header row
fputcsv($output, ['Student ID', 'Subject Code', 'Obtained Marks', 'Total Marks', 'Semester', 'Academic Year']);

// Example rows
fputcsv($output, ['470', 'CS101', '85', '100', 'Semester 1', '2024/2025']);
fputcsv($output, ['470', 'CS102', '78', '100', 'Semester 1', '2024/2025']);
fputcsv($output, ['471', 'BA101', '92', '100', 'Semester 1', '2024/2025']);

fclose($output);
exit;
?>

