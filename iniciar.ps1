param([int]$Puerto = 8000)
$ErrorActionPreference = 'Stop'
Set-Location -LiteralPath $PSScriptRoot
$env:PHPRC = Join-Path $PSScriptRoot 'tools'
Write-Host "Quick Wash: http://127.0.0.1:$Puerto" -ForegroundColor Magenta
Set-Location -LiteralPath (Join-Path $PSScriptRoot 'public')
php -S "127.0.0.1:$Puerto" -t . ..\vendor\laravel\framework\src\Illuminate\Foundation\resources\server.php
