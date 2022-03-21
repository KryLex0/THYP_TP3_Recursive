<?php

require_once "databaseMethod.php";

//Fixe une limite maximum d'execution du code (cela permet d'éviter un grand temps d'execution si il y a un grand nombre de dossiers à parcourir)
set_time_limit (500);
//$path= "docs";

//type de fichier prit en charge
//$extensionAccepte = array("png", "jpg", "jpeg");

//lancement de la fonction qui explore tout les dossiers dans le répertoire "docs"
//explorerDir($path, $extensionAccepte);



function explorerDir($path, $extensionAccepte, $tableName, $mysqlClient)
{
	//ouvre le dossier mère dans lequel on souhaite chercher ce qu'il contient
	$folder = opendir($path);
	
	//tant qu'on peut lire le contenu du dossier
	while($entree = readdir($folder))
	{		
		//
		if($entree != "." && $entree != "..")
		{
			//si $path."/".$entree (docs/nom/Fichier/A/Explorer) correspond à un dossier
			if(is_dir($path."/".$entree))
			{
				//sauvegarde le chemin dans une variable temporaire
				$sav_path = $path;
				//ajoute le nom du sous dossier au chemin (docs => docs/dir1)
				$path .= "/".$entree;
				//relance la fonction avec le chemin du sous dossier 
				explorerDir($path, $extensionAccepte, $tableName, $mysqlClient);
				//réattribue le chemin initial grâce à la variable temporaire utilisé au dessus
				$path = $sav_path;
			}
			//sinon, cela correspond à un fichier
			else
			{
				//crée une variable qui va contenir le chemin vers le fichier (docs/dir1/image1.png)
				$path_source = $path."/".$entree;
				//séparation par rapport au caractère "/" dans le chemin
				$array = explode('/', $path_source);
				//séparation par rapport au caractère "." dans le nom du fichier (qui correspond au dernier index de la séparation précédente)
				//cela permet d'obtenir une array contenant [0]=>nomDuFichier, [1]=>extensionDuFichier
				$array1 = explode('.', end($array));
				//attribue le dernier index de la séparation précédente (extension) à une variable
            	$extension = strtolower(end($array1));
				
				//si l'extension du fichier trouvé au dessus est acceptée (png, jpg, jpeg)
				if(in_array($extension, $extensionAccepte)){
					$cheminDest = dirname($path_source);
					$nomFichier = $array1[0];
					$tailleImage = filesize($path_source);

					if(!dataIsInDB($mysqlClient, $tableName, "nom_fichier", $nomFichier, "chemin_fichier", $cheminDest)){
                        //insertion des données dans la BDD
                        $sqlQuery = "INSERT INTO uploadfilesdata(nom_fichier, extension_fichier, chemin_fichier, taille_image) VALUES ('$nomFichier', '$extension', '".$cheminDest."', '$tailleImage')";
						$result = $mysqlClient->prepare($sqlQuery);
                        $result->execute();
                    }

				}

			}
		}
	}
	//fermeture du dossier mère "docs"
	closedir($folder);
}
?>
