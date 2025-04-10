<?php
// Set the appropriate headers for CSV file download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=question_template.csv');

// Open the output stream
$output = fopen('php://output', 'w');

// Define the CSV header row
$header = ['test_id', 'que_desc', 'ans1', 'ans2', 'ans3', 'ans4', 'true_ans'];

// Write the header row to the CSV file
fputcsv($output, $header);

// Optionally, you can add sample rows by uncommenting the following lines:
// $sampleData = ['1', 'What is PHP?', 'student01', 'Answer 1', 'Answer 2', 'Answer 3', 'Answer 4', 'Answer 1'];
// fputcsv($output, $sampleData);

// Close the output stream
fclose($output);
exit();
