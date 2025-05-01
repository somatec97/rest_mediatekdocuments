<?php
 include_once("AccessBDD.php");
 
 /**
  * Contrôleur : reçoit et traite les demandes du point d'entrée
  */
 class Controle{
 	
     private $accessBDD;
 
     /**
      * Constructeur : récupération de l'instance d'accès à la BDD
      */
     public function __construct(){
         try{
             $this->accessBDD = new AccessBDD();
         }catch(Exception $e){
             $this->reponse(500, "erreur serveur");
             die();
         }
     }
 
     /**
      * réponse renvoyée (affichée) au client au format json
      * @param int $code code standard HTTP
      * @param string $message message correspondant au code
      * @param array $result résultat de la demande 
      */
     private function reponse($code, $message, $result=""){
         $retour = array(
             'code' => $code,
             'message' => $message,
             'result' => $result
         );
         echo json_encode($retour, JSON_UNESCAPED_UNICODE);
     }
 
     /**
      * requete arrivée en GET (select)
      * @param string $table nom de la table
      * @param type $champs nom et valeur des champs de recherche
      */
     public function get($table, $champs){
    $result = null;

    // Affichage pour le débogage : affichage de la table et des paramètres
   // echo "Table: $table\n";
   // echo "Champs: ";
    //var_dump($champs);
    
    // Cas spécifique pour la table "exemplaire"
    if ($table == "exemplaire") {
        // Débogage de l'ID
       // echo "ID reçu: " . (isset($champs['id']) ? $champs['id'] : 'ID non trouvé') . "\n";
        
        // Si l'ID n'est pas trouvé dans $champs, on le récupère de l'URL
        if (!isset($champs['id']) && isset($_GET['id'])) {
            $champs['id'] = $_GET['id'];
        }
        
        // Débogage pour vérifier l'ID après avoir essayé de le récupérer
        //echo "ID après récupération : " . (isset($champs['id']) ? $champs['id'] : 'ID non trouvé') . "\n";

        // Si l'ID est maintenant présent, on appelle selectExemplairesRevue
        if (isset($champs['id'])) {
            $result = $this->accessBDD->selectExemplairesRevue($champs['id']);
        } else {
            // Si l'ID est toujours absent, on renvoie une erreur
            $this->reponse(400, "Paramètre 'id' manquant pour récupérer les exemplaires.");
            return; // Arrêter ici si l'ID est manquant
        }
    } else {
        // Cas généraux pour toutes les autres tables
        if ($champs == "") {
            // Si aucun champ n'est passé, on appelle selectAll
            $result = $this->accessBDD->selectAll($table);
        } else {
            // Si des champs sont spécifiés, on appelle select
            $result = $this->accessBDD->select($table, $champs);
        }
    }

    // Vérification du résultat
    if (gettype($result) != "array" && ($result == false || $result == null)) {
        $this->reponse(400, "requete invalide");
    } else {
        $this->reponse(200, "OK", $result);
    }
}

 
     /**
      * requete arrivée en DELETE
      * @param string $table nom de la table
      * @param array $champs nom et valeur des champs
      */
     public function delete($table, $champs){
         $result = $this->accessBDD->delete($table, $champs);	
         if ($result == null || $result == false){
             $this->reponse(400, "requete invalide");
         }else{	
             $this->reponse(200, "OK");
         }
     }
 
     /**
      * requete arrivée en POST (insert)
      * @param string $table nom de la table
      * @param array $champs nom et valeur des champs
      */
     public function post($table, $champs){
         $result = $this->accessBDD->insertOne($table, $champs);	
         if ($result == null || $result == false){
             $this->reponse(400, "requete invalide");
         }else{	
             $this->reponse(200, "OK");
         }
     }
 
     /**
      * requete arrivée en PUT (update)
      * @param string $table nom de la table
      * @param string $id valeur de l'id
      * @param array $champs nom et valeur des champs
      */
     public function put($table, $id, $champs){
         $result = $this->accessBDD->updateOne($table, $id, $champs);	
         if ($result == null || $result == false){
             $this->reponse(400, "requete invalide");
         }else{	
             $this->reponse(200, "OK");
         }
     }
 	
     /**
      * login et/ou pwd incorrects
      */
     public function unauthorized(){
         $this->reponse(401, "authentification incorrecte");
     }
 }