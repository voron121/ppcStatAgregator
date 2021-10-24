<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddProductsTable extends AbstractMigration
{
    public function change(): void
    {
        $environment = $this->input->getParameterOption('-e');
        if (in_array($environment, ["production", "development", "testing"])) {
            $this->execute("CREATE TABLE IF NOT EXISTS `sponsored_products` (
                      `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'Ид записи в сервисе',
                      `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Название товара',
                      `asin` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ASIN',
                      `accountId` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ид акканта в amazon',
                      PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            // Добавим индексы
            $table = $this->table('sponsored_products');
            $table->addIndex(['asin'], ['unique' => true,'name' => 'asin'])->save();
        }
    }
}
