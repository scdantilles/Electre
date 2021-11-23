<?php
session_start();

//mettre à jour les infos du proxy
$proxy = "proxy:port";

$ean = $_GET['ean'];

$ean = isbn2ean($ean);

function isbn2ean($x){
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

function get_access_token($proxy){
  
  //The url you wish to send the POST request to
  $url = "https://login.electre-ng-horsprod.com/auth/realms/electre/protocol/openid-connect/token";

  //The data you want to send via POST
  $fields = [
      'grant_type'      => 'password',
      'scope'           => 'roles',
      'username'        => 'username-de-l-institution',
      'password'        => 'lepassword',
      'client_id'       => 'api-client',
      'client_secret'   => ''
  ];

  //url-ify the data for the POST
  $fields_string = http_build_query($fields);

  //open connection
  $ch = curl_init();

  //set the url, number of POST vars, POST data
  curl_setopt($ch,CURLOPT_URL, $url);
  curl_setopt($ch,CURLOPT_PROXY, $proxy);
  curl_setopt($ch,CURLOPT_POST, count($fields));
  curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);

  //So that curl_exec returns the contents of the cURL; rather than echoing it
  curl_setopt($ch,CURLOPT_RETURNTRANSFER, true); 

  //execute post
  $result = curl_exec($ch);
  $info = curl_getinfo($ch);

  echo "result get token : ".$result;
  
  echo "<hr />";
  
  echo "info get token : ".$info;

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
  
    $_SESSION['access_token'] = get_access_token($proxy);  
    $_SESSION['time'] = time();
  }
  
}else
    
    $_SESSION['access_token'] = get_access_token($proxy); 
    
$access_token = $_SESSION['access_token'];


// Complétez $url avec l'url cible (l'url de la page que vous voulez télécharger)
$url="https://api.demo.electre-ng-horsprod.com/notices/ean/".$ean; 

$headers[] = 'authorization:Bearer '.$access_token.'';
 
// Tableau contenant les options de téléchargement
$options=array(
      CURLOPT_URL            => $url, // Url cible (l'url la page que vous voulez télécharger)
      CURLOPT_RETURNTRANSFER => true, // Retourner le contenu téléchargé dans une chaine (au lieu de l'afficher directement)
      CURLOPT_HEADER         => false, // Ne pas inclure l'entête de réponse du serveur dans la chaine retournée
      CURLOPT_HTTPHEADER     => $headers
);

// Création d'un nouvelle ressource cURL
$CURL=curl_init();
  
curl_setopt($CURL, CURLOPT_PROXY, $proxy);  
// Configuration des options de téléchargement
curl_setopt_array($CURL,$options);

// Exécution de la requête
$content=curl_exec($CURL);      
$info = curl_getinfo($CURL);

echo "<hr />";

echo "content : ".$content;
  
echo "<hr />";

echo "info : ".print_r($info);

//Traitement des données
$data = json_decode($content, true);

$path = $data['notices'][0]['imagetteCouverture'];

$headers[] = 'authorization:Bearer '.$access_token.'';
 
// Tableau contenant les options de téléchargement
$options=array(
      CURLOPT_URL            => $path, // Url cible (l'url la page que vous voulez télécharger)
      CURLOPT_RETURNTRANSFER => true, // Retourner le contenu téléchargé dans une chaine (au lieu de l'afficher directement)
      CURLOPT_HEADER         => false, // Ne pas inclure l'entête de réponse du serveur dans la chaine retournée
      CURLOPT_HTTPHEADER     => $headers
);

$process = curl_init(); 
curl_setopt_array($process,$options);

$content=curl_exec($process);      
$info = curl_getinfo($process);

echo "<hr />";

$imgData = base64_encode($content);

// Format the image SRC:  data:{mime};base64,{data};
$src = 'data: image/jpeg;base64,'.$imgData;

// Affichage
echo '<img src="'.$src.'" alt="couverture du livre">';
  
echo "<hr />";

echo "info2 : ".print_r($info);

// Fermeture de la session cURL
curl_close($CURL);
curl_close($process);


?>
