# Electre

La personnalisation des vignettes dans Primo VE est détaillée dans le lien suivant : 
https://knowledge.exlibrisgroup.com/Primo/Product_Documentation/020Primo_VE/025Display_Configuration/Configuring_Thumbnail_Templates_for_Primo_VE

C’est à partir des informations de cette page que nous avons, à l’Université des Antilles, développé un script php qui utilise l’API d’Electre pour aller chercher une couverture et l’afficher à partir d’un EAN.
C’est le chemin vers ce script qui est renseigné dans la configuration d’Alma mentionnée ci-dessus, avec les paramètres demandés en plus. (où trouver l’EAN dans la notice par exemple)

Pensez à mettre à jour les valeurs entre '' des lignes 45 et 46 du fichier electre.php avec les éléments fournis par Electre pour l’utilisation de leur API.
Et pour que ça marche il faut que le fichier soit hébergé sur un serveur web accessible sur internet et en https.

Pré requis: activer les sessions php, et la libraire curl pour php.
