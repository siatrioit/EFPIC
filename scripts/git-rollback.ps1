# Atgriešanās uz iepriekšēju versiju (commit vai tag).
# BRĪDINĀJUMS: izmaiņas, kas nav commitētas, tiks zaudētas.
#
# Lietošana:
#   .\scripts\git-rollback.ps1 -List
#   .\scripts\git-rollback.ps1 -ToTag "baseline/pre-customization"
#   .\scripts\git-rollback.ps1 -ToCommit "abc1234"

param(
    [switch]$List,

    [string]$ToTag = "",

    [string]$ToCommit = ""
)

$ErrorActionPreference = "Stop"
$root = Split-Path -Parent $PSScriptRoot
Set-Location $root

if (-not (Test-Path ".git")) {
    Write-Error "Nav Git repozitorija: $root"
}

if ($List) {
    Write-Host "=== Bāzes tagi ===" -ForegroundColor Cyan
    git tag -l "baseline/*"
    Write-Host ""
    Write-Host "=== Snapshot tagi ===" -ForegroundColor Cyan
    git tag -l "snapshot/*"
    Write-Host ""
    Write-Host "=== Pēdējie commiti ===" -ForegroundColor Cyan
    git log -15 --oneline --decorate
    exit 0
}

$target = ""
if (-not [string]::IsNullOrWhiteSpace($ToTag)) {
    $target = $ToTag
}
elseif (-not [string]::IsNullOrWhiteSpace($ToCommit)) {
    $target = $ToCommit
}
else {
    Write-Error "Norādiet -ToTag vai -ToCommit. Skatiet tagus: .\scripts\git-rollback.ps1 -List"
}

$uncommitted = git status --porcelain
if (-not [string]::IsNullOrWhiteSpace($uncommitted)) {
    Write-Host "BRĪDINĀJUMS: ir necommitētas izmaiņas. Tās tiks NOŅEMTAS." -ForegroundColor Yellow
    git status -sb
    $confirm = Read-Host "Turpināt? (rakstiet YES)"
    if ($confirm -ne "YES") {
        Write-Host "Atcelts."
        exit 1
    }
}

git reset --hard $target
Write-Host "Atgriezts uz: $target" -ForegroundColor Green
git log -1 --oneline --decorate
