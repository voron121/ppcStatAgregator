<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddPortfolioTable extends AbstractMigration
{
    public function change(): void
    {
        $environment = $this->input->getParameterOption('-e');
        if (in_array($environment, ["production", "development", "testing"])) {
            $this->execute("CREATE TABLE IF NOT EXISTS `portfolios` (
                  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'Ид записи в сервисе',
                  `userId` bigint(20) DEFAULT NULL COMMENT 'Ид пользователя в сервисе',
                  `accountId` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ид аккаунта amazon',
                  `portfolioId` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ид портфолио в amazon',
                  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Имя портфолио',
                  `amount` bigint(20) DEFAULT NULL COMMENT 'Сумма бюджета умноженная на 1000000',
                  `currencyCode` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Строковый код валюты (USD, UAH, RUB)',
                  `policy` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                  `startDate` date DEFAULT NULL,
                  `endDate` date DEFAULT NULL,
                  `inBudget` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                  `state` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                  PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            // Добавим индексы
            $table = $this->table('portfolios');
            $table->addIndex(['portfolioId'], ['unique' => true,'name' => 'portfolioId'])->save();
        }
    }
}
