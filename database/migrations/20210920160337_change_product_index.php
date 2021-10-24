<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class ChangeProductIndex extends AbstractMigration
{
    public function change(): void
    {
        $environment = $this->input->getParameterOption('-e');
        if (in_array($environment, ["production", "development", "testing"])) {
            $this->table('sponsored_products')->removeIndexByName('asin')->save();
            $this->table('sponsored_products')->addColumn(
                'sku',
                'string',
                [
                    'limit' => 20,
                    'null' => false,
                    "comment" => "SKU товара в amazon",
                    'after' => 'productSynonym'
                ]
            )->save();
            $this->table('sponsored_products')->addIndex(['asin', 'sku'], ['unique' => true,'name' => 'asinTosku'])->save();
        }
    }
}
