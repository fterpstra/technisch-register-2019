<?php
/*
Generates the main index, cluster pages and information model pages of the
technisch register. See the README for the types of pages.

URL patterns (rewritten to ?url=... by .htaccess):
  /                      main index
  /{clusterId}           cluster page, or model page if the cluster has one model
  /{clusterId}/{repoId}  model page
*/
include 'registerConfig.php';

$url = isset($_GET['url']) ? $_GET['url'] : '';
$urlparts = explode("/", $url);
$cluster = False;
$indexPage = True;
$clusterPage = False;
$repo = False;
$clusterData = [];

$clustersArr = json_decode(file_get_contents($clustersURL), true);
$reposArr = json_decode(file_get_contents($reposURL), true);

$repoData = False;
$models = [];
if (count($urlparts) > 0) {
  $cluster = $urlparts[0];
  // if a cluster has 1 model, directly create an informationmodel page, otherwise create a cluster page
  $repo = $cluster;
  if (count($urlparts) > 1) {
    if ($urlparts[1] != "index.html") {
      $repo = $urlparts[1];
    }
  }

  foreach ($clustersArr as $cl) {
    if ($cl["id"] == $cluster) {
      $clusterData = $cl;
      $indexPage = False;
    }
  }

  foreach ($reposArr as $re) {
    // if the id is the same as the repo, the information is not a cluster, but we got the repo data directly.
    if ($re["id"] == $repo) {
      $repoData = $re;
      $indexPage = False;
    }
    if ($re["cluster"] == $cluster && $cluster != "") {
      // get all models for the cluster
      array_push($models, $re);
    }
  }
  if (count($models) > 0 && $repoData == False) {
    // no repos, but models: we are in a clusterPage
    $clusterPage = True;
  }
}

// unknown cluster/repo: fall back to the main index instead of an empty page
if (!$indexPage && !$clusterPage && $repoData == False) {
  $indexPage = True;
  $cluster = False;
  $repo = False;
}
?><html lang="nl">
 <head>
  <meta content="text/html; charset=utf-8" http-equiv="Content-Type"/>
  <title>
   Technisch register voor geo-standaarden in Nederland
  </title>
  <link href="<?=$baseURL;?>resources/css/style.css" rel="stylesheet" type="text/css"/>
  <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet"/>
 </head>
 <body>
  <header>
   <div id="topmenu_container">
     <?php
      include "./resources/html/header.html";
     ?>
   </div>
   <div id="breadctxt_container">
    <div id="breadctxt">
     <a href="<?=$baseURL;?>">
      Technisch register voor geo-standaarden in Nederland
     </a>
     <?php if ($cluster && $clusterData) { ?>
       &gt;
       <a href="<?=$baseURL;?><?=htmlspecialchars($cluster)?>">
        <?=$clusterData["titel_kort"];?>
       </a>
     <?php } ?>
     <?php if ($repo && $repo != $cluster && $repoData) { ?>
       &gt;
       <a href="<?=$baseURL;?><?=htmlspecialchars($cluster)."/".htmlspecialchars($repoData["id"])?>">
        <?=$repoData["titel_kort"];?>
       </a>
     <?php } ?>
    </div>
   </div>
  </header>
  <article class="exp">
   <?php if ($clusterPage || $indexPage) { ?>
   <h2>
    Technisch register voor geo-standaarden in Nederland
   </h2>
   <p>
    Het technisch register is de centrale vindplaats voor de informatiemodellen uit het
    <a href="http://www.geonovum.nl/onderwerpen/basismodel-geo-informatie-nen3610/algemeen-basismodel-geo-informatie-nen3610">
     NEN3610
    </a>
    stelsel, plus de technische standaarden die bij die informatiemodellen horen. Deze technische standaarden implementeren het informatiemodel en haar regels in bijvoorbeeld XML Schema en Schematron, maar het kan ook gaan om implementatiebestanden voor visualisatieregels en iconen. Al deze ‘technische’ bestanden zijn te vinden in dit register.
   </p>
   <p>
    Dit technisch register voor geo-standaarden wordt beheerd door
    <a href="http://www.geonovum.nl">
     Geonovum
    </a>
    . Ook Nederlandse geo-standaarden die niet bij Geonovum in beheer zijn, maar wél onderdeel van het NEN3610 stelsel zijn, zijn hier te vinden. Dit kan ofwel fysiek, ofwel via een referentie zijn naar een eigen register van de beheerder van de desbetreffende standaard.
   </p>
   <div id="container">
    <div id="leftcolumn">
      <h3>
       Ingang: informatiemodel
      </h3>
    <?php
    if ($clusterPage) {
      // only display relevant models with the proper URL
      foreach ($models as $model) {
        ?>
        <p>
         <i class="fa fa-file">
         </i>
         <span style="margin-left: 25px">
          <a href="<?=$baseURL.htmlspecialchars($cluster).'/'.htmlspecialchars($model["id"])?>">
           <?=$model["titel_kort"];?>
          </a>
         </span>
        </p>
        <p>
         <span style="margin-left:37px; width: 100%">
           <?=$model["beschrijving_kort"];?>
         </span>
        </p>
        <?php
      }
    } else {
      // if we are building the general index, display all clusters, with a URL to an index.html page
      foreach ($clustersArr as $cl) {
        ?>
        <p>
         <i class="fa fa-file">
         </i>
         <span style="margin-left: 25px">
          <a href="<?=$baseURL.$cl["id"]?>/index.html">
           <?=$cl["titel_kort"];?>
          </a>
         </span>
        </p>
        <p>
         <span style="margin-left:37px; width: 100%">
           <?=$cl["beschrijving_kort"];?>
         </span>
        </p>
        <?php
      }
    }
    ?>
    </div>
    <div id="rightcolumn">
      <?php
        include "listDescriptions.php";
      ?>
    </div>
    <?php } else {
      // This means this is not a cluster page and not the index page
      // So we are in a repo page, for a specific model. Build the information for this
      $pageData = $clusterData ? $clusterData : $repoData;
      ?>
      <div>
      <h2>
       <?=$pageData["titel"];?>
      </h2>
      <p>
       <?=$pageData["beschrijving"];?>
      </p>
         </div>
         <div id="container">
          <div id="leftcolumn">
            <?php
              include "listDescriptions.php";
            ?>
          </div>
         </div>
    <?php } ?>

   </div>
  </article>
  <footer>
   <?php
    include "./resources/html/footer.html";
   ?>
  </footer>
 </body>
</html>
