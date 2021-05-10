<?php

class commande {
	
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
	
	// Récupérer toutes les commandes d'un client passé en paramètre
	public function getCommandesClient($client) {
		$sql = "SELECT * FROM commande WHERE idClient = :id";
		
		$req = $this->pdo->prepare($sql);
		$req->bindParam(':id', $client, PDO::PARAM_INT);
		$req->execute();
		
		return $req->fetchAll();
	}
	
	// Permet de créer la commande du client passé en paramètre avec l'ensemble des articles qu'il a commandé en paramètre
	public function validerCommande($client, $lesArticles) {
		$sql = "INSERT INTO commande (dateCommande, idClient) VALUES (CURRENT_DATE, :id)";
		
		$req = $this->pdo->prepare($sql);
		$req->bindParam(':id', $client, PDO::PARAM_INT);
		$req->execute();


		$sql = "SELECT * FROM commande WHERE dateCommande = :dateC AND idClient = :id ORDER BY numeroCommande DESC LIMIT 1";

		$req = $this->pdo->prepare($sql);
		$laDate = date('Y-m-d');
		$req->bindParam(':dateC', $laDate, PDO::PARAM_STR);
		$req->bindParam(':id', $client, PDO::PARAM_INT);
		$req->execute();

		$numCommande = $req->fetchAll()[0]['numeroCommande'];

		foreach($lesArticles as $article)
		{
			$sql = "INSERT INTO commander (numeroCommande, codeProduit, quantite) VALUES (:numC, :codeP, :quant)";

			$req = $this->pdo->prepare($sql);
			$req->bindParam(':numC', $numCommande, PDO::PARAM_INT);
			$req->bindParam(':codeP', $article['codeProduit'], PDO::PARAM_INT);
			$req->bindParam(':quant', $article['quantite'], PDO::PARAM_INT);
			$req->execute();

			$sql = "UPDATE produit SET stockProduit = (SELECT stockProduit WHERE codeProduit = :codeP) - :quant WHERE codeProduit = :codeP";

			$req = $this->pdo->prepare($sql);
			$req->bindParam(':codeP', $article['codeProduit'], PDO::PARAM_INT);
			$req->bindParam(':quant', $article['quantite'], PDO::PARAM_INT);
			$req->execute();
		}
	}
}