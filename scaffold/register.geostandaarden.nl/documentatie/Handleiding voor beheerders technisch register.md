# Handleiding voor beheerders van het technisch register

Deze handleiding beschrijft het beheer van https://register.geostandaarden.nl via de
GitHub repository `Geonovum/register.geostandaarden.nl`. Zij vervangt de oude
handleiding uit [technisch-register-2019](https://github.com/Geonovum/technisch-register-2019),
die nog het webhook/servermechanisme beschreef.

Het belangrijkste verschil met vroeger: **deze git repository ís de bron van wat
gepubliceerd staat.** Alles wat op de website verandert — artefacten, configuratie,
teksten — verandert via een commit of pull request in deze repository. Na een merge
naar `main` zet een GitHub Action de inhoud automatisch met rsync op de webserver.
Inloggen op de server is voor publicatie dus **niet** meer nodig.

| Branch | Omgeving |
|---|---|
| `main` | https://register.geostandaarden.nl/ (productie) |
| `develop` | https://test.register.geostandaarden.nl/ (test) |

## 1. Hoe publicatie werkt

Bronrepositories (de informatiemodel-repositories in
[`config/repos.json`](../config/repos.json)) publiceren door in GitHub een **release**
aan te maken:

* Een **pre-release** wordt automatisch op de `develop` branch gezet en verschijnt
  binnen enkele minuten op de testomgeving. Hier komt geen beoordeling aan te pas.
* Een **release** leidt automatisch tot een **pull request naar `main`** met als titel
  *"Automated update from \<organisatie/repository\> \<tag\> to main"*. Pas nadat een
  beheerder deze PR heeft gemerged staat de nieuwe versie op productie.

Daarnaast draait dagelijks (05:30 UTC) de workflow *Sync releases from source
repositories*. Die haalt van elke repository in `config/repos.json` de laatste release
op en opent een pull request (*"Automated sync of \<model-id\> (\<tag\>)"*) zodra de
inhoud van het register afwijkt. Dit is het vangnet voor bronrepositories die de
publicatie-workflow niet zelf kunnen draaien, en de manier om alles opnieuw te
synchroniseren.

Bij beide routes geldt: alleen repositories die in `config/repos.json` staan worden
geaccepteerd, en alleen mappen waarvan de naam een artefacttype uit
[`config/descriptions.json`](../config/descriptions.json) is worden overgenomen. De
inhoud komt terecht in `/{artefacttype}/{model-id}/` en **vervangt** daar de vorige
inhoud van dat model volledig.

## 2. Automatische pull requests beoordelen

Als beheerder beoordeel je de automatische PR's. Controleer:

1. **Model-id en release**: kloppen het model-id en de release/tag in de PR-beschrijving
   met wat je verwacht? Is dit een aangekondigde publicatie?
2. **Bestanden**: klik op *Files changed*. De wijzigingen mogen alleen mappen
   `/{artefacttype}/{model-id}/` van dát model raken. Wijzigingen aan `config/`,
   `index.php` of andere modellen horen niet in een automatische publicatie-PR.
3. **CI**: de check *Validate changed artefacts* moet groen zijn (controleert onder
   andere of gewijzigde XML-bestanden well-formed zijn).

Klik op **Merge pull request** om te publiceren — **let op: mergen naar `main` zet de
wijziging direct op productie.** Ben je het niet eens met de wijziging, sluit de PR dan
met *Close pull request*, eventueel met een toelichting voor de aanbieder.

## 3. Een nieuw informatiemodel toevoegen

Alle stappen gebeuren in deze repository (via de GitHub-webinterface of een lokale
clone); er zijn geen serverhandelingen nodig. De beheerder van het informatiemodel kan
de configuratiewijziging ook zelf als pull request aanleveren — beoordeel die dan zoals
hierboven.

### 3.1 `config/repos.json`

Voeg een blok toe zoals:

```json
{
  "id": "nen3610",
  "cluster": "",
  "titel": "NEN3610",
  "titel_kort": "NEN3610",
  "beschrijving": "Het Basismodel Geo-informatie (NEN3610) bevat de gemeenschappelijke basis ...",
  "beschrijving_kort": "Basismodel Geo-informatie",
  "url": "https://github.com/Geonovum/NEN3610"
}
```

Betekenis van de velden en waar ze op de website verschijnen:

| Veld | Gebruik |
|---|---|
| `id` | Bepaalt de URL's: de modelpagina (`/{id}` of `/{cluster}/{id}`) en de artefactmappen (`/xmlschema/{id}/` enz.). Kleine letters en cijfers, geen spaties. Verandert dit later, dan veranderen de URL's mee — vermijd dat. |
| `cluster` | Het `id` van het cluster waar het model onder valt (bijv. `"brt"`), of `""` als het model niet in een cluster zit. |
| `titel` | De paginatitel (kop) van de modelpagina. |
| `titel_kort` | De linktekst naar het model in het kruimelpad en in de modellenlijst op een clusterpagina. |
| `beschrijving` | De lange beschrijving op de modelpagina. Mag HTML bevatten (bijv. een link). |
| `beschrijving_kort` | De korte beschrijving onder de link op een clusterpagina. Richtlijn: maximaal ±58 tekens (wordt niet technisch afgedwongen, maar langere teksten ontsieren de lijst). |
| `url` | De URL van de GitHub repository van het model. Dit is tevens de toegangscontrole: alleen releases van deze URL worden geaccepteerd. |

### 3.2 `config/cluster.json`

De hoofdpagina van het register toont uitsluitend wat in `cluster.json` staat. Er zijn
drie situaties:

* **Het model valt onder een bestaand cluster** (bijv. een nieuw BRT-product):
  `cluster.json` hoeft niet gewijzigd te worden.
* **Het model moet zelfstandig op de hoofdpagina** (zoals NEN3610 of IMGeo): voeg aan
  `cluster.json` een blok toe met **hetzelfde `id` als in `repos.json`**. Zonder dit
  blok is het model wel bereikbaar via zijn URL, maar niet vindbaar vanaf de
  hoofdpagina.
* **Er komt een nieuw cluster van meerdere modellen** (zoals `brt` of `ro`): voeg een
  blok toe met een eigen `id`, en gebruik dat `id` als `cluster`-waarde bij de
  betreffende modellen in `repos.json`.

Velden van `cluster.json` en waar ze verschijnen:

| Veld | Gebruik |
|---|---|
| `id` | De URL van de clusterpagina: `/{id}`. |
| `titel` | De paginatitel (kop) van de clusterpagina. |
| `titel_kort` | De linktekst op de hoofdpagina en in het kruimelpad. |
| `beschrijving` | De lange beschrijving op de clusterpagina. Mag HTML bevatten. |
| `beschrijving_kort` | De korte beschrijving onder de link op de hoofdpagina (richtlijn: max ±58 tekens). |

Na het mergen van de configuratie-PR naar `main` staan de nieuwe pagina's direct op de
website; artefacten verschijnen zodra het model voor het eerst publiceert.

### 3.3 Publicatie vanuit de bronrepository inrichten

Kies één van de twee routes:

* **Workflow in de bronrepository** (aanbevolen, direct na elke release): plaats
  [`examples/source-repo-publish.yml`](../examples/source-repo-publish.yml) als
  `.github/workflows/publish-to-register.yml` in de bronrepository en zorg dat de
  secrets `GH_APP_ID` en `GH_APP_PRIVATE_KEY` er beschikbaar zijn. Voor repositories
  binnen de Geonovum-organisatie zijn dit organisatiesecrets; repositories van andere
  organisaties (bijv. Kadaster) moeten ze als repository secret toevoegen.
* **Niets doen in de bronrepository**: de dagelijkse sync-workflow pikt de laatste
  release vanzelf op (uiterlijk de volgende ochtend). Wil je niet wachten, draai de
  sync dan handmatig (zie §6).

De eisen aan de bronrepository zelf (mapnamen per artefacttype, releases maken) staan
in de *Handleiding voor beheerders informatiemodellen*. De mapnamen moeten exact
overeenkomen met de sleutels van `config/descriptions.json`: `zipfile`,
`informatiemodel`, `gmlapplicatieschema`, `xmlschema`, `regels`, `waardelijst`,
`wsdl`, `visualisatie`, `symbool`.

## 4. Een informatiemodel verwijderen

Maak een pull request die (1) het blok uit `config/repos.json` verwijdert, (2) het
eventuele blok met hetzelfde `id` uit `config/cluster.json` verwijdert, en (3) de
mappen `/{artefacttype}/{model-id}/` verwijdert. Na de merge verdwijnt het model van
de website; de deploy verwijdert de bestanden ook op de server.

## 5. Teksten van de website aanpassen

* **Beschrijvingen van artefacttypen** (rechterkolom hoofdpagina, kolommen op cluster-
  en modelpagina's): `config/descriptions.json`, velden `titel` en `beschrijving`
  (HTML toegestaan). Op de modelpagina worden alleen artefacttypen getoond waarvoor
  het model daadwerkelijk bestanden heeft; het type `zipfile` wordt op hoofd- en
  clusterpagina's niet als ingang getoond.
* **Introductietekst van de hoofdpagina**: rechtstreeks in `index.php`.
* **Menu (Home | Help | Over deze website)**: `resources/html/header.html`; de pagina's
  zelf zijn `resources/html/help.php` en `resources/html/over.php`.

Ook dit zijn gewone pull requests: eerst naar `develop` mergen om op de testomgeving
te kijken kan altijd, mergen naar `main` publiceert naar productie.

## 6. Handmatig synchroniseren en opnieuw deployen

* **Eén model of alles opnieuw ophalen**: ga in GitHub naar *Actions* → *Sync releases
  from source repositories* → *Run workflow*. Vul bij `repo_id` een model-id in om één
  model te synchroniseren, of laat het veld leeg voor alle modellen. Voor elk model
  waarvan de laatste release afwijkt van het register wordt een PR geopend; modellen
  zonder release worden overgeslagen.
* **De website opnieuw deployen zonder inhoudelijke wijziging** (bijv. na
  serveronderhoud): *Actions* → *Validate and deploy* → *Run workflow*, met `main`
  (productie) of `develop` (test) als branch.

## 7. Testomgeving

De testomgeving https://test.register.geostandaarden.nl/ toont altijd de `develop`
branch. Er zijn drie manieren om daar iets op te zetten:

1. de beheerder van een informatiemodel maakt een **pre-release** (gaat er automatisch
   heen);
2. je merget een pull request eerst naar `develop`;
3. je pusht zelf een wijziging naar `develop`.

`develop` af en toe gelijktrekken met `main` voorkomt dat de testomgeving achterloopt.

Lokaal testen van de PHP-pagina's kan met een clone van de repository en
`php -S localhost:8080` in de hoofdmap (bekijk `http://localhost:8080/index.php`,
eventueel met `?url=brt` e.d.). Let op: de ingebouwde PHP-server toont geen
directory listings van de artefactmappen en past geen `.htaccess` toe; voor een
volledige test dient de testomgeving.

## 8. Server en secrets

Voor publicatie en beheer is geen servertoegang nodig. SSH-toegang is alleen nodig
voor de Apache/PHP-configuratie zelf. De webserver vereist Apache met `mod_rewrite`
en PHP, met voor de DocumentRoot `AllowOverride FileInfo Indexes Options` (of `All`)
en `Options +Indexes` (voor de directory listings van de artefactmappen). Alle verdere
inrichting (`.htaccess`, `index.php`, configuratie) komt mee met de deploy.

In de repository (Settings → Secrets and variables → Actions) staan:

| Secret | Gebruikt door | Doel |
|---|---|---|
| `SSH_DEPLOY_KEY`, `SSH_HOST`, `SSH_USER` | *Validate and deploy* | rsync naar de webserver |
| `SSH_TARGET_DIR_PROD`, `SSH_TARGET_DIR_DEV` | *Validate and deploy* | DocumentRoot van productie resp. test |
| `GH_APP_ID`, `GH_APP_PRIVATE_KEY` | bronrepositories | GitHub App met schrijftoegang tot deze repository (zelfde app als de docs.geostandaarden.nl-flow) |

Verder moet in de Actions-instellingen van de repository *"Allow GitHub Actions to
create and approve pull requests"* aan staan (nodig voor de sync-workflow).

## 9. Afhankelijkheden

* Webserver: Apache (`mod_rewrite`, directory listings) en PHP ≥ 7.2. De PHP-pagina's
  gebruiken geen database en doen geen externe requests; de configuratie wordt uit de
  lokale `config/*.json` gelezen.
* GitHub Actions: `actions/checkout`, `actions/create-github-app-token`,
  `tj-actions/changed-files`, `peter-evans/create-pull-request`, en `jq`/`xmllint`
  op de standaard Ubuntu-runners.
* Het publicatiemechanisme is bewust gelijk gehouden aan dat van
  [docs.geostandaarden.nl](https://github.com/Geonovum/docs.geostandaarden.nl)
  (zelfde principes, zelfde secretnamen, zelfde GitHub App), zodat beide sites door
  dezelfde persoon beheerd kunnen worden.
