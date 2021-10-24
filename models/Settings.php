<?php

namespace Models;

// TODO: реализовать как active record (find save сетеры гетеры и тд)
class Settings extends BaseModel
{
    /**
     * Вернет массив с глобальными параметрами для товаров
     * @return array
     */
    public function getGeneralProductSettings() : array
    {
        $stmt = $this->db->query("SELECT settings FROM settings WHERE `section` = 'Products'");
        $settings = $stmt->fetchColumn();
        return $settings ? json_decode($settings, true) : [];
    }

    /**
     * Сохранить глобальные настройки для товаров
     * @param string $settings
     */
    public function setGeneralProductSettings(string $settings) : void
    {
        $query = "INSERT INTO settings
                    SET `settings` = :settings,
                        `section` = 'Products'
                ON DUPLICATE KEY UPDATE
                        `settings` = :settings";
        $stmt = $this->db->prepare($query);
        $stmt->execute(["settings" => $settings]);
    }
}