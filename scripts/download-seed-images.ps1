# Download sample images for database seeding
# This script downloads a curated set of images from picsum.photos for local seeding:
#   Image source: https://picsum.photos/images#2

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

$outputDir = "database\seeders\data\images"

# Ensure output directory exists
if (!(Test-Path $outputDir)) {
    New-Item -ItemType Directory -Path $outputDir -Force
    Write-Host "Created directory: $outputDir"
}

$downloaded = 0
$failed = 0

foreach ($image in $images) {
    try {
        # Randomly add effects
        $params = @()
        if ((Get-Random -Minimum 0 -Maximum 2) -eq 1) { $params += "grayscale" }
        if ((Get-Random -Minimum 0 -Maximum 4) -eq 1) { $params += "blur" }
        
        $queryString = ""
        if ($params.Count -gt 0) {
            $queryString = "?" + ($params -join "&")
        }
        
        $url = "https://picsum.photos/id/$($image.id)/640/480$queryString"
        $filename = "$($image.name).jpg"
        $filepath = Join-Path $outputDir $filename
        
        Write-Host "Downloading $filename from $url..."
        
        Invoke-WebRequest -Uri $url -OutFile $filepath -ErrorAction Stop
        $downloaded++
        Write-Host "✓ Downloaded $filename" -ForegroundColor Green
        
        # Be nice to the server
        Start-Sleep -Milliseconds 500
        
    } catch {
        $failed++
        Write-Host "✗ Failed to download $($image.name): $($_.Exception.Message)" -ForegroundColor Red
    }
}

Write-Host "`nDownload complete!"
Write-Host "Downloaded: $downloaded images" -ForegroundColor Green
Write-Host "Failed: $failed images" -ForegroundColor Red

# List downloaded files
$files = Get-ChildItem $outputDir -Filter "*.jpg"
Write-Host "`nDownloaded files:"
$files | ForEach-Object { 
    $size = [math]::Round($_.Length / 1KB, 2)
    Write-Host "  $($_.Name) ($size KB)"
}
