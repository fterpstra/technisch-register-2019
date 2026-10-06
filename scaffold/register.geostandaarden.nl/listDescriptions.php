<?php
/*
Renders the "Ingang: soort bestand" column.

Included by index.php, which provides $repoData (for a model page) and the
config file locations. The list of artefact types is driven entirely by
config/descriptions.json:
 - on a model page, only the artefact types for which a directory exists for
   that model are shown;
 - on the index and on cluster pages all artefact types are shown.
The "beschrijving" values may contain HTML (e.g. links).
*/

// font-awesome icons per artefact type; fa-file-o is used for types not listed here
$descriptionIcons = [
  "gmlapplicatieschema" => "fa-file-code-o",
  "xmlschema" => "fa-file-code-o",
  "jsonschema" => "fa-file-code-o",
  "shacl" => "fa-file-code-o",
  "regels" => "fa-file-code-o",
  "waardelijst" => "fa-file-text-o",
  "symbool" => "fa-file-image-o",
];
// artefact types not shown as an entry on the index and cluster pages
$hideOnIndex = ["zipfile"];

$descriptionsArr = json_decode(file_get_contents($descriptionsURL), true);
?>
 <h3>
  Ingang: soort bestand
 </h3>
<?php
foreach ($descriptionsArr as $key => $description) {
  $icon = isset($descriptionIcons[$key]) ? $descriptionIcons[$key] : "fa-file-o";
  if ($repoData) {
    // model page: only show the artefact types available for this model
    $descModelDir = $key."/".$repoData["id"];
    if (!is_dir($descModelDir)) {
      continue;
    }
    $href = $baseURL.$descModelDir;
  } else {
    // index or cluster page: show all artefact types
    if (in_array($key, $hideOnIndex)) {
      continue;
    }
    $href = $baseURL.$key."/";
  }
  ?>
  <p>
   <i class="fa <?=$icon?>">
   </i>
   <span style="margin-left: 25px">
    <a href="<?=$href?>">
     <?=$description["titel"]?>
    </a>
   </span>
  </p>
  <p>
   <span style="margin-left:37px; width: 100%">
     <?=$description["beschrijving"]?>
   </span>
  </p>
<?php } ?>
