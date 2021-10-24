<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddProductSettingsColumn extends AbstractMigration
{
    public function change(): void
    {
        $environment = $this->input->getParameterOption('-e');
        if (in_array($environment, ["production", "development", "testing"])) {
            $table = $this->table('sponsored_products');
            $table->addColumn(
                'productSynonym',
                'string',
                [
                    'limit' => 255,
                    'null' => true,
                    "comment" => "Псевдоним товара. Специалист может указать любой псевдоним для товара. Имеет значение только во вьюхах",
                    'after' => 'name'
                ]
            );
            $table->addColumn(
                'settings',
                'json',
                [
                    'null' => true,
                    "comment" => "Объект json с настройками для товара",
                    'after' => 'accountId'
                ]
            );
            $table->save();
        }
    }
}
