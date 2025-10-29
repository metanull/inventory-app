# Concatenate all test files for analysis
# This allows reading all tests at once for faster migration

$testFiles = Get-ChildItem -Path "tests" -Filter "*Test.php" -Recurse

$output = @()
$output += "# ALL TEST FILES CONCATENATED"
$output += "# Generated: $(Get-Date)"
$output += "# Total files: $($testFiles.Count)"
$output += ""

foreach ($file in $testFiles) {
    $relativePath = $file.FullName.Replace((Get-Location).Path + '\', '')
    $output += ""
    $output += "################################################################################"
    $output += "# FILE: $relativePath"
    $output += "################################################################################"
    $output += ""
    $output += Get-Content $file.FullName -Raw
}

$output | Out-File -FilePath "all-tests-concatenated.txt" -Encoding UTF8

Write-Host "Concatenated $($testFiles.Count) test files into all-tests-concatenated.txt"
Write-Host "File size: $((Get-Item all-tests-concatenated.txt).Length / 1MB) MB"
