<nav id="loginbar">
<?php

if(!isset($_SESSION['ID'])) {
    
    $sessionID = '';
    ?>

    <a class="bouton" href="?page=profil" title="Se connecter" >Se connecter</a>

    <a class="bouton" href="?page=profil" title="Créer un compte" >Créer un compte</a>

<?php
    
} else {
    $sessionID = filter_var($_SESSION['ID'], FILTER_SANITIZE_NUMBER_INT) ;
    ?>
    <img src="<?php echo IMG_LINK.'/'.$_SESSION['avatar'];?>" alt="nothing">
    
    <form method="POST" action="index.php">
    <input type="text" name="message">
    <input type="submit" value="Envoyer">
    </form>
    <a href="logout.php">Se déconnecter</a>
    <a href="index.php?favoris=1">Mes favoris</a>
    <?php

}

?>
</nav>

<?php


//Afficher uniquement les favoris
if(filter_has_var(INPUT_GET, 'favoris')) {
    $favoris = filter_input(INPUT_GET, 'favoris', FILTER_SANITIZE_NUMBER_INT);
}

//Passe aux twits suivants
if(filter_has_var(INPUT_GET, 'next')){
    $pas = filter_input(INPUT_GET, 'next', FILTER_SANITIZE_NUMBER_INT);
    $prev = $pas + $progression;
    $next = $pas - $progression;
}

//Passe aux twits précédents
if(filter_has_var(INPUT_GET, 'prev')){  
    $pas = filter_input(INPUT_GET, 'prev', FILTER_SANITIZE_NUMBER_INT);
    $prev = $pas + $progression;
    $next = $pas - $progression;
    
}

//récupère le numero de page cliquée
if(filter_has_var(INPUT_GET, 'nbpage')){
    $page =  filter_input(INPUT_GET, 'nbpage', FILTER_SANITIZE_NUMBER_INT);
    //$pas = $page;
    
    $prev = (($page-1) * $progression) + $progression;
    $next = (($page-1) * $progression) - $progression ;
    $pas = ($page-1) * $progression;
}

//Vérifie l'action du clique sur le lien correspondant
/* rt = retweet
 * nrt = supprime le retweet
 * fv= favoris
 * nfv = supprime le favoris
 */
if(filter_has_var(INPUT_GET, 'rt') || 
    filter_has_var(INPUT_GET, 'fv') ||
    filter_has_var(INPUT_GET, 'nfv') ||
    filter_has_var(INPUT_GET, 'nrt')) {
    $Twit = filter_input_array(INPUT_GET,$tabAction);
}

//récupère le message en cas de twit
$twit = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_FULL_SPECIAL_CHARS);


//Vérifie qu'il y a une connexion de l'utilisateur
if(isset($_SESSION['ID'])) {
    if(!empty($Twit)) {
//Crée une nouvelle action de l'utilisateur retwit(sup) ou favoris(sup) 
        newAction($Twit,$_SESSION['ID']);
        header('Location: index.php');
    }

    if(!empty($twit) ) {
//enregistre un nouveau twit        
        echo newTwit($twit,$_SESSION['ID']);
        header('Location: index.php');
    }

//récupère les ID des favoris de l'utilisateur    
    $tab_favoris = getFavoris($_SESSION['ID']);
}


//On affiche la liste des favoris ou des twits
if($favoris == 1) {
    $resTwits = getFavoris($_SESSION['ID'],TRUE);
    $nbtwits = nbTwits(15,'favoris');
} else {
    $resTwits = getTwits($pas);
    $nbtwits = nbTwits();
}

$countTwits = count($resTwits);

?>
<nav id="barremenu">
    <span id="gauche">
<?php
if($prev >= 15 && $prev < $nbtwits['nbtwits']){
    ?>
    <a class="bouton" href="index.php?favoris=<?php echo $favoris;?>&prev=<?php echo $prev;?>" title="Anciens"><<</a>
<?php
}
?>
    </span>
    <span id="milieu">
<?php
for($i=1; $i <= $nbtwits['nbpages']; ++$i) {
    echo '<a class="page" href="index.php?favoris='.$favoris.'&nbpage='.$i.'">'.$i.'</a>';
}
?>
    </span>
    <span id="droite">
<?php       
if($pas >0  && $next >= 0){
?>

    <a class="bouton" href="index.php?favoris=<?php echo $favoris;?>&next=<?php echo $next;?>" title="Nouveaux">>></a>
<?php
}
?></span>
</nav>
<?php
if($countTwits > 0) {
    foreach($resTwits as $twit) {
        $dateheure = explode(' ',$twit['created_on']);
// Définit les classes par défaut
        $classfavoris ='';
        $classretwit ='';
// Définit les actions par défaut        
        $actionfavoris = 'fv';
        $actionretwit = 'rt';

// Vérifie si le twit est dans les favoris ou si le twit est à l'utilisateur
        if(in_array($twit['idMessage'], $tab_favoris) || 
                $twit['idUser'] == $sessionID) {
            if($twit['idUser'] != $sessionID){
                $classfavoris = 'nofavoris';                
            }
            $actionfavoris = 'nfv';
        }
// Vérifie si c'est un retwit de l'utilisateur
        if(!is_null($twit['uretwit'])) {
            if($twit['idUser'] == $sessionID){
                $classretwit = 'noretwit';
                $actionretwit = 'nrt';
            }
            
        }

    ?>
    <article class="twit">
        <h1 class="twitdate"><?php echo $dateheure[0];?></h1><h4 class="twitheure"><?php echo $dateheure[1];?></h4>
        <p class="twittext"><?php echo $twit['idMessage'] .' '. mb_substr($twit['message'],0,140).' '.$twit['uretwit'] ; ?>[...]</p>
        <ul class="barreoutils">
            <li class="<?php echo $classretwit ;?> retwit">
                <a href="index.php?<?php echo $actionretwit;?>=<?php echo $twit['idMessage'];?>&origin=<?php echo $twit['idUser'];?>" title="retwit"></a>
            </li>
            <li class="<?php echo $classfavoris ;?> favoris">
                <a href="index.php?<?php echo $actionfavoris;?>=<?php echo $twit['idMessage'];?>" title="favoris"></a>
            </li>
            <li class="twitauteur"><?php echo '@'.$twit['login'];?></li>
        </ul>
        
    </article>

    <?php
    } 
}  else { ?>
    <article class="twit">
    <h1 class="twitdate">AUCUN TWIT</h4>
    <p class="twittext"></p>
    <p class="twitauteur"></p>
    </article>
<?php }
?>
<script type="text/javascript">
$.ajax({
   type: "POST",
   url: "include/profil.php",
   success: function(r,x,y){
     $("body").html($(r));
   }
 });
</script>