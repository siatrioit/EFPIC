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

function Get-GitExe {
    $candidates = @(
        "git",
        "${env:ProgramFiles}\Git\cmd\git.exe",
        "${env:ProgramFiles(x86)}\Git\cmd\git.exe"
    )
    foreach ($c in $candidates) {
        if (Get-Command $c -ErrorAction SilentlyContinue) { return $c }
        if (Test-Path $c) { return $c }
    }
    Write-Error "Git nav atrasts. Parstartejiet terminali pec instalacijas."
}

$git = Get-GitExe
$gitConfig = @("-c", "user.name=Edgars", "-c", "user.email=dev@edgarsfoto.lv")

if (-not (Test-Path ".git")) {
    Write-Error "Nav Git repozitorija: $root"
}

$status = & $git @gitConfig status --porcelain
if ([string]::IsNullOrWhiteSpace($status)) {
    Write-Host "Nav izmainu - commit nav nepieciesams."
    exit 0
}

& $git @gitConfig add -A
Write-Host ""
Write-Host "=== Izmaiņas ===" -ForegroundColor Cyan
& $git @gitConfig status -sb
Write-Host ""

& $git @gitConfig commit -m $Message
if ($LASTEXITCODE -ne 0) {
    Write-Error "Commit neizdevas."
}

Write-Host "Commit izveidots." -ForegroundColor Green

if ($Tag) {
    if ([string]::IsNullOrWhiteSpace($TagName)) {
        $TagName = "snapshot/$(Get-Date -Format 'yyyy-MM-dd_HHmm')"
    }
    & $git @gitConfig tag -a $TagName -m $Message
    Write-Host "Tags: $TagName" -ForegroundColor Green
}

Write-Host ""
Write-Host "Pedejie commiti:" -ForegroundColor Cyan
& $git @gitConfig log -5 --oneline --decorate
