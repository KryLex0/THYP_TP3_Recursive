<?php

//fonction qui retourne le nombre de lignes dans la BDD
function getDatabaseCount($mysqlClient, $table){
    $sqlQueryCount = "SELECT COUNT(*) FROM $table";

    $result = $mysqlClient->prepare($sqlQueryCount);
    $result->execute();
    $databaseCount = $result->fetchAll();
    return $databaseCount[0][0];
}

function getDataDatabase($mysqlClient, $table, $nbElemPage, $numDepartElem){
    //requete pour obtenir les X lignes de la BDD en fonction de l'offset (la page ou l'on se trouve)
    //avec une limite fixé du nombre de résultat à retourner
    $sqlQueryData = "SELECT * FROM $table ORDER BY id DESC LIMIT $nbElemPage OFFSET $numDepartElem";

    $result = $mysqlClient->prepare($sqlQueryData);
    $result->execute();
    $dataDatabase = $result->fetchAll();
    return $dataDatabase;
}

//fonction qui permet d'obtenir l'url de la page précédente
function getPreviousPageUrl(){
    $previousPageNumber = $_GET["numPage"] - 1;
    $baseUrl = strtok($_SERVER['REQUEST_URI'], '?');
    $previousPageUrl = $baseUrl . "?numPage=" . $previousPageNumber;
    return $previousPageUrl;
}

//fonction qui permet d'obtenir l'url de la page suivante
function getNextPageUrl(){
    $nextPageNumber = $_GET["numPage"] + 1;
    $baseUrl = strtok($_SERVER['REQUEST_URI'], '?');
    $nextPageUrl = $baseUrl . "?numPage=" . $nextPageNumber;
    return $nextPageUrl;
}

//fonction qui permet d'obtenir une url en fonction du numéro de page passé en paramètre
function getPageUrlByNumber($numNewPage){
    $baseUrl = strtok($_SERVER['REQUEST_URI'], '?');
    $nextPageUrl = $baseUrl . "?numPage=" . $numNewPage;
    return $nextPageUrl;
}

//si les données retournés de la requete sont vides, modifie une variable de l'url et affiche la dernière page possédant des résultats 
function verifLastPage($mysqlClient, $table, $nbElemPage, $numDepartElem, $nbPageData){
    if(!getDataDatabase($mysqlClient, $table, $nbElemPage, $numDepartElem)){
        if($_GET["numPage"] > $nbPageData){
            $query = $_GET;
            $query['numPage'] = $nbPageData;
            $query_result = http_build_query($query);

            header("LOCATION: ?$query_result");
            exit();
        }
    }
}

//vérifie si une image est déjà présente dans la BDD
function dataIsInDB($mysqlClient, $table, $nomColonne1, $donneeLigne1, $nomColonne2, $donneeLigne2){
    $sqlQueryData = "SELECT * FROM $table WHERE $nomColonne1='$donneeLigne1' AND $nomColonne2='$donneeLigne2'";
    
    $result = $mysqlClient->prepare($sqlQueryData);
    $result->execute();
    $dataDatabase = $result->fetchAll();
    $isInDB = false;
    if(!empty($dataDatabase)){
        $isInDB = true;
    }
    return $isInDB;
}

//vérifie si une image présente dans la BDD existe également dans les dossiers de l'application
function verifFileExist($mysqlClient, $table){
    $sqlQueryData = "SELECT * FROM $table";
    
    $result = $mysqlClient->prepare($sqlQueryData);
    $result->execute();
    $dataDatabase = $result->fetchAll();
    foreach($dataDatabase as $key){
        //si l'image présente dans la BDD n'est pas présente dans les dossiers
        if(!file_exists($key["chemin_fichier"] . "/" . $key["nom_fichier"] . "." . $key["extension_fichier"])){
            //supprime les infos de l'image de la BDD
            removeDataDatabase($mysqlClient, $table, $key["nom_fichier"], $key["extension_fichier"], $key["chemin_fichier"]);
        }
    }
    
}

//fonction qui supprime les données d'une image de la BDD suivant son nom, son extension ainsi que le chemin
function removeDataDatabase($mysqlClient, $table, $nom_fichier, $extension_fichier, $chemin_fichier){
    $sqlQuery = "DELETE FROM $table WHERE nom_fichier='$nom_fichier' AND extension_fichier='$extension_fichier' AND chemin_fichier='".$chemin_fichier."'";
	$result = $mysqlClient->prepare($sqlQuery);
    $result->execute();
}


?>