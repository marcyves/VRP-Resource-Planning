<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id')->nullable()->after('id');
        });

        $defaultCompanyId = DB::table('companies')->orderBy('id')->value('id');

        foreach (DB::table('programs')->orderBy('id')->get() as $program) {
            $companyIds = DB::table('courses')
                ->join('schools', 'schools.id', '=', 'courses.school_id')
                ->where('courses.program_id', $program->id)
                ->distinct()
                ->orderBy('schools.company_id')
                ->pluck('schools.company_id');

            if ($companyIds->isEmpty()) {
                if ($defaultCompanyId !== null) {
                    DB::table('programs')->where('id', $program->id)->update([
                        'company_id' => $defaultCompanyId,
                    ]);
                }

                continue;
            }

            $primaryCompanyId = $companyIds->first();
            DB::table('programs')->where('id', $program->id)->update([
                'company_id' => $primaryCompanyId,
            ]);

            foreach ($companyIds->slice(1) as $companyId) {
                $newProgramId = DB::table('programs')->insertGetId([
                    'name' => $program->name,
                    'short_description' => $program->short_description,
                    'company_id' => $companyId,
                ]);

                $courseIds = DB::table('courses')
                    ->join('schools', 'schools.id', '=', 'courses.school_id')
                    ->where('courses.program_id', $program->id)
                    ->where('schools.company_id', $companyId)
                    ->pluck('courses.id');

                if ($courseIds->isNotEmpty()) {
                    DB::table('courses')->whereIn('id', $courseIds)->update([
                        'program_id' => $newProgramId,
                    ]);
                }
            }
        }

        if ($this->programsCompanyIdIsNullable()) {
            DB::statement('ALTER TABLE programs MODIFY company_id BIGINT UNSIGNED NOT NULL');
        }

        if ($this->programsCompanyForeignKeyName() === null) {
            Schema::table('programs', function (Blueprint $table) {
                $table->foreign('company_id')
                    ->references('id')
                    ->on('companies')
                    ->cascadeOnUpdate()
                    ->restrictOnDelete();
            });
        }
    }

    public function down(): void
    {
        $foreignKey = $this->programsCompanyForeignKeyName();

        if ($foreignKey !== null) {
            Schema::table('programs', function (Blueprint $table) use ($foreignKey) {
                $table->dropForeign($foreignKey);
            });
        }

        Schema::table('programs', function (Blueprint $table) {
            $table->dropColumn('company_id');
        });
    }

    private function programsCompanyForeignKeyName(): ?string
    {
        $row = DB::selectOne(
            'SELECT CONSTRAINT_NAME AS name
             FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = ?
               AND TABLE_NAME = ?
               AND COLUMN_NAME = ?
               AND REFERENCED_TABLE_NAME IS NOT NULL
             LIMIT 1',
            [Schema::getConnection()->getDatabaseName(), 'programs', 'company_id']
        );

        return $row->name ?? null;
    }

    private function programsCompanyIdIsNullable(): bool
    {
        $row = DB::selectOne(
            'SELECT IS_NULLABLE AS is_nullable
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = ?
               AND TABLE_NAME = ?
               AND COLUMN_NAME = ?
             LIMIT 1',
            [Schema::getConnection()->getDatabaseName(), 'programs', 'company_id']
        );

        return ($row->is_nullable ?? 'NO') === 'YES';
    }
};
