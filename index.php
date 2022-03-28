<!DOCTYPE html>
<link rel="stylesheet" href="style/upload.css">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="script/script.js"></script>


<h1 style="text-align:center;">Upload</h1>

<?php

$pathParent = dirname(__FILE__);
//appel d'un fichier contenant les login de la BDD
require_once "credentials/credentials.php";

//appel d'un fichier contenant les fonctions de la BDD
require_once "databaseMethod.php";
//appel d'un fichier contenant la fonctions de lecture recursive
require_once "LireRecursDir.php";

$path = "docs";

//connexion a la BDD
$mysqlClient = new PDO($dbname, $login, $password);

//si aucun paramètre n'est passé dans l'url, redirige automatiquement vers la page numéro 1
if(!$_GET["numPage"]){
    header("LOCATION: ?numPage=1");
    exit();
}

//taille max de 8 Mo
$sizeMax = 8;
$tailleMaxFichier = $sizeMax * 1024 * 1024;
//chemin des fichiers upload
$cheminDest = "uploadFiles";
$pathImage = $pathParent . "\\" . $cheminDest;

//type de fichier prit en charge
$extensionAccepte = array("png", "jpg", "jpeg");
//nom de la table dans laquelle récupérer les données
$tableName = "uploadfilesdata";

//nombre d'element à afficher par page
$nbElemPage = 6;

//permet d'obtenir le nombre de page qui contiendront 6 elements de la BDD par page (58/6 arrondi au supérieur donne 10 pages de 6 élements max) 
if(ceil(getDatabaseCount($mysqlClient, $tableName)/$nbElemPage) == 0){
    $nbPageData = 1;
}else{
    $nbPageData = ceil(getDatabaseCount($mysqlClient, $tableName)/$nbElemPage);
}

//offset qui permet d'obtenir les lignes de données à partir du numéro de page (page 2 => de l'élement 7 à ...)
$numDepartElem = $nbElemPage * $_GET['numPage'] - $nbElemPage;
//si l'utilisateur change le numéro de page vers un nombre trop grand (sans données donc), redirige vers la dernière page contenant des données
verifLastPage($mysqlClient, $tableName, $nbElemPage, $numDepartElem, $nbPageData);

if(!file_exists($cheminDest)){
    mkdir($cheminDest);
}


//affiche un message en tête de page suivant la réussite ou non de l'upload grâce à un paramètre présent dans l'url
if(isset($_GET["uploadSuccess"])){
    switch($_GET["uploadSuccess"]){
        case "true":
            echo "<p style='text-align:center;background-color:lightgreen;font-size:30px;'>Upload du fichier réussit.</p>";
            break;
        case "false":
            echo "<p style='text-align:center;background-color:yellow;font-size:30px;'>L'image est déjà présente dans la BDD.</p>";
            break;
        case "extension":
            echo "<p style='text-align:center;background-color:orange;font-size:30px;'>Le type de fichier n'est pas prit en charge.</p>";
            break;
        case "error":
            echo "<p style='text-align:center;background-color:red;font-size:30px;'>Une erreur est survenue lors de l'upload. Veuillez réessayer.</p>";
            break;
        case "scan":
            echo "<p style='text-align:center;background-color:lightblue;font-size:30px;'>L'ensemble des images du dossier ont été scannés.</p>";
            break;
    }

}


?>

<!--Grille contenant les images-->
    <div class="divClass">
        <?php
        $i=0;
        
        //affiche les élements retournés de la requete SQL
        $dataFromDatanase = getDataDatabase($mysqlClient, $tableName, $nbElemPage, $numDepartElem);
        if(empty($dataFromDatanase)){
            echo "<p><strong>Aucunes images n'est présente dans la Base de Données.</strong></p>";
        }else{
            foreach($dataFromDatanase as $tab => $val) {
                $i++;
            ?>
                <div class="divClassData">
                    <img class="image" src="<?php echo $val["chemin_fichier"] . "/" . $val["nom_fichier"] . "." . $val["extension_fichier"]; ?>" onClick="$(this).toggleClass('zoomed');">
                    <p><strong style="text-decoration: underline;">Nom de l'image:</strong><?php echo(" " . $val["nom_fichier"] . "." . $val["extension_fichier"]); ?></p>
                    <p><strong style="text-decoration: underline;">Taille de l'image:</strong><?php echo(" " . $val["taille_image"] . " octets");?></p>
                    <p><strong style="text-decoration: underline;">Chemin de l'image:</strong><?php echo(" " . $val["chemin_fichier"]); ?></p>
                </div>

            <?php 
                //toutes les 2 itérations, revient à la ligne afin d'avoir 2 éléments par ligne
                if($i%2==0){
                    ?>
                    <div style='clear:both'></div>
                    <?php
                }
            }
        }
        ?>
    </div>


  <!--Bouton de numéro de page
1, 2, [3], 4, 5, ..., 30-->
<div class="changePage">
    <div class="changePageButton">
        
        <!-- Affiche un bouton qui redirige vers la page 1 lorsqu'on se trouve sur une autre page que la page 1 -->
        <?php if($_GET["numPage"] != 1){?>
            <!-- Aller à la 1ère page -->
            <a href=<?php echo getPageUrlByNumber(1); ?> class="btn btn-info" style="background-color:red"><<</a>
            <!-- Aller à la page précédente -->
            <a href=<?php echo getPreviousPageUrl(); ?> class="btn btn-info" style="margin-right:10px;background-color:green"><</a>

            <a href=<?php echo getPageUrlByNumber(1); ?> class="btn btn-info"><?php echo (1); ?></a>
        <?php }


            for($i=-2; $i<3; $i++){
                //s'il y a une différence > 2 entre la 1ère page et la page actuelle (ou entre la dernière page et la page actuelle), affiche [...]
                if($i == -2 && $_GET["numPage"] + $i > 2 || $i == 2 && $_GET["numPage"] + $i < $nbPageData - 2 ){
                    ?><a class="btn btn-info" readonly><?php echo("..."); ?></a><?php
                    
                }
                //affiche le numéro de page actuel
                elseif($i == 0){
                    ?><strong><a class="btn btn-info" style="background-color:grey"><?php echo ($_GET["numPage"] + $i); ?></a></strong><?php
                    
                //affiche les numéros de pages précédents et suivants autour de la page actuelle
                }elseif($_GET["numPage"] + $i > 1 && $_GET["numPage"] + $i < $nbPageData ){
                ?>
                <a href=<?php echo getPageUrlByNumber($_GET["numPage"] + $i); ?> class="btn btn-info"><?php echo ($_GET["numPage"] + $i); ?></a>

            <?php
                }
            }

        //Affiche un bouton qui redirige vers la dernière page lorsqu'on se trouve sur une autre page que la dernière
        if($_GET["numPage"] != $nbPageData){?>
            <a href=<?php echo getPageUrlByNumber($nbPageData); ?> class="btn btn-info"><?php echo ($nbPageData); ?></a>
            <!-- Aller à la page suivante -->
            <a href=<?php echo getNextPageUrl(); ?> class="btn btn-info" style="margin-left:10px;background-color:green">></a>
            <!-- Aller à la dernière page -->
            <a href=<?php echo getPageUrlByNumber($nbPageData); ?> class="btn btn-info" style="background-color:red">>></a>
        <?php } ?>

        
    </div>
</div>


<!--Formulaire d'upload d'une image-->

<div class="divClass">
    <div class="divContainer">
        <form action="" method="post" enctype="multipart/form-data">
            <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo($tailleMaxFichier); ?>">
            <label>Votre fichier: </label>
            <input type="file" name="nomFichier"><br>
            <label>Nouveau nom du fichier: </label><input type="text" name="nouveauNomFichier" placeholder="Nom d'origine si vide"><br><br>
            <input type="submit" value="Envoyer" name="uploadFile">
            <input type="submit" value="Scanner le répertoire" name="scan">
        </form>
    </div>
    <p>Type de fichier prit en charge:
        <!--Affiche les extensions prises en charge avec une virgule pour séparer (sauf le dernier élément)-->
        <?php foreach($extensionAccepte as $val){
            if (array_search($val, $extensionAccepte) == array_key_last($extensionAccepte)){
                echo $val;
            }else{
                echo $val . ", ";
            }
        }
        echo "</br>Taille max d'une image: $sizeMax Mo";
        ?>
    </p>
</div>



    <?php

    //si un fichier est bien envoyé via le formulaire
    if(isset($_FILES["nomFichier"]) && isset($_POST["uploadFile"])){
        //si aucune erreur n'est présente lors de l'upload du fichier
        if ($_FILES["nomFichier"]["error"] == 0) {
            //permet d'obtenir l'extension
            $array = explode('.', $_FILES['nomFichier']['name']);
            $extension = strtolower(end($array));
            $tailleImage = $_FILES["nomFichier"]["size"];
            
            if(in_array($extension, $extensionAccepte)){
                //si l'input du nouveau nom est vide, garde le nom d'origine du fichier
                if($_POST["nouveauNomFichier"] == null){
                    //nom du fichier seul
                    $nomFichier = basename($_FILES["nomFichier"]["name"], "." . $extension);
                
                //sinon, attribue le nouveau nom entré au fichier
                }else{               
                    $nomFichier = $_POST["nouveauNomFichier"];
                }
                try {
                    if(!dataIsInDB($mysqlClient, $tableName, "nom_fichier", $nomFichier, "chemin_fichier", $cheminDest)){
                        //insertion des données dans la BDD
                        $sqlQuery = "INSERT INTO uploadfilesdata(nom_fichier, extension_fichier, chemin_fichier, taille_image) VALUES ('$nomFichier', '$extension', '".$cheminDest."', '$tailleImage')";
                        $result = $mysqlClient->prepare($sqlQuery);
                        $result->execute();
                        //upload le fichier dans un dossier "/uploadFiles"
                        move_uploaded_file($_FILES["nomFichier"]["tmp_name"], $pathImage . "\\" . $nomFichier . "." . $extension);
                        
                        //redirection vers la même page avec des paramètre pour savoir si l'upload est réussit ou non
                        redirectToNewURL("true");
                    }else{
                        redirectToNewURL("false");
                        
                    }
                } catch(PDOException $e) {
                    die('Erreur de connexion à la BDD. Erreur n°' . $e->getCode() . ':' . $e->getMessage());
                }
                //affiche un message d'upload réussi
                ?>
                <p style="text-align:center;">Upload du fichier <strong>'<?php echo $nomFichier . "." . $extension; ?>'</strong> réussit.</p>

                <?php
                //affiche un problème concernant le type de document ajouté qui n'est pas prit en charge
            }else{
                redirectToNewURL("extension");
            }
        }else{
            redirectToNewURL("error");
        }

    }


function redirectToNewURL($message){
    $URL=strtok($_SERVER["REQUEST_URI"], '?') . "?numPage=1&uploadSuccess=" . $message;
    echo "<script type='text/javascript'>document.location.href='{$URL}';</script>";
    echo '<META HTTP-EQUIV="refresh" content="0;URL=' . $URL . '">';
}


    if(isset($_POST["scan"])){
        //fonction de lecture recursive dans un dossier séparé
        explorerDir($path, $extensionAccepte, $tableName, $mysqlClient);
        //fonction de lecture recursive dans le dossier d'image par défaut
        explorerDir($cheminDest, $extensionAccepte, $tableName, $mysqlClient);
        verifFileExist($mysqlClient, $tableName);
        redirectToNewURL("scan");
        
        
    }




?>