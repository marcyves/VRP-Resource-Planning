<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->dropUsersCompanyForeignKeyIfExists();

        if (! $this->usersCompanyIdIsNullable()) {
            DB::statement('ALTER TABLE users MODIFY company_id BIGINT UNSIGNED NULL');
        }

        if ($this->usersCompanyForeignKeyName() === null) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreign('company_id')
                    ->references('id')
                    ->on('companies')
                    ->cascadeOnUpdate()
                    ->restrictOnDelete();
            });
        }

        foreach (['admin', 'éditeur', 'rédacteur', 'super admin'] as $name) {
            if (! DB::table('statuses')->where('name', $name)->exists()) {
                DB::table('statuses')->insert(['name' => $name]);
            }
        }
    }

    public function down(): void
    {
        DB::table('statuses')->where('name', 'super admin')->delete();

        $this->dropUsersCompanyForeignKeyIfExists();

        DB::statement('UPDATE users SET company_id = 1 WHERE company_id IS NULL');
        DB::statement('ALTER TABLE users MODIFY company_id BIGINT UNSIGNED NOT NULL');

        if ($this->usersCompanyForeignKeyName() === null) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreign('company_id')
                    ->references('id')
                    ->on('companies')
                    ->cascadeOnUpdate()
                    ->restrictOnDelete();
            });
        }
    }

    private function dropUsersCompanyForeignKeyIfExists(): void
    {
        $foreignKey = $this->usersCompanyForeignKeyName();

        if ($foreignKey === null) {
            return;
        }

        Schema::table('users', function (Blueprint $table) use ($foreignKey) {
            $table->dropForeign($foreignKey);
        });
    }

    private function usersCompanyForeignKeyName(): ?string
    {
        $row = DB::selectOne(
            'SELECT CONSTRAINT_NAME AS name
             FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = ?
               AND TABLE_NAME = ?
               AND COLUMN_NAME = ?
               AND REFERENCED_TABLE_NAME IS NOT NULL
             LIMIT 1',
            [Schema::getConnection()->getDatabaseName(), 'users', 'company_id']
        );

        return $row->name ?? null;
    }

    private function usersCompanyIdIsNullable(): bool
    {
        $row = DB::selectOne(
            'SELECT IS_NULLABLE AS is_nullable
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = ?
               AND TABLE_NAME = ?
               AND COLUMN_NAME = ?
             LIMIT 1',
            [Schema::getConnection()->getDatabaseName(), 'users', 'company_id']
        );

        return ($row->is_nullable ?? 'NO') === 'YES';
    }
};
