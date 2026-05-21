# efpic — versiju kontrole un atgriešanās

Šis repozitorijs satur **abus** spraudņus:

- `efpic/` — pamata spraudnis
- `efpic-pro/` — Pro paplašinājums

## Sākuma punkts (baseline)

Pirms lielām izmaiņām ir tags:

- `baseline/pre-customization` — stāvoklis pirms tīrīšanas un pielāgojumiem

## Pirms katra liela soļa

PowerShell no mapes `g:\Mans disks\EFPIC`:

```powershell
.\scripts\git-snapshot.ps1 -Message "Pirms: Pro reklāmu noņemšana" -Tag
```

Tas izveido commit un tagu `snapshot/YYYY-MM-DD_HHMM`.

## Atgriešanās, ja kaut kas salūzt

Skatīt pieejamās versijas:

```powershell
.\scripts\git-rollback.ps1 -List
```

Atgriezties uz sākumu:

```powershell
.\scripts\git-rollback.ps1 -ToTag "baseline/pre-customization"
```

Atgriezties uz konkrētu snapshot:

```powershell
.\scripts\git-rollback.ps1 -ToTag "snapshot/2026-05-21_1430"
```

## Cursor aģentam

Aģents pirms riskantām izmaiņām izveido snapshot ar `-Tag`. Pēc katra pabeigta uzdevuma — commit ar skaidru ziņojumu latviski vai angliski.

## Attālināta dublēšana (ieteicams)

Kad būs gatavs, pievienojiet GitHub/GitLab repozitoriju un:

```powershell
git remote add origin <jūsu-repo-url>
git push -u origin main --tags
```

Tad dublējums ir arī ārpus šī diska.
