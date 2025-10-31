@{
    RootModule           = 'InventoryApp.Deployment.psm1'
    ModuleVersion        = '1.0.0'
    GUID                 = '1e29bb68-3d41-40e4-9289-b2ef2fbd678b'
    Author               = 'Museum With No Frontiers'
    CompanyName          = 'Museum With No Frontiers'
    Copyright            = '(c) 2025 Museum With No Frontiers. All rights reserved.'
    Description          = 'PowerShell module for deploying Inventory Management API on Windows with persistent storage, atomic symlink swapping, and comprehensive error handling.'
    PowerShellVersion    = '5.0'
    RequiredModules      = @()
    FunctionsToExport    = @(
        'Test-SystemPrerequisites'
        'Test-DeploymentPackage'
        'New-StagingDirectory'
        'Remove-OldStagingDirectories'
        'New-StorageSymlink'
        'Swap-WebserverSymlink'
        'Remove-SwapBackup'
        'New-EnvironmentFile'
        'Invoke-LaravelSetup'
        'Invoke-LaravelDown'
        'Deploy-Application'
    )
    CmdletsToExport      = @()
    VariablesToExport    = @()
    AliasesToExport      = @()
    PrivateData          = @{
        PSData = @{
            Tags       = @('Deployment', 'Laravel', 'Windows', 'PowerShell')
            ProjectUri = 'https://github.com/metanull/inventory-app'
            LicenseUri = 'https://github.com/metanull/inventory-app/blob/main/LICENSE'
        }
    }
    HelpInfoUri          = 'https://github.com/metanull/inventory-app/docs'
}
