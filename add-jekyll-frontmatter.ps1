# PowerShell script to add Jekyll front matter to model documentation files

$modelsDir = "c:\Users\phave\Documents\Development\inventory-app\docs\models"
$files = Get-ChildItem -Path $modelsDir -Filter "*.md" | Where-Object { $_.Name -ne "index.md" }

# List of files that already have front matter (we already processed these)
$processedFiles = @("Item.md", "Address.md", "Collection.md", "Partner.md","Artist.md","Author.md","AvailableImage.md")

foreach ($file in $files) {
    if ($processedFiles -contains $file.Name) {
        Write-Host "Skipping already processed file: $($file.Name)" -ForegroundColor Yellow
        continue
    }
    
    # Extract the model name from filename (remove .md extension)
    $modelName = [System.IO.Path]::GetFileNameWithoutExtension($file.Name)
    
    Write-Host "Processing: $($file.Name)" -ForegroundColor Green
    
    # Read the current content
    $content = Get-Content -Path $file.FullName -Raw
    
    # Check if the file already has front matter
    if ($content.StartsWith("---")) {
        Write-Host "  - Already has front matter, skipping" -ForegroundColor Yellow
        continue
    }
    
    # Create the front matter
    $frontMatter = @"
---
layout: default
title: $modelName
parent: Database Models
---

"@
    
    # Combine front matter with existing content
    $newContent = $frontMatter + $content
    
    # Write back to file
    Set-Content -Path $file.FullName -Value $newContent -NoNewline
    
    Write-Host "  - Added front matter successfully" -ForegroundColor Green
}

Write-Host "`nCompleted processing all model files!" -ForegroundColor Cyan
