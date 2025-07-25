[CmdletBinding()]
param()
Process {
    # Create a header for the issues file
    @"
# Issues Report
Generated on: $(Get-Date -Format "yyyy-MM-dd HH:mm:ss")

"@

    # Run format check (prettier)
    Write-Information "Running format check..."
    @"
## Format Issues

"@

    npx prettier --log-level warn --no-color --check resources/js/ 2>&1

    # Run linting (eslint)
    Write-Information "Running lint check..."
    @"

## Lint Issues

"@
    
    npx eslint --quiet --no-fix --format stylish 2>&1

    # Run type-check (vue-tsc)
    Write-Information "Running type check..."
    @"

## TypeScript Issues

"@
    
    npx vue-tsc --noEmit --pretty false 2>&1

    # Run build (vite build)
    Write-Information "Running build check..."
    @"

## Build Issues

"@
    
    npm run build 2>&1

    @"
"@

    Write-Information "All issues have been written to $tempFile"
}
