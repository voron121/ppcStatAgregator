<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateUsersLogTable extends AbstractMigration
{
    public function change(): void
    {
        $environment = $this->input->getParameterOption('-e');
        if ("users_log" === $environment) {
            $this->execute("CREATE TABLE IF NOT EXISTS `phinxlog` (
                  `version` bigint(20) NOT NULL,
                  `migration_name` varchar(100) DEFAULT NULL,
                  `start_time` timestamp NULL DEFAULT NULL,
                  `end_time` timestamp NULL DEFAULT NULL,
                  `breakpoint` tinyint(1) NOT NULL DEFAULT '0',
                  PRIMARY KEY (`version`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8");

            $this->execute("CREATE TABLE IF NOT EXISTS `userslog` (
                  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'Ид записи',
                  `userId` bigint(20) DEFAULT NULL COMMENT 'Ид пользователя в системме',
                  `timestamp` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Дата и время события',
                  `level` enum('emergency','alert','critical','error','warning','notice','info','debug') COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Уровень события согласно PSR-3',
                  `message` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Сообщение события',
                  `context` json DEFAULT NULL COMMENT 'Контекст согласно PSR-3',
                  PRIMARY KEY (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        }
    }
}
