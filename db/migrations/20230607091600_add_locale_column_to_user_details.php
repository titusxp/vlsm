<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddLocaleColumnToUserDetails extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('user_details');
        $table->addColumn('user_locale', 'string', ['limit' => 10, 'default' => 'fr_FR', 'after' => 'email'])
              ->update();
    }
}
