<?php

class commander {
	
	// Objet PDO servant à la connexion à la base
	private $pdo;

	// Connexion à la base de données
	public function __construct() {
		$config = parse_ini_file("config.ini");
		
		try {
			$this->pdo = new \PDO("mysql:host=".$config["host"].";dbname=".$config["database"].";charset=utf8", $config["user"], $config["password"]);
		} catch(Exception $e) {
			echo $e->getMessage();
		}
	}
	
	// Récupère la liste des produits commandés d'une commande
	public function getProduitsCommande($commande) {
		$sql = "SELECT codeProduit, quantite FROM commander WHERE numeroCommande = :numero";
		
		$req = $this->pdo->prepare($sql);
		$req->bindParam(':numero', $commande, PDO::PARAM_INT);
		$req->execute();
		
		return $req->fetchAll();
	}
}