<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    private const TABLE = "tarefas";
    private const COLUMN = "user";
    private const FKNAME = "tarefas_user_name";

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable(self::TABLE)) {
            Schema::table(self::TABLE, function (Blueprint $table) {
                $table->bigInteger(self::COLUMN, false, true)->nullable(false);
                $table->foreign(self::COLUMN, self::FKNAME)->on("users")->references("id");
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable(self::TABLE) && Schema::hasColumn(self::TABLE, self::COLUMN)) {
            Schema::table(self::TABLE, function (Blueprint $table) {
                $table->dropForeign(self::FKNAME);
                $table->dropColumn(self::COLUMN);
            });
        }
    }
};
