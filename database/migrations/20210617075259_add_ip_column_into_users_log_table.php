<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddIpColumnIntoUsersLogTable extends AbstractMigration
{
    public function change(): void
    {
        $environment = $this->input->getParameterOption('-e');
        if (in_array($environment, ["users_log"])) {
            $table = $this->table('userslog');
            $table->addColumn('ip', 'string', ['limit' => 120, "comment" => "IP пользователя"])->save();
        }
    }
}
