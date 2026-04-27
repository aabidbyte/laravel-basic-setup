<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Get the migration connection name for Telescope.
     */
    public function getTelescopeConnection(): ?string
    {
        return config('telescope.storage.database.connection');
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ============================================
        // DATABASE TRIGGERS (MySQL only)
        // ============================================

        // Create database triggers to protect user ID 1 (MySQL only)
        if (DB::getDriverName() === 'mysql') {
            // Trigger to prevent deletion of user ID 1
            DB::unprepared('
                CREATE TRIGGER prevent_user_id_1_delete
                BEFORE DELETE ON users
                FOR EACH ROW
                BEGIN
                    IF OLD.id = 1 THEN
                        SIGNAL SQLSTATE "45000" SET MESSAGE_TEXT = "Cannot delete user ID 1 - this user is protected";
                    END IF;
                END
            ');

            // Trigger to prevent direct database updates to user ID 1
            DB::unprepared('
                CREATE TRIGGER prevent_user_id_1_update
                BEFORE UPDATE ON users
                FOR EACH ROW
                BEGIN
                    -- Check if trying to update user ID 1
                    IF OLD.id = 1 THEN
                        -- Allow the update only if it\'s coming from the application
                        -- We check for a session variable that Laravel sets
                        IF @laravel_user_id_1_self_edit IS NULL OR @laravel_user_id_1_self_edit != 1 THEN
                            SIGNAL SQLSTATE "45000"
                            SET MESSAGE_TEXT = "Cannot update user ID 1 - only user ID 1 can edit themselves through the application";
                        END IF;
                    END IF;
                END
            ');

            // Trigger to prevent changing the ID of user ID 1
            DB::unprepared('
                CREATE TRIGGER prevent_user_id_1_id_change
                BEFORE UPDATE ON users
                FOR EACH ROW
                BEGIN
                    -- Prevent changing the ID of user ID 1
                    IF OLD.id = 1 AND NEW.id != 1 THEN
                        SIGNAL SQLSTATE "45000"
                        SET MESSAGE_TEXT = "Cannot change the ID of user ID 1 - this user is protected";
                    END IF;
                END
            ');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the triggers first
        if (DB::getDriverName() === 'mysql') {
            DB::unprepared('DROP TRIGGER IF EXISTS prevent_user_id_1_delete');
            DB::unprepared('DROP TRIGGER IF EXISTS prevent_user_id_1_update');
            DB::unprepared('DROP TRIGGER IF EXISTS prevent_user_id_1_id_change');
        }
    }
};
