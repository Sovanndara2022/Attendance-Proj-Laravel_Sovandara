<?php
// database/migrations/20xx_xx_xx_xxxxxx_add_marked_at_to_attendances_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            if (!Schema::hasColumn('attendances', 'marked_at')) {
                $table->timestamp('marked_at')->nullable()->after('comment');
                $table->index('marked_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            if (Schema::hasColumn('attendances', 'marked_at')) {
                $table->dropIndex(['marked_at']);
                $table->dropColumn('marked_at');
            }
        });
    }
};
