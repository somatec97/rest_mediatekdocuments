<?php
include_once("ConnexionPDO.php");

/**
 * Classe de construction des requêtes SQL à envoyer à la BDD
 */
class AccessBDD {
	
    public $login="root";
    public $mdp="";
    public $bd="mediatek86";
    public $serveur="localhost";
    public $port="3306";	
    public $conn = null;

    /**
     * constructeur : demande de connexion à la BDD
     */
    public function __construct(){
        try{
            $this->conn = new ConnexionPDO($this->login, $this->mdp, $this->bd, $this->serveur, $this->port);
        }catch(Exception $e){
            throw $e;
        }
    }

    /**
     * récupération de toutes les lignes d'une table
     * @param string $table nom de la table
     * @return lignes de la requete
     */
    public function selectAll($table){
        //echo "table reçue dans selectAll: ";
        //var_dump("table reçue dans selectAll: ", $table); 
        if($this->conn != null){
            switch ($table) {
                case "livre" :
                    return $this->selectAllLivres();
                case "dvd" :
                    return $this->selectAllDvd();
                case "revue" :
                    return $this->selectAllRevues();
                case "commandedocument" :
                    return $this->selectAllCommandesDocument();
                case "abonnementsecheance" :
                    return $this->selectAllAbonnementsEcheance();
                case "exemplaire" :
                    return $this->selectExemplairesRevue();
                case "genre" :
                case "public" :
                case "rayon" :
                case "etat" :
                    // select portant sur une table contenant juste id et libelle
                    return $this->selectTableSimple($table);
                default:
                    // select portant sur une table, sans condition
                    return $this->selectTable($table);
            }			
        }else{
            return null;
        }
    }

    /**
     * récupération des lignes concernées
     * @param string $table nom de la table
     * @param array $champs nom et valeur de chaque champs de recherche
     * @return lignes répondant aux critères de recherches
     */	
    public function select($table, $champs){
        if($this->conn != null && $champs != null){
            switch($table){
                case "exemplaire" :
                    return $this->selectExemplairesRevue($champs['id']);
                case "dvd" :
                    return $this->selectAllDvd($champs['id']);
                case "revue" :
                    return $this->selectAllRevues($champs['id']);
                case "commandedocument" :
                    return $this->selectAllCommandesDocument($champs['id']);
                case "abonnement" :
                    return $this->selectAllAbonnementsRevues($champs['id']);
                case "exemplairesdocument":
                    return $this->selectAllExemplairesDocument($champs['id']);
                default:                    
                    // cas d'un select sur une table avec recherche sur des champs
                    return $this->selectTableOnConditons($table, $champs);					
            }				
        }else{
                return null;
        }
    }

    /**
     * récupération de toutes les lignes d'une table simple (qui contient juste id et libelle)
     * @param string $table
     * @return lignes triées sur lebelle
     */
    public function selectTableSimple($table){
        $req = "select * from $table order by libelle;";		
        return $this->conn->query($req);	    
    }
    
    /**
     * récupération de toutes les lignes d'une table
     * @param string $table
     * @return toutes les lignes de la table
     */
    public function selectTable($table){
        $req = "select * from $table;";		
        return $this->conn->query($req);        
    }
    
    /**
     * récupération des lignes d'une table dont les champs concernés correspondent aux valeurs
     * @param type $table
     * @param type $champs
     * @return type
     */
    public function selectTableOnConditons($table, $champs){
        // construction de la requête
        $requete = "select * from $table where ";
        foreach ($champs as $key => $value){
            $requete .= "$key=:$key and";
        }
        // (enlève le dernier and)
        $requete = substr($requete, 0, strlen($requete)-3);								
        return $this->conn->query($requete, $champs);		
    }

    /**
     * récupération de toutes les lignes de la table Livre et les tables associées
     * @return lignes de la requete
     */
    public function selectAllLivres(){
        $req = "Select l.id, l.ISBN, l.auteur, d.titre, d.image, l.collection, ";
        $req .= "d.idrayon, d.idpublic, d.idgenre, g.libelle as genre, p.libelle as lePublic, r.libelle as rayon ";
        $req .= "from livre l join document d on l.id=d.id ";
        $req .= "join genre g on g.id=d.idGenre ";
        $req .= "join public p on p.id=d.idPublic ";
        $req .= "join rayon r on r.id=d.idRayon ";
        $req .= "order by titre ";		
        return $this->conn->query($req);
    }	

    /**
     * récupération de toutes les lignes de la table DVD et les tables associées
     * @return lignes de la requete
     */
    public function selectAllDvd(){
        $req = "Select l.id, l.duree, l.realisateur, d.titre, d.image, l.synopsis, ";
        $req .= "d.idrayon, d.idpublic, d.idgenre, g.libelle as genre, p.libelle as lePublic, r.libelle as rayon ";
        $req .= "from dvd l join document d on l.id=d.id ";
        $req .= "join genre g on g.id=d.idGenre ";
        $req .= "join public p on p.id=d.idPublic ";
        $req .= "join rayon r on r.id=d.idRayon ";
        $req .= "order by titre ";	
        return $this->conn->query($req);
    }	

    /**
     * récupération de toutes les lignes de la table Revue et les tables associées
     * @return lignes de la requete
     */
    public function selectAllRevues(){
        $req = "Select l.id, l.periodicite, d.titre, d.image, l.delaiMiseADispo, ";
        $req .= "d.idrayon, d.idpublic, d.idgenre, g.libelle as genre, p.libelle as lePublic, r.libelle as rayon ";
        $req .= "from revue l join document d on l.id=d.id ";
        $req .= "join genre g on g.id=d.idGenre ";
        $req .= "join public p on p.id=d.idPublic ";
        $req .= "join rayon r on r.id=d.idRayon ";
        $req .= "order by titre ";
        return $this->conn->query($req);
    }	

    /**
     * récupération de tous les exemplaires d'une revue
     * @param string $id id de la revue
     * @return lignes de la requete
     */
    public function selectExemplairesRevue($id){
    //var_dump($id); 
    $param = array("id" => $id);
    $req = "SELECT e.id, e.numero, e.dateAchat, e.photo, e.idEtat, et.libelle ";
    $req .= "FROM exemplaire e ";
    $req .= "JOIN document d ON e.id=d.id ";
    $req .= "JOIN etat et ON e.idEtat = et.id ";
    $req .= "WHERE d.id = :id ";
    $req .= "ORDER BY e.dateAchat DESC";
    return $this->conn->query($req, $param);
}

    /**
     * récupération de toutes les commandes d'un document
     * @param string $id id du document concerné
     * @return lignes de la requete
     */
   
    public function selectAllCommandesDocument($id = null) {
    $param = [];
    $req = "SELECT l.nbExemplaire, l.idLivreDvd, l.idSuivi, s.libelle, l.id, 
                   MAX(c.dateCommande) AS dateCommande, SUM(c.montant) AS montant
            FROM commandedocument l
            JOIN suivi s ON s.id = l.idSuivi
            LEFT JOIN commande c ON l.id = c.id ";

    if (!empty($id)) {
        $req .= "WHERE l.idLivreDvd = :id ";
        $param = ["id" => $id];
    }

    $req .= "GROUP BY l.id ORDER BY dateCommande DESC";

    return $this->conn->query($req, $param);
    }
    
    /**
     * récupération de tout les abonnements d'une revue
     * @param string $id id de l'abonnement de la revue concernée
     * @return lignes de la requete
     */
   
    public function selectAllAbonnementsRevues($id){
        $param = array(":id" => $id);

    
    $req = "SELECT a.id AS id, c.dateCommande, c.montant, a.dateFinAbonnement, a.idRevue, r.titre ";
    $req .= "FROM commande c ";
    $req .= "JOIN abonnement a ON c.id = a.id ";
    $req .= "JOIN revue r ON r.id = a.idRevue ";
    $req .= "WHERE a.idRevue = :id ";
    $req .= "ORDER BY c.dateCommande DESC";

    return $this->conn->queryAll($req, $param);
    }


 
    /**
     * récupération de tout les abonnements arrivant à échéance dans 30 jours
     * @return lignes de la requête
     */
   public function selectAllAbonnementsEcheance(){
        $req = "SELECT a.id AS id, c.dateCommande, c.montant, a.dateFinAbonnement, a.idRevue, doc.titre 
            FROM commande c 
            JOIN abonnement a ON c.id = a.id 
            JOIN revue r ON r.id = a.idRevue 
            JOIN document doc ON doc.id = r.id
            WHERE a.dateFinAbonnement <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
            ORDER BY a.dateFinAbonnement ASC";

        try {
            $res = $this->conn->query($req);
            if (!$res) {
                echo "<h2> La requête SQL a échoué.</h2>";
            }
            return $res;
        }catch (Exception $e) {
           // echo "<h2> Exception capturée :</h2><pre>" . $e->getMessage() . "</pre>";
            return null;
        }
    }
    
    /**
    * Récupération de tous les exemplaires d'un document
    * @param string $id id du document concerné
    * @return lignes de la requête
    */
    public function selectAllExemplairesDocument($id){
    $param = array(
                "id" => $id
        );
        $req = "select ex.id, ex.numero, ex.dateAchat, ex.photo, ex.idEtat, et.libelle ";
        $req .= "from exemplaire ex JOIN etat et ON ex.idEtat = et.id ";
        $req .= "where ex.id = :id ";
        $req .= "order by ex.dateAchat DESC";       
        return $this->conn->query($req, $param);
    }  

    /**
     * suppresion d'une ou plusieurs lignes dans une table
     * @param string $table nom de la table
     * @param array $champs nom et valeur de chaque champs
     * @return true si la suppression a fonctionné
     */	
    public function delete($table, $champs){
        if($this->conn != null){
            // construction de la requête
            $requete = "delete from $table where ";
            foreach ($champs as $key => $value){
                $requete .= "$key=:$key and ";
            }
            // (enlève le dernier and)
            $requete = substr($requete, 0, strlen($requete)-5);   
            return $this->conn->execute($requete, $champs);		
        }else{
            return null;
        }
    }

    /**
     * ajout d'une ligne dans une table
     * @param string $table nom de la table
     * @param array $champs nom et valeur de chaque champs de la ligne
     * @return true si l'ajout a fonctionné
     */	
    public function insertOne($table, $champs){
        if($this->conn != null && $champs != null){
            $requete = "INSERT INTO $table (";
            foreach ($champs as $key => $value){
                    $requete .= "$key,";
            }
            $requete = rtrim($requete, ",") . ") VALUES (";
            foreach ($champs as $key => $value){
                $requete .= ":$key,";
            }
            $requete = rtrim($requete, ",") . ");";

            // DEBUG : log de la requête
            file_put_contents("debug_insert.log", "REQUETE : $requete\nCHAMPS : " . print_r($champs, true));

            try {
                #return $this->conn->execute($requete, $champs);
                $ok = $this->conn->execute($requete, $champs);

    if (!$ok) {
        file_put_contents("pdo_error.log", "Échec de execute() sans exception.");
    }

    return $ok;
            } catch (PDOException $e) {
                file_put_contents("pdo_error.log", $e->getMessage()); // Écrit dans un fichier
                echo "<h1>ERREUR PDO :</h1><pre>" . $e->getMessage() . "</pre>"; // S'affiche direct dans navigateur ou appel
                exit;
            }

        } else {
            return null;
        }
        
    }


    /**
     * modification d'une ligne dans une table
     * @param string $table nom de la table
     * @param string $id id de la ligne à modifier
     * @param array $param nom et valeur de chaque champs de la ligne
     * @return true si la modification a fonctionné
     */	
    public function updateOne($table, $id, $champs){
        if($this->conn != null && $champs != null){
            switch($table){
 

                case "exemplairesdocument":
 

                    $champsExemplaire = [
 

                        'id' => $champs['Id'],
 

                        'numero' => $champs['Numero'],
 

                        'dateAchat' => $champs['DateAchat'],
 

                        'photo' => $champs['Photo'],
 

                        'idEtat' => $champs['IdEtat']
 

                    ];
 

                    $requete = "UPDATE exemplaire SET ";
 

                    foreach ($champsExemplaire as $key => $value) {
 

                        $requete .= "$key=:$key,";
 

                    }
 

                    $requete = substr($requete, 0, strlen($requete)-1);
 

                    $requete .= " WHERE id=:id AND numero=:numero;";
 

                    $champsExemplaire['numero'] = $id;
 

                    $updateExemplaire = $this->conn->execute($requete, $champsExemplaire);   
 

                    if(!$updateExemplaire){
 

                        return null;
 

                    }
 

                default:
 

                    $champs['id'] = $id;
 

                    $requete = "UPDATE $table SET ";
 

                    foreach ($champs as $key => $value) {
 

                        $requete .= "$key=:$key,";
 

                    }
 

                    $requete = substr($requete, 0, strlen($requete)-1);
 

                    $requete .= " WHERE id=:id;";
 

                    return $this->conn->execute($requete, $champs);                 
 
            }	
            return $this->conn->execute($requete, $champs);		
        }else{
            return null;
        }
    }

}