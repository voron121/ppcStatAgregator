<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddAccountColumnIntoRobotsLogTable extends AbstractMigration
{
    public function change(): void
    {
        $environment = $this->input->getParameterOption('-e');
        if (in_array($environment, ["robots_log"])) {
            $table = $this->table('robotslog');
            $table->addColumn('account', 'string', ['limit' => 255, "comment" => "Ид аккаунта в амазон"])->save();
        }
    }
}
