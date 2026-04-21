<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('business_profiles', function (Blueprint $table): void {
            $table->string('team_invite_code', 32)->nullable()->unique()->after('setup_completed_at');
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->foreignId('business_profile_id')->nullable()->after('email_verified_at')->constrained('business_profiles')->nullOnDelete();
        });

        $profiles = DB::table('business_profiles')
            ->select('id', 'user_id')
            ->orderBy('id')
            ->get();

        foreach ($profiles as $profile) {
            DB::table('business_profiles')
                ->where('id', $profile->id)
                ->update([
                    'team_invite_code' => $this->generateUniqueInviteCode(),
                ]);

            DB::table('users')
                ->where('id', $profile->user_id)
                ->update([
                    'business_profile_id' => $profile->id,
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('business_profile_id');
        });

        Schema::table('business_profiles', function (Blueprint $table): void {
            $table->dropUnique('business_profiles_team_invite_code_unique');
            $table->dropColumn('team_invite_code');
        });
    }

    protected function generateUniqueInviteCode(): string
    {
        do {
            $code = 'TEAM-'.Str::upper(Str::random(8));
        } while (DB::table('business_profiles')->where('team_invite_code', $code)->exists());

        return $code;
    }
};
