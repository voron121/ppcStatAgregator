<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddSponsoredProductsAdsTable extends AbstractMigration
{
    public function change(): void
    {
        $environment = $this->input->getParameterOption('-e');
        if (in_array($environment, ["production", "development", "testing"])) {
            $this->execute("CREATE TABLE IF NOT EXISTS `sponsored_products_ads` (
                    `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'Ид записи в сервисе',
                    `adId` bigint(20) NOT NULL COMMENT 'Ид объявления в amazon',
                    `campaignId` bigint(20) DEFAULT NULL COMMENT 'Ид кампании в amazon',
                    `portfolioId` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ид портфолио в amazon',
                    `accountId` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ид акканта в amazon',
                    `sku` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'SKU (для селлеров',
                    `asin` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ASIN (для вендеров)',
                    `productName` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Название товара',
                    `status` enum('Enabled','Paused','Archived') COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Статус объявления',
                    PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            // Добавим индексы
            $table = $this->table('sponsored_products_ads');
            $table->addIndex(['adId'], ['unique' => true,'name' => 'adId'])->save();
        }
    }
}
