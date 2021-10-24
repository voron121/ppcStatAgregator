<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateUsersTable extends AbstractMigration
{
    public function change () : void
    {
        $environment = $this->input->getParameterOption('-e');
        if ("auth" === $environment) {
            $this->execute("CREATE TABLE IF NOT EXISTS `phinxlog` (
                  `version` bigint(20) NOT NULL,
                  `migration_name` varchar(100) DEFAULT NULL,
                  `start_time` timestamp NULL DEFAULT NULL,
                  `end_time` timestamp NULL DEFAULT NULL,
                  `breakpoint` tinyint(1) NOT NULL DEFAULT '0',
                  PRIMARY KEY (`version`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8");
            $this->execute("CREATE TABLE IF NOT EXISTS `users` (
                  `userId` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'Ид пользователя в системе',
                  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Email пользователя',
                  `login` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Логин пользователя',
                  `password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Пароль пользователя',
                  `userGroupId` tinyint(255) DEFAULT NULL COMMENT 'Группа пользователя (уровень доступа)',
                  `registrationDate` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Дата регистрации пользователя',
                  `lastActivity` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Дата последней автороизации в сервисе',
                  `status` enum('NotAprove','Aprove') COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Статус пользователя',
                  PRIMARY KEY (`userId`)
            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        }
    }
}
