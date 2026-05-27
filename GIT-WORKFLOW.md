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

## Attālinātais repozitorijs

- **GitHub:** https://github.com/siatrioit/EFPIC
- **Clone:** `git clone https://github.com/siatrioit/EFPIC.git`

## Deploy uz serveri (cPanel)

Repozitoriju **neklonē** tieši uz `wp-content/plugins`. Klonē atsevišķi, piem. `~/repositories/EFPIC`, tad deploy:

- Automātiski: fails `.cpanel.yml` (cPanel → Git Version Control → Update/Deploy)
- Manuāli SSH: `bash scripts/deploy-to-plugins.sh`

Mērķa ceļš serverī:

`/home2/trioitlv/edgarsfoto.lv/wp-content/plugins/efpic`  
`/home2/trioitlv/edgarsfoto.lv/wp-content/plugins/efpic-pro`
