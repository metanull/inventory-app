<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'test_user',
            'email' => 'test@example.com',
            'password' => bcrypt(Str::random(12)),
        ]);

        $user = User::create([
            'name' => 'metanull',
            'email' => 'havelangep@hotmail.com',
            'password' => bcrypt('password'),
        ]);
        $token = $user->createToken('api-token');
        $user->save();

        $consoleOutput = new \Symfony\Component\Console\Output\ConsoleOutput();
        $consoleOutput->getFormatter()->setStyle('yellow', new \Symfony\Component\Console\Formatter\OutputFormatterStyle('yellow', null, array('bold')));
        $consoleOutput->getFormatter()->setStyle('green', new \Symfony\Component\Console\Formatter\OutputFormatterStyle('green', null, array('bold')));
        $consoleOutput->writeln("\tAPI Token created for user <yellow>{$user->name}</yellow>: <green>{$token->plainTextToken}</green>");

    }
}
