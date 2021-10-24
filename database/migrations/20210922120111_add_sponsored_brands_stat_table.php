<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddSponsoredBrandsStatTable extends AbstractMigration
{
    public function change(): void
    {
        $environment = $this->input->getParameterOption('-e');
        if (in_array($environment, ["production", "development", "testing"])) {
            $this->execute("CREATE TABLE IF NOT EXISTS `sponsored_brands_ads_stat` (
                      `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'Ид записи',
                      `impressions` int(11) DEFAULT NULL COMMENT 'Количество показов',
                      `clicks` int(11) DEFAULT NULL COMMENT 'Количество кликов',
                      `spend` bigint(20) DEFAULT NULL COMMENT 'Затраты на рекламу умноженные на 1000000',
                      `sale` bigint(20) DEFAULT NULL COMMENT 'Сумма продаж товара за день умноженная на 1000000',
                      `cpc` bigint(20) DEFAULT NULL COMMENT 'Cost per click (стоимость клика) умноженная на 1000000',
                      `ctr` bigint(20) DEFAULT NULL COMMENT 'Click-Thru Rate (переходный рейтинг) умноженный на 1000000',
                      `acos` bigint(20) DEFAULT NULL COMMENT 'Total Advertising Cost of Sales (общая стоимость продажи реклам) умноженная на 1000000',
                      `roas` bigint(20) DEFAULT NULL COMMENT 'Total Return on Advertising Spend (общая рентабельность рекламы) умноженная на 1000000',
                      `sku` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'SKU товара',
                      `asin` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ASIN товара',
                      `date` date DEFAULT NULL COMMENT 'Дата актуальности данных (дата для который были получены отчеты)',
                      PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            // Добавим индексы
            $table = $this->table('sponsored_brands_ads_stat');
            $table->addIndex(['asin', 'sku', 'date'], ['unique' => true,'name' => 'productKey'])->save();
        }
    }
}
