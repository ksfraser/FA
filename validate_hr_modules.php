<?php
/**
 * FrontAccounting HR and Project Management Integration Test
 *
 * Simple validation script to ensure all modules are properly integrated
 */

// Autoload check
echo "Checking module structure...\n";

// Check if all required files exist
$requiredFiles = [
    'modules/EmployeeManagement/EmployeeService.php',
    'modules/EmployeeManagement/Entities.php',
    'modules/EmployeeManagement/Events.php',
    'modules/EmployeeManagement/EmployeeException.php',
    'modules/TimesheetManagement/TimesheetService.php',
    'modules/TimesheetManagement/Entities.php',
    'modules/TimesheetManagement/Events.php',
    'modules/TimesheetManagement/TimesheetException.php',
    'modules/ProjectManagement/ProjectService.php',
    'modules/ProjectManagement/Entities.php',
    'modules/ProjectManagement/Events.php',
    'modules/ProjectManagement/ProjectException.php',
    'modules/HRProjectManagementService.php',
    'HR_PROJECT_MANAGEMENT_README.md'
];

$allFilesExist = true;
foreach ($requiredFiles as $file) {
    if (file_exists($file)) {
        echo "✓ $file exists\n";
    } else {
        echo "✗ $file missing\n";
        $allFilesExist = false;
    }
}

// Syntax check
echo "\nChecking PHP syntax...\n";
$syntaxOk = true;
$phpFiles = array_filter($requiredFiles, fn($f) => pathinfo($f, PATHINFO_EXTENSION) === 'php');

foreach ($phpFiles as $file) {
    $output = shell_exec("php -l \"$file\" 2>&1");
    if (strpos($output, 'No syntax errors detected') !== false) {
        echo "✓ $file syntax OK\n";
    } else {
        echo "✗ $file syntax error: $output\n";
        $syntaxOk = false;
    }
}

// Summary
echo "\n=== VALIDATION SUMMARY ===\n";
echo "Files present: " . ($allFilesExist ? "PASS" : "FAIL") . "\n";
echo "Syntax check: " . ($syntaxOk ? "PASS" : "FAIL") . "\n";

if ($allFilesExist && $syntaxOk) {
    echo "\n🎉 All HR and Project Management modules are ready!\n";
    echo "\nNext steps:\n";
    echo "1. Create the required database tables (see README)\n";
    echo "2. Configure dependency injection container\n";
    echo "3. Integrate with FrontAccounting's existing modules\n";
    echo "4. Test with sample data\n";
} else {
    echo "\n❌ Issues found. Please check the errors above.\n";
}