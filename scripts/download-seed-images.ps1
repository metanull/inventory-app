<#

.SYNOPSIS
   Download sample images for database seeding

.DESCRIPTION
    This script downloads a curated set of images from picsum.photos for local seeding:

    Image source: https://picsum.photos/images#2
#>
[CmdletBinding()]
[Diagnostics.CodeAnalysis.SuppressMessage("PSAvoidUsingWriteHost", "", Justification = "Write-Host is used for direct output to console, which is appropriate here.")]
param(
    [string]$outputDir = "database\seeders\data\images"
)
End {
    $images = @(
        @{ id = 30; name = "shyamanta_baruah" },
        @{ id = 31; name = "how_soon_ngu" },
        @{ id = 32; name = "rodrigo_melo" },
        @{ id = 33; name = "alejandro_escamilla" },
        @{ id = 34; name = "aleks_dorohovich" },
        @{ id = 35; name = "shane_colella" },
        @{ id = 36; name = "vadim_sherbakov" },
        @{ id = 37; name = "austin_neill" },
        @{ id = 38; name = "allyson_souza" },
        @{ id = 39; name = "luke_chesser" },
        @{ id = 40; name = "ryan_mcguire" },
        @{ id = 41; name = "nithya_ramanujam" },
        @{ id = 42; name = "luke_chesser_2" },
        @{ id = 43; name = "oleg_chursin" },
        @{ id = 44; name = "christopher_sardegna" },
        @{ id = 45; name = "alan_haverty" },
        @{ id = 46; name = "jeffrey_kam" },
        @{ id = 47; name = "christopher_sardegna_2" },
        @{ id = 48; name = "luke_chesser_3" },
        @{ id = 49; name = "margaret_barley" },
        @{ id = 50; name = "tyler_wanlass" },
        @{ id = 51; name = "ireneuilia" },
        @{ id = 52; name = "cierra" },
        @{ id = 53; name = "j_duclos" },
        @{ id = 54; name = "nicholas_swanson" },
        @{ id = 55; name = "tyler_wanlass_2" },
        @{ id = 56; name = "sebastian_muller" },
        @{ id = 57; name = "nicholas_swanson_2" },
        @{ id = 58; name = "tony_naccarato" },
        @{ id = 59; name = "art_wave" }
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
            # Randomly add effects
            $params = @()
            if ((Get-Random -Minimum 1 -Maximum 5) -eq 1) { $params += "grayscale" }
            if ((Get-Random -Minimum 1 -Maximum 10) -eq 1) { $params += "blur" }

            $queryString = ""
            if ($params.Count -gt 0) {
                $queryString = "?" + ($params -join "&")
            }

            $url = "https://picsum.photos/id/$($image.id)/640/480$queryString"
            $filename = "$($image.name).jpg"
            $filepath = Join-Path $outputDir $filename

            Write-Information "Downloading $filename from $url..."

            Invoke-WebRequest -Uri $url -OutFile $filepath -ErrorAction Stop
            $downloaded++
            $downloadItem = Get-Item $filepath
            Write-Host "✓" -ForegroundColor Green -NoNewline
            Write-Host " Downloaded $filename ($($downloadItem.Length / 1KB) Kb)"

            # Be nice to the server
            Start-Sleep -Milliseconds 500

        }
        catch {
            $failed++
            Write-Host "✗" -ForegroundColor Red -NoNewline
            Write-Host " Failed to download $($image.name): $($_.Exception.Message)"
        }
    }

    if ( $failed -ne 0 ) {
        Write-Warning "$failed images failed to download."
    }
    if ( $downloaded -ne 0) {
        Write-Information "Downloaded: $downloaded images."
    }
}