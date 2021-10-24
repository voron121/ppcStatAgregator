<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddSponsoredBrandsCampaignsTable extends AbstractMigration
{
    public function change(): void
    {
        $environment = $this->input->getParameterOption('-e');
        if (in_array($environment, ["production", "development", "testing"])) {
            $this->execute("CREATE TABLE IF NOT EXISTS `sponsored_brands_campaigns` (
                      `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'Ид записи в сервисе',
                      `campaignId` bigint(20) NOT NULL COMMENT 'Ид кампании в amazon',
                      `portfolioId` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ид портфолио в amazon',
                      `accountId` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ид акканта в amazon',
                      `campaign` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Название кампании',
                      `campaignType` enum('Sponsored Brands','Sponsored Brands Draft') COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Тип кампании',
                      `adFormat` enum('Video','Product Collection') COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Тип объявления',
                      `budget` bigint(20) DEFAULT NULL COMMENT 'Дневной бюджет кампании умноженый на 1000000',
                      `budgetType` enum('Daily','Lifetime') COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Тип дневного бюджета',
                      `сampaignStartDate` date DEFAULT NULL COMMENT 'Дата старта кампании',
                      `сampaignEndDate` date DEFAULT NULL COMMENT 'Дата отключения кампании',
                      `landingPageUrl` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ссылка на лендинг',
                      `landingPageAsins` text COLLATE utf8mb4_unicode_ci COMMENT 'ASINs для лендинга (от 3 до 100  разделеные через запятую)',
                      `brandName` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Название бренда',
                      `brandEntityId` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ид бренда',
                      `brandLogoAssetId` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ид логотипа (https://advertising.amazon.com/API/docs/en-us/bulksheets/sb/sb-entities/sb-entity-campaign)',
                      `headline` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Заголовок кампании',
                      `creativeAsins` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ASIN, разделеные запятой, которые будут отображаться в креативе (до 3 для коллекции товаров, 1 для видео)',
                      `mediaId` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ид видео дополнения',
                      `automatedBidding` enum('enabled','disabled') COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Статус автобиддера в amazon',
                      `bidMultiplier` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Множитель ставки для автобиддера (в диапазоне n range от -99.00% до +99.00%)',
                      `campaignStatus` enum('Enabled','Paused','Draft','Archived') COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Статус кампании',
                      PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            // Добавим индексы
            $table = $this->table('sponsored_brands_campaigns');
            $table->addIndex(['campaignId'], ['unique' => true,'name' => 'campaignId'])->save();
        }
    }
}
