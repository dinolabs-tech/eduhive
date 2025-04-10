<?php
// Database connection
include 'db_connection.php';

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Directory to save the backup file (site root folder)
$backupDir = __DIR__;
// Use a fixed filename instead of timestamp-based
$backupFile = $backupDir . '/backup_' . $dbname . '.sql';
$logFile = $backupDir . '/backup.log';

// Define backup interval (24 hours = 86400 seconds)
$backupInterval = 86400;

// Determine if a backup should be performed
// Check if file doesn't exist or if interval has passed
$shouldBackup = !file_exists($backupFile) || (time() - filemtime($backupFile) >= $backupInterval);

if ($shouldBackup) {
    // Specify mysqldump path (adjust if needed, e.g., '/usr/bin/mysqldump')
    $mysqldumpPath = 'mysqldump';

    // Build the command with error redirection
    $command = sprintf(
        '%s --host=%s --user=%s --password=%s %s > %s 2>&1',
        escapeshellarg($mysqldumpPath),
        escapeshellarg($servername),
        escapeshellarg($username),
        escapeshellarg($password),
        escapeshellarg($dbname),
        escapeshellarg($backupFile)
    );

    // Execute and capture output
    exec($command, $output, $returnVar);

    // Log detailed results
    $logMessage = date('[Y-m-d H:i:s]') . " Command executed: " . $command . PHP_EOL;
    $logMessage .= date('[Y-m-d H:i:s]') . " Return code: " . $returnVar . PHP_EOL;
    if ($returnVar === 0) {
        $logMessage .= date('[Y-m-d H:i:s]') . " Backup successful: " . $backupFile . " (" . filesize($backupFile) . " bytes)" . PHP_EOL;
    } else {
        $logMessage .= date('[Y-m-d H:i:s]') . " Error: Backup failed with status code " . $returnVar . PHP_EOL;
        $logMessage .= date('[Y-m-d H:i:s]') . " Output: " . implode("\n", $output) . PHP_EOL;
    }
    file_put_contents($logFile, $logMessage, FILE_APPEND);

    // Check file contents
    if (file_exists($backupFile) && filesize($backupFile) === 0) {
        file_put_contents($logFile, date('[Y-m-d H:i:s]') . " Warning: Backup file is empty: " . $backupFile . PHP_EOL, FILE_APPEND);
    }
}

$conn->close();
?>