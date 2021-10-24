<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddSettingsTable extends AbstractMigration
{
    public function change(): void
    {
        $environment = $this->input->getParameterOption('-e');
        if (in_array($environment, ["production", "development", "testing"])) {
            $this->execute("CREATE TABLE `settings` (
                `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Ид параметра',
                `section` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Раздел для которого прикреплен параметр',
                `settings` json DEFAULT NULL COMMENT 'Json объект с параметрами',
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            // Добавим индексы
            $table = $this->table('settings');
            $table->addIndex(['section'], ['unique' => true,'name' => 'section'])->save();
        }
    }
}
