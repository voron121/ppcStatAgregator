<?php

namespace Models;

class ProductsGroup extends BaseModel
{
    private $id;
    private $name;
    private $asins;
    private $settings;

    /**
     * @param int $id
     */
    public function setId(int $id) : void
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId() : int
    {
        return $this->id;
    }

    /**
     * @param string $name
     */
    public function setName(string $name) : void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName() : string
    {
        return "" != $this->name ? $this->name : "Группа " . $this->getId();
    }

    /**
     * @param string $asins
     */
    public function setAsins(string $asins) : void
    {
        $this->asins = $asins;
    }

    /**
     * @return string
     */
    public function getAsins() : string
    {
        return $this->asins;
    }

    /**
     * @param string $settings
     */
    public function setSettings(string $settings) : void
    {
        $this->settings = $settings;
    }

    /**
     * @return string
     */
    public function getSettings() : array
    {
        return !is_null($this->settings) ? json_decode($this->settings, true) : [];
    }

    /**
     * @param int $id
     * @return ProductsGroup
     */
    public function find(int $id) : ProductsGroup
    {
        $query = "SELECT id,
                         name,
                         asins,
                         settings
                    FROM product_groups
                    WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->execute(["id" => $id]);
        $stmt->setFetchMode(\PDO::FETCH_CLASS, 'Models\ProductsGroup');
        return $stmt->fetch();
    }

    /**
     * Удалит группу из БД
     * @param int $groupId
     */
    public function removeGroup(int $groupId) : void
    {
        $query = "DELETE FROM product_groups WHERE id = :groupId";
        $stmt = $this->db->prepare($query);
        $stmt->execute(["groupId" => $groupId]);
    }

    /**
     * @return int
     */
    public function save() : int
    {
        $fields  = " `name` = " . $this->db->quote($this->name) . " , ";
        $fields .= " `asins` = " . $this->db->quote($this->asins) . " , ";
        $fields .= " `settings` = " . $this->db->quote($this->settings);

        if (isset($this->id)) {
            $query = "UPDATE product_groups SET " . $fields . " WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->execute(["id" => $this->id]);
        } else {
            $query = "INSERT INTO product_groups SET " . $fields;
            $this->db->query($query);
        }
        return $this->db->lastInsertId();
    }
}