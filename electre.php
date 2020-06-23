<?php

// Définit le contenu de l'en-tête - dans ce cas, image/jpeg, nécessaire pour alma
header('Content-Type: image/jpeg');

session_start();

/*
Ci-dessous les informations pour connexion à l’API TEST.

Point d’entrée : https://electre3test-api.bvdep.com/v1.0

Access token URI : https://electre3test-idp.bvdep.com/connect/token

Authorization URI : https://electre3test-idp.bvdep.com/connect/authorize

Authorization grants : client_credentials

Authorization scopes : webapi
*/

// remplacer les valeurs par celles fournies par Electre
$key = 'cle_demo_enrichissements_electre';
$secret = 'cle-secrete';

$ean = $_GET['ean'];

$ean = isbn2ean($ean);

function isbn2ean($x)
{
  $x = str_replace("-","",$x);
  $x = str_replace(" ","",$x);
  if(strlen($x) < 10) $x = $x."X";
  if(strlen($x) == 10) // ISBN10
  {
    $x = substr($x,0,-1);
    $x = "978".$x;
    $code = $x;
    $x = str_split($x);
    $i = 0;
    while($i2 <= 11)
    {
      if($i2%2 == 0) $p = "1";
      else $p = "3";
      $r += $x[$i] * $p;
      if($x[$i] != "-") $i2++;
      $i++;
    }
    $q = floor($r/10);
    $x = 10 - ($r - $q * 10);
    if($x == "10") $x = "0";
    $x = $code.$x;
  }
  return $x;
}


function get_access_token($key, $secret){
  
  //The url you wish to send the POST request to
  $url = "https://electre3test-idp.bvdep.com/connect/token";

  //The data you want to send via POST
  $fields = [
      'grant_type'      => 'client_credentials',
      'scope'           => 'webapi',
      'client_id'       => $key,
      'client_secret'   => $secret
  ];

  //url-ify the data for the POST
  $fields_string = http_build_query($fields);

  //open connection
  $ch = curl_init();

  //set the url, number of POST vars, POST data
  curl_setopt($ch,CURLOPT_URL, $url);
  curl_setopt($ch,CURLOPT_POST, count($fields));
  curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);

  //So that curl_exec returns the contents of the cURL; rather than echoing it
  curl_setopt($ch,CURLOPT_RETURNTRANSFER, true); 

  //execute post
  $result = curl_exec($ch);
  $info = curl_getinfo($ch);

  //echo $result;

  curl_close($ch);

  $jsonresult = json_decode($result);
  
  if($info['http_code'] == 200)
  
    return $jsonresult->{'access_token'};
    
  else
    
    return NULL;
  
}

if(!isset($_SESSION['time'])){
  
  $_SESSION['time'] = time();
    
}


$diff = time() - $_SESSION['time'];


if(isset($_SESSION['access_token'])){
  
  if($diff > 3500){      
  
    $_SESSION['access_token'] = get_access_token($key, $secret);  
    $_SESSION['time'] = time();
  }
  
}else
    
    $_SESSION['access_token'] = get_access_token($key, $secret); 
    
$access_token = $_SESSION['access_token'];

// Complétez $url avec l'url cible (l'url de la page que vous voulez télécharger)
$url="https://electre3test-api.bvdep.com/v1.0/eans/".$ean."/cover?access_token=".$access_token; 

$headers[] = 'authorization:Bearer';
 
// Tableau contenant les options de téléchargement
$options=array(
      CURLOPT_URL            => $url, // Url cible (l'url la page que vous voulez télécharger)
      CURLOPT_RETURNTRANSFER => true, // Retourner le contenu téléchargé dans une chaine (au lieu de l'afficher directement)
      CURLOPT_HEADER         => false, // Ne pas inclure l'entête de réponse du serveur dans la chaine retournée
      CURLOPT_HTTPHEADER     => $headers
);
 
// Création d'un nouvelle ressource cURL
$CURL=curl_init();
 
// Configuration des options de téléchargement
curl_setopt_array($CURL,$options);

// Exécution de la requête
$content=curl_exec($CURL);      // Le contenu téléchargé est enregistré dans la variable $content.
$info = curl_getinfo($CURL);


echo $content;
 

// Fermeture de la session cURL
curl_close($CURL);

?>
