<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddAccountsTable extends AbstractMigration
{
    public function change(): void
    {
        $environment = $this->input->getParameterOption('-e');
        if ("auth" === $environment) {
            $this->execute("CREATE TABLE IF NOT EXISTS `accounts` (
                  `id` BIGINT(20) NOT NULL AUTO_INCREMENT COMMENT 'Ид записи',
                  `userId` BIGINT(20) NOT NULL COMMENT 'Ид пользователя в сервисе',
                  `accountId` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ид акканта в Amazon',
                  `name` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Имя акканта в Amazon',
                  `email` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Email акканта в Amazon',
                  PRIMARY KEY (`id`)
            ) ENGINE=INNODB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            // Добавим индексы
            $table = $this->table('accounts');
            $table->addIndex(['accountId'], ['unique' => true,'name' => 'accountId'])->save();
        }
    }
}
