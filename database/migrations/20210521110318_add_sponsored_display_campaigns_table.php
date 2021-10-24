<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddSponsoredDisplayCampaignsTable extends AbstractMigration
{
    public function change(): void
    {
        $environment = $this->input->getParameterOption('-e');
        if (in_array($environment, ["production", "development", "testing"])) {
            $this->execute("CREATE TABLE IF NOT EXISTS `sponsored_display_campaigns` (
                      `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'Ид записи в сервисе',
                      `campaignId` bigint(20) NOT NULL COMMENT 'Ид кампании в amazon',
                      `portfolioId` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ид портфолио в amazon',
                      `accountId` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ид акканта в amazon',
                      `campaign` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Название кампании',
                      `сampaignDailyBudget` bigint(20) DEFAULT NULL COMMENT 'Днепновй бюджет кампании умноженный на 1000000',
                      `сampaignStartDate` date DEFAULT NULL COMMENT 'Дата старта кампании',
                      `campaignEndDate` date DEFAULT NULL COMMENT 'Дата остановки кампании',
                      `campaignTargetingType` enum('Auto') COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Тип таргетинга',
                      `campaignTactic` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Рекламная стратегия кампании',
                      `campaignStatus` enum('Enabled','Paused','Archived') COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Статус кампании',
                      PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            // Добавим индексы
            $table = $this->table('sponsored_display_campaigns');
            $table->addIndex(['campaignId'], ['unique' => true,'name' => 'campaignId'])->save();
        }
    }
}
