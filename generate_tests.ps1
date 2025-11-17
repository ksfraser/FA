$phpFiles = Get-ChildItem -Path . -Recurse -Include *.php,*.inc | Where-Object { $_.FullName -notlike "*vendor*" -and $_.FullName -notlike "*tests*" -and $_.FullName -notlike "*composer*" }

foreach ($file in $phpFiles) {
    $relativePath = $file.FullName.Replace($PWD.Path + "\", "").Replace("\", "/")
    $baseName = $file.BaseName
    $testName = ($baseName -replace '[^a-zA-Z0-9_]', '_') + 'Test'
    $testFile = "tests/" + $testName + ".php"
    if (!(Test-Path $testFile)) {
        $content = "<?php

use PHPUnit\Framework\TestCase;

class $testName extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../$relativePath';
        `$this->assertTrue(true);
    }
}
"
        Set-Content -Path $testFile -Value $content
    }
}