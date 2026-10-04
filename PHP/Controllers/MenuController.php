<?php

require_once __DIR__ . "/../Models/MenuModel.php";

class MenuController
{
    private MenuModel $menuModel;

    public function __construct(PDO $pdo)
    {
        $this->menuModel = new MenuModel($pdo);
    }


    /**
     * Retourne tous les menus.
     */
    public function getAllMenus(): array
    {
        return $this->menuModel->getAll();
    }


    /**
     * Retourne un menu selon son identifiant.
     */
    public function getMenuById(int $menuId): ?array
    {
        if ($menuId <= 0) {
            return null;
        }

        return $this->menuModel->getById($menuId);
    }
}