# THYP_TP3_Recursive

##Upload
Projet d'upload de fichiers vers le répertoire du projet.

Création d'une page permettant d'upload un fichier avec la possibilité de le renommer. Une vérification du type de fichier est faites afin de limiter les documents autorisés (pdf, jpg png uniquement par exemple).
En parallèle de l'upload du fichier, des informations sont enregistrés dans une table qui correspondent au nouveau nom du fichier, à l'extension ainsi que le chemin du fichier. Cependant, si le nom de l'image est déjà présente, affiche un message pour le notifier à l'utilisateur.

##Messages d'erreurs

Plusieurs type de messages sont affichés:

    Message de succes lorsque les informations d'une image sont bien sauvegardés dans la BDD.
    Message d'un problème concernant le type de fichier qui n'est pas prit en charge.
    Message d'erreur lorsqu'une image portant le même nom est déjà présente dans la BDD.
    Message d'erreur lorsque le fichier à upload rencontre un problème.

##Lecture Récursive

Lorsqu'un dossier contient plusieurs sous dossiers ainsi que différents types de fichiers, recherche seulement les images au format png, jpg, jpeg afin de sauvegarder leurs données dans la BDD. De plus, s'il existe des informations d'une image présente dans la BDD mais n'existant pas dans le dossier concerné, les données sont supprimés afin de recenser seulement les images existantes.

##Pagination

Des vérifications sont faites afin de permettre seulement l'affichage des pages contenants des informations. Lorsque l'utilisateur arrive sur la page, il est directement redirigé vers la page 1. Si l'utilisateur tente de changer le paramètre du numéro de page dans l'url, il est soit redirigé vers la 1ère page, soit vers la dernière page contenant des informations s'il tente d'accéder à une page ne contenant aucunes données.
Des boutons permettent d'accéder aux pages proches de la page actuelle soit via des flèches, soit les numéros de page voisin. Il est également possible d'accéder directement à la 1ère ou dernière page via des boutons.

©Adam MKHININI
