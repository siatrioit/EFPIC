# Izveido Git commit (un pēc izvēles tagu) pirms lielām izmaiņām.
# Lietošana:
#   .\scripts\git-snapshot.ps1 -Message "Pirms Pro reklāmu noņemšanas" -Tag
#   .\scripts\git-snapshot.ps1 -Message "Pabeigts: telemetrijas izņemšana"

param(
    [Parameter(Mandatory = $true)]
    [string]$Message,

    [switch]$Tag,

    [string]$TagName = ""
)

$ErrorActionPreference = "Stop"
$root = Split-Path -Parent $PSScriptRoot
Set-Location $root

if (-not (Test-Path ".git")) {
    Write-Error "Nav Git repozitorija: $root"
}

$status = git status --porcelain
if ([string]::IsNullOrWhiteSpace($status)) {
    Write-Host "Nav izmaiņu — commit nav nepieciešams."
    exit 0
}

git add -A
Write-Host ""
Write-Host "=== Izmaiņas ===" -ForegroundColor Cyan
git status -sb
Write-Host ""

git commit -m $Message
if ($LASTEXITCODE -ne 0) {
    Write-Error "Commit neizdevās."
}

Write-Host "Commit izveidots." -ForegroundColor Green

if ($Tag) {
    if ([string]::IsNullOrWhiteSpace($TagName)) {
        $TagName = "snapshot/$(Get-Date -Format 'yyyy-MM-dd_HHmm')"
    }
    git tag -a $TagName -m $Message
    Write-Host "Tags: $TagName" -ForegroundColor Green
}

Write-Host ""
Write-Host "Pēdējie commiti:" -ForegroundColor Cyan
git log -5 --oneline --decorate
