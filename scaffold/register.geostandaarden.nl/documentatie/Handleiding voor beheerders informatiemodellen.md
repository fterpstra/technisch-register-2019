# Handleiding voor beheerders informatiemodellen

Deze handleiding beschrijft hoe je de technische bestanden van een informatiemodel
publiceert in het technisch register (https://register.geostandaarden.nl). Met
technische bestanden worden bedoeld: informatiemodellen (XMI of EAP), GML
applicatieschema's, XML schema's, schematron-gebaseerde regels, waardelijsten, WSDL's,
visualisaties (SLD's) en symbolen.

Deze handleiding vervangt de oude handleiding uit
[technisch-register-2019](https://github.com/Geonovum/technisch-register-2019), die het
publiceren via een webhook beschreef. **De webhook bestaat niet meer.** Publicatie
verloopt nu via GitHub releases en GitHub Actions, op dezelfde manier als het
publiceren van documenten op docs.geostandaarden.nl.

Op hoofdlijnen:

1. Je beheert de bestanden in een eigen GitHub repository met een voorgeschreven
   mappenstructuur (§1).
2. Je meldt het informatiemodel eenmalig aan bij het technisch register (§2).
3. Je stelt eenmalig de publicatie-workflow in, óf je gebruikt de dagelijkse
   synchronisatie (§3).
4. Publiceren doe je daarna door in GitHub een **release** aan te maken: een
   **pre-release** verschijnt automatisch op de testomgeving, een **release** wordt —
   na beoordeling door de beheerder van het register — op productie gezet (§4).

## 1. De repository en het voorgeschreven formaat

Maak een repository aan op github.com (of hergebruik een bestaande) en richt de
hoofdmap zo in dat elk artefacttype zijn eigen map heeft:

```
/<artefact-type>/<versie>/<bestanden>
```

`<artefact-type>` moet exact één van de volgende mapnamen zijn (dit zijn de
artefacttypen die het register kent; mappen met andere namen worden genegeerd):

* `informatiemodel`
* `gmlapplicatieschema`
* `xmlschema`
* `regels`
* `waardelijst`
* `wsdl`
* `visualisatie`
* `symbool`
* `zipfile` (zip met het complete informatiemodel; verschijnt op de pagina van het
  model zelf, maar niet als ingang op de hoofd- en clusterpagina's)

Per artefacttype worden de volgende bestandsextensies verwacht:

| Artefact-type | Bestandsextensie |
|---|---|
| informatiemodel | .xmi, .eap |
| gmlapplicatieschema | .xsd |
| xmlschema | .xsd, .wsdl (mag een diepere mappenstructuur bevatten) |
| regels | .sch |
| waardelijst | .xls, .pdf, .doc, .rdf, .xml |
| wsdl | .wsdl |
| visualisatie | .xml (mag een diepere mappenstructuur bevatten) |
| symbool | .eps, .png, .svg |

Voor `<versie>` adviseren wij te werken volgens
[BOMOS](https://www.forumstandaardisatie.nl/fileadmin/os/publicaties/HR_BOMOS__FINAL_web.pdf):
onderscheid tussen major (functionaliteitsaanpassing), minor (kleine verbeteringen) en
bugfixes, bijvoorbeeld `1.0.1`.

**Belangrijk — versies binnen de mappen bijhouden.** Bij elke publicatie wordt per
artefacttype de map van jouw model in het register (`/{artefact-type}/{model-id}/`)
**volledig vervangen** door de inhoud van de release. Het register toont dus altijd
precies wat er in de laatste release staat. Oudere versies blijven alleen
raadpleegbaar als je ze als versie-submappen in je repository laat staan.

## 2. Aanmelden bij het technisch register

Het register accepteert alleen releases van repositories die zijn opgenomen in
[`config/repos.json`](../config/repos.json) van de repository
`Geonovum/register.geostandaarden.nl`. Aanmelden kan op twee manieren:

* **Via de Geonovum helpdesk**: mail naar geostandaarden@geonovum.nl met:
  * de naam van het informatiemodel (bijvoorbeeld "IMGolf") en een korte naam voor
    links en het kruimelpad (bijvoorbeeld "IMGolf");
  * een korte omschrijving van maximaal ±58 tekens (bijvoorbeeld "Informatiemodel
    Golf");
  * een lange omschrijving (de tekst voor de pagina van het model);
  * de URL van de GitHub repository (bijvoorbeeld https://github.com/Geonovum/IMGolf);
  * of het model onder een bestaand cluster valt (bijvoorbeeld BRT of RO
    standaarden), of zelfstandig op de hoofdpagina moet verschijnen.
* **Zelf via GitHub**: open een pull request op
  `Geonovum/register.geostandaarden.nl` die het model toevoegt aan
  `config/repos.json` (en zo nodig `config/cluster.json`). Dit kan rechtstreeks in de
  webinterface van GitHub (potloodje bij het bestand; GitHub maakt automatisch een
  fork en pull request voor je). De betekenis van de velden staat in de
  [handleiding voor beheerders van het technisch register](Handleiding%20voor%20beheerders%20technisch%20register.md).

Wacht met de eerste release tot de aanmelding is verwerkt (de pull request is
gemerged). Een release van een niet-aangemelde repository wordt geweigerd: de
publicatie-workflow stopt dan met de foutmelding dat de repository niet in
`config/repos.json` staat.

## 3. De publicatie instellen (vervangt de webhook)

Er zijn twee routes om releases bij het register te laten aankomen. Kies er één.

### 3.1 Publicatie-workflow in je repository (aanbevolen)

Plaats het voorbeeldbestand
[`examples/source-repo-publish.yml`](../examples/source-repo-publish.yml) uit de
registerrepository in je eigen repository als
`.github/workflows/publish-to-register.yml`, op de default branch.

Daarnaast moeten de secrets `GH_APP_ID` en `GH_APP_PRIVATE_KEY` (de GitHub App
waarmee de workflow in het register mag schrijven) beschikbaar zijn:

* voor repositories binnen de **Geonovum-organisatie** zijn dit al
  organisatiesecrets — je hoeft niets te doen;
* voor repositories van **andere organisaties**: vraag de waarden op bij Geonovum en
  voeg ze toe onder *Settings → Secrets and variables → Actions* van je repository.

De workflow wordt uitsluitend getriggerd door het **publiceren van een release of
pre-release** (en door het promoveren van een bestaande pre-release naar release,
zie §4.3). Gewone commits en pushes publiceren dus niets; ook het aanpassen van
alleen de beschrijvende tekst van een bestaande release doet niets.

### 3.2 Zonder workflow: dagelijkse synchronisatie

Kun of wil je geen workflow en secrets in de repository opnemen, dan hoef je niets in
te stellen: het register haalt elke nacht (rond 05:30 UTC) van alle aangemelde
repositories de **laatste release** op en maakt een pull request zodra die afwijkt
van wat gepubliceerd staat. Houd rekening met de beperkingen van deze route:

* je nieuwe release staat pas de volgende ochtend als pull request klaar (sneller
  kan: vraag de registerbeheerder de synchronisatie handmatig te starten);
* **pre-releases worden genegeerd** — publiceren naar de testomgeving kan alleen via
  de workflow uit §3.1.

## 4. Een release uitbrengen

Ga in je repository naar *Releases* → *Draft a new release*. Vul bij *tag* het
versienummer in (volg de suggesties onder *Semantic versioning*), geef de release een
titel en beschrijving. De release wordt gemaakt van de branch die je selecteert; zorg
dat de artefactmappen daarin actueel zijn.

### 4.1 Testrelease (testomgeving)

Vink **"Set as a pre-release"** aan en klik op *Publish release*. De artefacten staan
binnen enkele minuten op https://test.register.geostandaarden.nl/, zonder
tussenkomst van een beheerder. Controleer daar of alles er staat zoals bedoeld.

*Vereist de workflow-route uit §3.1; de dagelijkse synchronisatie neemt pre-releases
niet mee.*

### 4.2 Productierelease

Laat **"Set as a pre-release"** uitgevinkt en klik op *Publish release*. Er wordt dan
automatisch een pull request geopend op de registerrepository
(*"Automated update from … to main"*). **De publicatie staat pas op
https://register.geostandaarden.nl/ nadat een beheerder van het register deze pull
request heeft beoordeeld en gemerged** — dit is anders dan bij de oude webhook, die
direct publiceerde. Duurt het lang, neem dan contact op met de registerbeheerder.

### 4.3 Een bestaande pre-release naar productie promoveren

Een geteste pre-release hoef je niet opnieuw aan te maken: open de release, klik op
*Edit*, haal het vinkje bij *"Set as a pre-release"* weg en klik op *Update release*.
Dit triggert de publicatie-workflow opnieuw en leidt tot dezelfde pull request als
§4.2. (Bij de sync-route van §3.2 wordt de gepromoveerde release de volgende nacht
opgepikt.)

## 5. Controleren en problemen oplossen

* Het verloop van de publicatie is te volgen onder het tabblad **Actions** van je
  eigen repository (workflow *"Publish release to technisch register"*). De
  beoordeling door de registerbeheerder is te volgen bij de **pull requests** van
  `Geonovum/register.geostandaarden.nl`.
* Veelvoorkomende foutmeldingen van de workflow:
  * *"… is not listed in config/repos.json"*: de repository is nog niet aangemeld
    (§2), of aangemeld onder een andere URL dan die van je repository.
  * *"The release contains none of the artefact directories …"*: de release bevat
    geen enkele map met een van de mapnamen uit §1 — controleer de mapnamen
    (exacte spelling, kleine letters, in de hoofdmap) en of je van de juiste branch
    releaset.
* Op de website toont de pagina van je model alleen de artefacttypen waarvoor
  daadwerkelijk bestanden gepubliceerd zijn.
