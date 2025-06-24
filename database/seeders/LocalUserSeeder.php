<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeder for creating a default user in the database.
 * This wil and shall only run in local environments.
 */
class LocalUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (! app()->isLocal()) {
            return;
        }

        $defaultUserPassword = config('app.default_user.password', 'password');
        $defaultUser = User::factory()->create([
            'name' => config('app.default_user.name', 'user'),
            'email' => config('app.default_user.email', 'user@example.com'),
            'password' => bcrypt($defaultUserPassword),
        ]);
        $token = $defaultUser->createToken('api-token');
        $defaultUser->save();

        $consoleOutput = new \Symfony\Component\Console\Output\ConsoleOutput;
        $consoleOutput->getFormatter()->setStyle('yellow', new \Symfony\Component\Console\Formatter\OutputFormatterStyle('yellow', null, ['bold']));
        $consoleOutput->getFormatter()->setStyle('green', new \Symfony\Component\Console\Formatter\OutputFormatterStyle('green', null, ['bold']));
        $consoleOutput->writeln("\tThe application is running in LOCAL mode (<yellow>this is not suitable for production use</yellow>)!");
        $consoleOutput->writeln("\tTest user created: <yellow>'{$defaultUser->name}' (e-mail: '{$defaultUser->email}')</yellow>; password: <green>{$defaultUserPassword}</green>");
        $consoleOutput->writeln("\tAPI Token created for user <yellow>'{$defaultUser->name}'</yellow>; token: <green>{$token->plainTextToken}</green>");

    }
}
