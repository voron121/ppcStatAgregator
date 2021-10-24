<?php


namespace Models\cabinet;

use Models\BaseModel;
use PDO;

class Product extends BaseModel
{
    protected $id;
    protected $groupId;
    protected $name;
    protected $productSynonym;
    protected $asin;
    protected $accountId;
    protected $settings;

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
     * @param int $groupId
     */
    public function setGroupId(int $groupId) : void
    {
        $this->groupId = $groupId;
    }

    /**
     * @return int
     */
    public function getGroupId() : int
    {
        return !is_null($this->groupId) ? $this->groupId : 0;
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
        return $this->name;
    }

    /**
     * @param string $productSynonym
     */
    public function setProductSynonym(string $productSynonym) : void
    {
        $this->productSynonym = $productSynonym;
    }

    /**
     * @return string
     */
    public function getProductSynonym() : string
    {
        return !is_null($this->productSynonym) ? $this->productSynonym : "";
    }

    /**
     * @param string $asin
     */
    public function setAsin(string $asin) : void
    {
        $this->asin = $asin;
    }

    /**
     * @return string
     */
    public function getAsin() : string
    {
        return $this->asin;
    }

    /**
     * @param string $accountId
     */
    public function setAccountId(string $accountId) : void
    {
        $this->accountId = $accountId;
    }

    /**
     * @return string
     */
    public function getAccountId() : string
    {
        return $this->accountId;
    }

    /**
     * @param string $settings
     */
    public function setSettings(string $settings) : void
    {
        $this->settings = $settings;
    }

    /**
     * @return array
     */
    public function getSettings() : array
    {
        return !is_null($this->settings) ? json_decode($this->settings, true) : [];
    }

    /**
     * @param int $productId
     * @return array
     */
    public function getSponsoredProductById(int $productId) : Product
    {
        $query = "SELECT id,
                         groupId,
                         name,
                         asin,
                         accountId,
                         productSynonym,
                         settings
                    FROM sponsored_products
                    WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->execute(["id" => $productId]);
        $stmt->setFetchMode(PDO::FETCH_CLASS, 'Models\Cabinet\Product');
        return $stmt->fetch();
    }

    /**
     * @return int
     */
    public function save() : int
    {
        $fields  = " `groupId` = " . $this->db->quote($this->groupId) . " , ";
        $fields .= " `name` = " . $this->db->quote($this->name) . " , ";
        $fields .= " `productSynonym` = " . $this->db->quote($this->productSynonym) . " , ";
        $fields .= " `asin` = " . $this->db->quote($this->asin) . " , ";
        $fields .= " `accountId` = " . $this->db->quote($this->accountId) . " , ";
        $fields .= " `settings` = " . $this->db->quote($this->settings);
        if (isset($this->id)) {
            $query = "UPDATE sponsored_products SET " . $fields . " WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->execute(["id" => $this->id]);
        } else {
            $query = "INSERT INTO sponsored_products SET " . $fields;
            $this->db->query($query);
        }
        return $this->db->lastInsertId();
    }

}