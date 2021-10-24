<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddErrorCheckColumnsInToAccountsTable extends AbstractMigration
{
    public function change(): void
    {
        $environment = $this->input->getParameterOption('-e');
        if (in_array($environment, ["auth"])) {
            $table = $this->table('accounts');
            $table->addColumn(
                "isError",
                "enum",
                [
                    "values" => ["yes", "no"],
                    "default" => "no",
                    "comment" => "Флаг наличия ошибки для аккаунта",
                    "after" => "email"
                ]
            );
            $table->addColumn(
                'errorText',
                'string',
                [
                    'limit' => 255,
                    'null' => true,
                    "comment" => "Полес с текстом ошибки для аккаунта",
                    'after' => 'isError'
                ]
            );
            $table->save();
        }
    }
}
