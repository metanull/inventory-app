# Fix shaped array PHPDoc syntax that breaks OpenAPI generator

$files = Get-ChildItem -Path "app" -Filter "*.php" -Recurse

foreach ($file in $files) {
    $content = Get-Content $file.FullName -Raw
    $originalContent = $content
    
    # Replace shaped array syntax with standard array syntax
    $content = $content -replace '@return array\{([^}]+)\}', '@return array'
    
    # Only write if content changed
    if ($content -ne $originalContent) {
        Set-Content -Path $file.FullName -Value $content -NoNewline
        Write-Host "Fixed: $($file.FullName)"
    }
}

Write-Host "Done fixing PHPDoc shaped arrays"
