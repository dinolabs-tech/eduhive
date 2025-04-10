<?php
// Set the appropriate headers for CSV file download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=enrollment_template.csv');

// Open the output stream
$output = fopen('php://output', 'w');

// Define the CSV header row
$header = ['ID', 'NAME', 'DATE_OF_BIRTH', 'CLASS', 'ARM', 'PASSWORD'];

// Write the header row to the CSV file
fputcsv($output, $header);

// Optionally, you can add sample rows by uncommenting the following lines:
// $sampleData = ['1', 'What is PHP?', 'student01', 'Answer 1', 'Answer 2', 'Answer 3', 'Answer 4', 'Answer 1'];
// fputcsv($output, $sampleData);

// Close the output stream
fclose($output);
exit();
