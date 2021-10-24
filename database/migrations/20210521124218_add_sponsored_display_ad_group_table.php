<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddSponsoredDisplayAdGroupTable extends AbstractMigration
{
    public function change(): void
    {
        $environment = $this->input->getParameterOption('-e');
        if (in_array($environment, ["production", "development", "testing"])) {
            $this->execute("CREATE TABLE IF NOT EXISTS `sponsored_display_ad_group` (
                      `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'Ид записи в сервисе',
                      `adGroupId` bigint(20) NOT NULL COMMENT 'Ид группы в amazon',
                      `campaignId` bigint(20) DEFAULT NULL COMMENT 'Ид кампании в amazon',
                      `portfolioId` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ид портфолио в amazon',
                      `accountId` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ид акканта в amazon',
                      `campaignTactic` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Стратегия кампании',
                      `adGroup` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Название группы',
                      `maxBid` bigint(20) DEFAULT NULL COMMENT 'Максимальная ставка умноженная на 1000000',
                      `adGroupStatus` enum('Enabled','Paused','Archived') COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Статус группы',
                      PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            // Добавим индексы
            $table = $this->table('sponsored_display_ad_group');
            $table->addIndex(['adGroupId'], ['unique' => true,'name' => 'adGroupId'])->save();
        }
    }
}
