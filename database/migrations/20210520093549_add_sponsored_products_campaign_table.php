<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddSponsoredProductsCampaignTable extends AbstractMigration
{
    public function change(): void
    {
        $environment = $this->input->getParameterOption('-e');
        if (in_array($environment, ["production", "development", "testing"])) {
            $this->execute("CREATE TABLE IF NOT EXISTS `sponsored_products_campaigns` (
                  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'Ид записи в сервисе',
                  `campaignId` bigint(20) NOT NULL COMMENT 'Ид кампании',
                  `portfolioId` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ид портфолио в amazon',
                  `accountId` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ид акканта в amazon',
                  `campaign` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Название кампании',
                  `campaignDailyBudget` bigint(20) DEFAULT NULL COMMENT 'Дневной бюджет кампании умноженный на 1000000',
                  `campaignStartDate` date DEFAULT NULL COMMENT 'Дата начала кампании',
                  `campaignEndDate` date DEFAULT NULL COMMENT 'Дата завершения кампании',
                  `campaignTargetingType` enum('Manual','Auto') COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Тип таргетинга кампании',
                  `campaignStatus` enum('Enabled','Paused','Archived') COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Статус кампании',
                  `biddingStrategy` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Стратегия кампании',
                  PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            // Добавим индексы
            $table = $this->table('sponsored_products_campaigns');
            $table->addIndex(['campaignId'], ['unique' => true,'name' => 'campaignId'])->save();
        }
    }
}
