<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddSponsoredProductsKeywordsTable extends AbstractMigration
{
    public function change(): void
    {
        $environment = $this->input->getParameterOption('-e');
        if (in_array($environment, ["production", "development", "testing"])) {
            $this->execute("CREATE TABLE IF NOT EXISTS `sponsored_products_keywords` (
                      `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'Ид записи в сервисе',
                      `keywordId` bigint(20) NOT NULL COMMENT 'Ид ключевого слова в amazon',
                      `campaignId` bigint(20) DEFAULT NULL COMMENT 'Ид кампании в amazon',
                      `portfolioId` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ид портфолио в amazon',
                      `accountId` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ид акканта в amazon',
                      `maxBid` bigint(20) DEFAULT NULL COMMENT 'Ставка умноженная на 1000000',
                      `keyword` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ключевое слово',
                      `matchType` enum('Phrase','Exact','Broad','Campaign Negative Exact','Campaign Negative Phrase','Negative Exact','Negative Phrase') COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Тип',
                      `status` enum('Enabled','Paused','Archived','deleted') COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Статус ключевого слова',
                      PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            // Добавим индексы
            $table = $this->table('sponsored_products_keywords');
            $table->addIndex(['keywordId'], ['unique' => true,'name' => 'keywordId'])->save();
        }
    }
}
