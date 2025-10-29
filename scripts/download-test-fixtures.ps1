<#
.SYNOPSIS
   Download sample images for test fixtures

.DESCRIPTION
    This script downloads a small set of test images for use in unit/integration tests.
    Images are stored in tests/Fixtures/images/ and committed to git.
#>
[CmdletBinding()]
[Diagnostics.CodeAnalysis.SuppressMessage("PSAvoidUsingWriteHost", "", Justification = "Write-Host is used for direct output to console, which is appropriate here.")]
param(
    [string]$outputDir = "tests\Fixtures\images"
)
End {
    # Small set of test images (different sizes for different test scenarios)
    $images = @(
        @{ id = 30; name = "test-image-small"; width = 100; height = 100 },
        @{ id = 31; name = "test-image-medium"; width = 640; height = 480 },
        @{ id = 32; name = "test-image-large"; width = 1024; height = 768 },
        @{ id = 33; name = "test-image-portrait"; width = 480; height = 640 }
    )

    # Ensure output directory exists
    if (!(Test-Path $outputDir)) {
        New-Item -ItemType Directory -Path $outputDir -Force
        Write-Information "Created directory: $outputDir"
    }

    $downloaded = 0
    $failed = 0

    foreach ($image in $images) {
        try {
            $url = "https://picsum.photos/id/$($image.id)/$($image.width)/$($image.height)"
            $filename = "$($image.name).jpg"
            $filepath = Join-Path $outputDir $filename

            # Skip if already exists
            if (Test-Path $filepath) {
                Write-Host "⊘" -ForegroundColor Yellow -NoNewline
                Write-Host " Skipped $filename (already exists)"
                continue
            }

            Write-Information "Downloading $filename from $url..."

            Invoke-WebRequest -Uri $url -OutFile $filepath -ErrorAction Stop
            $downloaded++
            $downloadItem = Get-Item $filepath
            Write-Host "✓" -ForegroundColor Green -NoNewline
            Write-Host " Downloaded $filename ($([math]::Round($downloadItem.Length / 1KB, 1)) KB)"

            # Be nice to the server
            Start-Sleep -Milliseconds 500

        }
        catch {
            $failed++
            Write-Host "✗" -ForegroundColor Red -NoNewline
            Write-Host " Failed to download $($image.name): $($_.Exception.Message)"
        }
    }

    if ($failed -ne 0) {
        Write-Warning "$failed images failed to download."
    }
    if ($downloaded -ne 0) {
        Write-Information "Downloaded: $downloaded images to $outputDir"
    }
    else {
        Write-Information "All images already downloaded."
    }
}
