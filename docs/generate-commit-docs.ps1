#!/usr/bin/env pwsh
<#
.SYNOPSIS
    Git Commit Documentation Generator (PowerShell Version)
    
.DESCRIPTION
    Fetches commits from main branch and generates markdown files
    Author: Generated for Inventory Management UI
    
.EXAMPLE
    .\generate-commit-docs.ps1
#>

param()

$DOCS_DIR = "docs/_docs",
$BRANCH = "main",
$LOG_FILE = "docs/.commit-docs.log"

if(-not (test-path $DOCS_DIR)) {
    New-Item -Path $DOCS_DIR -ItemType Directory | Out-Null
}

# Setup logging function
function Write-Log {
    param(
        [Parameter(Mandatory=$true)]
        [string]$Message,
        [ValidateSet("INFO", "WARNING", "ERROR")]
        [string]$Level = "INFO"
    )
    
    $timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    $logEntry = "$timestamp - $Level - $Message"
    
    # Write to console
    switch ($Level) {
        "INFO" { Write-Host $logEntry -ForegroundColor Green }
        "WARNING" { Write-Host $logEntry -ForegroundColor Yellow }
        "ERROR" { Write-Host $logEntry -ForegroundColor Red }
    }
    
    # Write to log file
    if(-not (Test-Path $LOG_FILE)) {
        New-Item -Path $LOG_FILE -ItemType File -Force | Out-Null
    }
    Add-Content -Path $LOG_FILE -Value $logEntry -Encoding UTF8
}

class CommitDocGenerator {
    [string]$DocsDir
    [string]$RepoRoot
    
    CommitDocGenerator() {
        $this.DocsDir = $script:DOCS_DIR
        $this.RepoRoot = "."
    }
    
    [string] SanitizeFilename([string]$Text) {
        # Sanitize text for use in filenames
        $sanitized = $Text -replace '[^a-zA-Z0-9._-]', '_'
        $sanitized = $sanitized -replace '_+', '_'
        return $sanitized.Trim('_')
    }
    
    [string] RunGitCommand([string]$Command) {
        # Run a git command and return the output
        try {
            $arguments = $Command -split ' ', 2
            $gitCommand = $arguments[0]
            $gitArgs = if ($arguments.Length -gt 1) { $arguments[1] } else { "" }
            
            $processInfo = New-Object System.Diagnostics.ProcessStartInfo
            $processInfo.FileName = "git"
            $processInfo.Arguments = $gitArgs
            $processInfo.WorkingDirectory = $this.RepoRoot
            $processInfo.UseShellExecute = $false
            $processInfo.RedirectStandardOutput = $true
            $processInfo.RedirectStandardError = $true
            $processInfo.CreateNoWindow = $true
            
            $process = New-Object System.Diagnostics.Process
            $process.StartInfo = $processInfo
            $process.Start() | Out-Null
            
            $stdout = $process.StandardOutput.ReadToEnd()
            $stderr = $process.StandardError.ReadToEnd()
            $process.WaitForExit()
            
            if ($process.ExitCode -ne 0) {
                Write-Log "Git command failed: $Command" -Level "ERROR"
                Write-Log "Error: $stderr" -Level "ERROR"
                return $null
            }
            
            return $stdout.Trim()
        }
        catch {
            Write-Log "Git command failed: $Command" -Level "ERROR"
            Write-Log "Error: $($_.Exception.Message)" -Level "ERROR"
            return $null
        }
    }
    
    [void] ValidateRepository() {
        # Validate that we're in a Git repository
        if (-not (Test-Path ".git")) {
            Write-Log "Not a Git repository. Please run from repository root." -Level "ERROR"
            exit 1
        }
        
        # Check if main branch exists
        $result = $this.RunGitCommand("git show-ref --verify --quiet refs/heads/main")
        if ($null -eq $result) {
            Write-Log "Main branch not found locally. Fetching from remote..." -Level "WARNING"
            $fetchResult = $this.RunGitCommand("git fetch origin main:main")
            if ($null -eq $fetchResult) {
                Write-Log "Failed to fetch main branch" -Level "ERROR"
                exit 1
            }
        }
    }
    
    [string] GetLatestDocumentedCommit() {
        # Get the latest commit hash from existing documentation
        if (-not (Test-Path $this.DocsDir)) {
            return $null
        }
        
        $mdFiles = Get-ChildItem -Path $this.DocsDir -Filter "*.md" -File | Where-Object { $_.Name -ne "index.md" }
        if ($mdFiles.Count -eq 0) {
            return $null
        }
        
        # Find the most recent file
        $latestFile = $mdFiles | Sort-Object LastWriteTime -Descending | Select-Object -First 1
        
        try {
            $content = Get-Content -Path $latestFile.FullName -Raw -Encoding UTF8
            if ($content -match '(?m)^commit_hash:\s*([a-f0-9]+)') {
                return $matches[1].Trim('"')
            }
        }
        catch {
            Write-Log "Could not read latest commit from $($latestFile.Name): $($_.Exception.Message)" -Level "WARNING"
        }
        
        return $null
    }
    
    [System.Collections.ArrayList] GetCommits([string]$SinceCommit) {
        # Get list of commits to process
        $commits = New-Object System.Collections.ArrayList
        
        if ($SinceCommit) {
            Write-Log "Fetching commits since $SinceCommit" -Level "INFO"
            $command = "git log main --pretty=format:%H|%ci|%s|%an --reverse $SinceCommit..HEAD"
        } else {
            Write-Log "Fetching all commits..." -Level "INFO"
            $command = "git log main --pretty=format:%H|%ci|%s|%an --reverse"
        }
        
        $output = $this.RunGitCommand($command)
        if (-not $output) {
            return $commits
        }
        
        foreach ($line in $output -split "`n") {
            if ($line.Trim()) {
                $parts = $line -split '\|', 4
                if ($parts.Length -eq 4) {
                    $commit = @{
                        'hash' = $parts[0]
                        'date' = $parts[1]
                        'title' = $parts[2]
                        'author' = $parts[3]
                    }
                    $commits.Add($commit) | Out-Null
                }
            }
        }
        
        return $commits
    }
    
    [string] GetCommitMessage([string]$CommitHash) {
        # Get full commit message
        $command = "git log -1 --pretty=format:%B $CommitHash"
        $result = $this.RunGitCommand($command)
        if ($result) { 
            return $result 
        } else { 
            return "" 
        }
    }
    
    [string] GetFilesChanged([string]$CommitHash) {
        # Get list of files changed in a commit
        $command = "git show --name-status --pretty=format: $CommitHash"
        $output = $this.RunGitCommand($command)
        
        if (-not $output) {
            return "No files changed in this commit."
        }
        
        $filesMd = @()
        foreach ($line in $output -split "`n") {
            $line = $line.Trim()
            if (-not $line) { continue }
            
            $parts = $line -split "`t", 2
            if ($parts.Length -ne 2) { continue }
            
            $status = $parts[0]
            $filename = $parts[1]
            
            switch -Regex ($status) {
                '^A$' { $filesMd += "- ✅ **Added:** ``$filename``" }
                '^M$' { $filesMd += "- 📝 **Modified:** ``$filename``" }
                '^D$' { $filesMd += "- ❌ **Deleted:** ``$filename``" }
                '^R' { $filesMd += "- 🔄 **Renamed:** ``$filename``" }
                '^C' { $filesMd += "- 📋 **Copied:** ``$filename``" }
                default { $filesMd += "- 📄 **Changed:** ``$filename``" }
            }
        }
        
        if ($filesMd.Count -gt 0) { 
            return $filesMd -join "`n" 
        } else { 
            return "No files changed in this commit." 
        }
    }
    
    [hashtable] GenerateMarkdown([hashtable]$Commit) {
        # Generate markdown content for a commit
        $commitMessage = $this.GetCommitMessage($Commit.hash)
        $filesChanged = $this.GetFilesChanged($Commit.hash)
        
        # Format date for filename
        try {
            $dateObj = [DateTime]::Parse($Commit.date)
            $fileDate = $dateObj.ToString("yyyyMMddHHmmss")
        }
        catch {
            $fileDate = ($Commit.date -replace '[^0-9]', '').Substring(0, [Math]::Min(14, ($Commit.date -replace '[^0-9]', '').Length))
        }
        
        # Create filename
        $safeTitle = $this.SanitizeFilename($Commit.title)
        $shortHash = $Commit.hash.Substring(0, 7)
        $filename = "$fileDate-$shortHash-$safeTitle.md"
        
        # Generate content
        $content = @"
---
layout: default
title: "$($Commit.title)"
date: $($Commit.date)
author: $($Commit.author)
commit_hash: $($Commit.hash)
nav_exclude: true
---

# $($Commit.title)

**Commit:** ``$($Commit.hash)``  
**Date:** $($Commit.date)  
**Author:** $($Commit.author)  

## Commit Message

``````
$commitMessage
``````

## Files Changed

$filesChanged

## Links

- [View commit on GitHub](https://github.com/metanull/inventory-management-ui/commit/$($Commit.hash))
- [Browse repository at this commit](https://github.com/metanull/inventory-management-ui/tree/$($Commit.hash))

---

*This documentation was automatically generated from Git commit data.*
"@
        
        return @{
            'filename' = $filename
            'content' = $content
        }
    }
    
    [void] GenerateIndex() {
        # Generate index file for the documentation
        $indexContent = @"
---
layout: default
title: Commit Documentation
nav_exclude: true
---

# Commit Documentation

This directory contains automatically generated documentation for each commit to the main branch.

## Recent Commits

"@
        
        # Get recent markdown files (excluding index.md)
        $mdFiles = Get-ChildItem -Path $this.DocsDir -Filter "*.md" -File | Where-Object { $_.Name -ne "index.md" }
        $mdFiles = $mdFiles | Sort-Object LastWriteTime -Descending | Select-Object -First 20
        
        foreach ($mdFile in $mdFiles) {
            try {
                $content = Get-Content -Path $mdFile.FullName -Raw -Encoding UTF8
                
                if ($content -match '(?m)^title:\s*"([^"]+)"') {
                    $title = $matches[1]
                } else { continue }
                
                if ($content -match '(?m)^date:\s*(.+)') {
                    $date = $matches[1] -split ' ' | Select-Object -First 1
                } else { continue }
                
                $basename = $mdFile.BaseName
                $indexContent += "- [$title]($basename/) - $date`n"
            }
            catch {
                Write-Log "Could not process $($mdFile.Name): $($_.Exception.Message)" -Level "WARNING"
            }
        }
        
        $indexContent += "`n*Documentation automatically generated on $(Get-Date -Format 'yyyy-MM-ddTHH:mm:ss')*`n"
        
        $indexPath = Join-Path $this.DocsDir "index.md"
        Set-Content -Path $indexPath -Value $indexContent -Encoding UTF8
    }
    
    [void] Run() {
        # Main execution function
        Write-Log "Starting Git commit documentation generator..." -Level "INFO"
        
        # Validate repository
        $this.ValidateRepository()
        
        # Create docs directory
        if (-not (Test-Path $this.DocsDir)) {
            New-Item -Path $this.DocsDir -ItemType Directory | Out-Null
        }
        
        # Get latest documented commit
        $latestCommit = $this.GetLatestDocumentedCommit()
        if ($latestCommit) {
            Write-Log "Latest documented commit: $latestCommit" -Level "INFO"
        } else {
            Write-Log "No existing documentation found. Processing all commits." -Level "INFO"
        }
        
        # Fetch latest changes
        Write-Log "Fetching latest changes from remote..." -Level "INFO"
        $fetchResult = $this.RunGitCommand("git fetch origin")
        if ($null -eq $fetchResult) {
            Write-Log "Failed to fetch from remote (continuing with local data)" -Level "WARNING"
        }
        
        # Get commits to process
        $commits = $this.GetCommits($latestCommit)
        
        if ($commits.Count -eq 0) {
            Write-Log "No new commits to document." -Level "INFO"
            return
        }
        
        # Process commits
        Write-Log "Processing $($commits.Count) commits..." -Level "INFO"
        for ($i = 0; $i -lt $commits.Count; $i++) {
            $commit = $commits[$i]
            $commitNum = $i + 1
            Write-Log "Processing commit $commitNum/$($commits.Count): $($commit.hash)" -Level "INFO"
            
            $result = $this.GenerateMarkdown($commit)
            $filepath = Join-Path $this.DocsDir $result.filename
            
            Set-Content -Path $filepath -Value $result.content -Encoding UTF8
            
            Write-Log "Generated: $($result.filename)" -Level "INFO"
        }
        
        # Generate index
        Write-Log "Generating documentation index..." -Level "INFO"
        $this.GenerateIndex()
        
        Write-Log "Documentation generation completed!" -Level "INFO"
        Write-Log "Generated files are in: $($this.DocsDir)" -Level "INFO"
    }
}

# Main execution
try {
    $generator = [CommitDocGenerator]::new()
    $generator.Run()
}
catch {
    Write-Log "Script execution failed: $($_.Exception.Message)" -Level "ERROR"
    exit 1
}
