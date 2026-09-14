$ErrorActionPreference = 'Stop'
Set-Location -LiteralPath $PSScriptRoot
$env:PHPRC = Join-Path $PSScriptRoot 'tools'
php artisan test
exit $LASTEXITCODE
