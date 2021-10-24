<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class RemoveNotActualColumnsFromAccountsTable extends AbstractMigration
{
    public function change(): void
    {
        $environment = $this->input->getParameterOption('-e');
        if (in_array($environment, ["auth"])) {
            $table = $this->table('accounts');
            $table->removeColumn('name')->save();
        }
    }
}
