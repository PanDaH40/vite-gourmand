<?php

class MenuModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }


    /**
     * Retourne tous les menus.
     */
    public function getAll(): array
    {
        $sql = "
            SELECT
                menu_id,
                titre,
                nombre_personne_minimum,
                prix_par_personne,
                regime,
                description,
                quantite_restante
            FROM menu
            ORDER BY menu_id ASC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /**
     * Retourne un menu grâce à son identifiant.
     */
    public function getById(int $menuId): ?array
    {
        $sql = "
            SELECT
                menu_id,
                titre,
                nombre_personne_minimum,
                prix_par_personne,
                regime,
                description,
                quantite_restante
            FROM menu
            WHERE menu_id = :menu_id
            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            "menu_id" => $menuId
        ]);

        $menu = $stmt->fetch(PDO::FETCH_ASSOC);

        return $menu ?: null;
    }
}