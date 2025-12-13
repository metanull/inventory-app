& {
	Write-Warning "This will WIPE THE ENTIRE DATABASE!"
	if ( 'I know what I do!' -eq (Read-Host -Prompt 'Type "I know what I do!" to continue...')) {
		$Managers = @('havelangep@hotmail.com')
		$Users = @('havelangep@hotmail.com', 'havelangep@gmail.com', 'evaplaysviolin@gmail.com', 'eva@museumwnf.net', 'management@museumwnf.net')
		$Directory = 'C:\mwnf-server\github-apps\production\inventory-app'

		if ((Resolve-Path $Directory -ErrorAction Continue)) {
			Set-Location $Directory
			php artisan db:wipe --force
			php artisan migrate:refresh --force
			php artisan db:seed --class=MinimalDatabaseSeeder --force

			$Users |% {
				php artisan user:create $_ $_
				php artisan user:email-verification $_ verify
				if ($Managers -contains $_) {
					php artisan user:assign-role $_ 'Manager of Users'
				} else {
					php artisan user:assign-role $_ 'Regular User'
				}
			}
		}
	} else {
		Write-Information 'Cancelled'
	}
}