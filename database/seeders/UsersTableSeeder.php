<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Setting;
use App\Models\User;
use App\Space\InstallUtils;
use Illuminate\Database\Seeder;
use Vinkla\Hashids\Facades\Hashids;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::create([
            'email' => 'admin@invoiceshelf.com',
            'name' => 'Jane Doe',
            'role' => 'super admin',
            'password' => 'invoiceshelf@123',
        ]);

        $company = Company::create([
            'name' => 'xyz',
            'owner_id' => $user->id,
            'slug' => 'xyz',
        ]);

        $company->unique_hash = Hashids::connection(Company::class)->encode($company->id);
        $company->save();
        $company->setupDefaultData();
        $user->companies()->attach($company->id);
        setPermissionsTeamId($company->id);
        $user->assignRole('super admin');
        setPermissionsTeamId(null);

        Setting::setSetting('profile_complete', 0);
        // Set version.
        InstallUtils::setCurrentVersion();
    }
}
