<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class FinalizeAdminsIndependenceMigrationTest extends TestCase
{
    public function test_it_drops_legacy_admin_foreign_key_before_making_admin_id_auto_increment(): void
    {
        $migration = file_get_contents(
            __DIR__.'/../../database/migrations/2026_09_16_140000_finalize_admins_independence.php'
        );

        $legacyConstraintPosition = strpos($migration, "'admins' => 'admins_admin_id_foreign'");
        $alterPosition = strpos($migration, 'ALTER TABLE admins MODIFY admin_id BIGINT UNSIGNED AUTO_INCREMENT');

        $this->assertIsInt($legacyConstraintPosition);
        $this->assertIsInt($alterPosition);
        $this->assertLessThan($alterPosition, $legacyConstraintPosition);
        $this->assertStringContainsString('$table->dropForeign($constraint)', $migration);
        $this->assertStringNotContainsString('$table->dropForeign([$constraint])', $migration);
    }
}
