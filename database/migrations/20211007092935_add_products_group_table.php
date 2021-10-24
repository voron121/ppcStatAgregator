<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddProductsGroupTable extends AbstractMigration
{
    public function change(): void
    {
        $environment = $this->input->getParameterOption('-e');
        if (in_array($environment, ["production", "development", "testing"])) {
            $this->execute("CREATE TABLE IF NOT EXISTS `product_groups` (
                      `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'Ид записи в сервисе',
                      `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Название группы товара',
                      `asins` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Строка с асинами',
                      `settings` json DEFAULT NULL COMMENT 'Json объект с параметрами для группы',
                      PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        }
    }
}
