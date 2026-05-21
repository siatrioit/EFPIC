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

if ($List) {
    Write-Host "=== Bazes tagi ===" -ForegroundColor Cyan
    & $git @gitConfig tag -l "baseline/*"
    Write-Host ""
    Write-Host "=== Snapshot tagi ===" -ForegroundColor Cyan
    & $git @gitConfig tag -l "snapshot/*"
    Write-Host ""
    Write-Host "=== Pededjie commiti ===" -ForegroundColor Cyan
    & $git @gitConfig log -15 --oneline --decorate
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
    Write-Error "Noradiet -ToTag vai -ToCommit. Skatiet tagus: .\scripts\git-rollback.ps1 -List"
}

$uncommitted = & $git @gitConfig status --porcelain
if (-not [string]::IsNullOrWhiteSpace($uncommitted)) {
    Write-Host "BRIDINAJUMS: ir necommittetas izmainas. Tas tiks NONEMTAS." -ForegroundColor Yellow
    & $git @gitConfig status -sb
    $confirm = Read-Host "Turpinat? (rakstiet YES)"
    if ($confirm -ne "YES") {
        Write-Host "Atcelts."
        exit 1
    }
}

& $git @gitConfig reset --hard $target
Write-Host "Atgriezts uz: $target" -ForegroundColor Green
& $git @gitConfig log -1 --oneline --decorate
