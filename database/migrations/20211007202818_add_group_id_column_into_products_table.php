<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddGroupIdColumnIntoProductsTable extends AbstractMigration
{
    public function change(): void
    {
        $environment = $this->input->getParameterOption('-e');
        if (in_array($environment, ["production", "development", "testing"])) {
            $table = $this->table('sponsored_products');
            $table->addColumn(
                'groupId',
                'biginteger',
                [
                    "after" => "id",
                    'default' => 0,
                    "comment" => "Ид группы с товарами (группа товаров, отличная от группа с товарами Amazon)"
                ]
            )->save();
        }
    }
}
