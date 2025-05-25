<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;               // ← import Model
use Illuminate\Database\Eloquent\Factories\Factory as EloquentFactory;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Disable *all* model events during seeding
        Model::withoutEvents(function () {
            // 1) discover all models:
            $models = collect(File::files(app_path('Models')))
                ->map(fn($file) => 'App\\Models\\' . Str::replaceLast('.php', '', $file->getFilename()));

            // 2) seed each (skip User, we do that manually)
            $models->each(function (string $model) {
                if ($model === User::class) {
                    $this->command->info("Skipped User model (admin below).");
                    return;
                }

                $factoryClass = "Database\\Factories\\" . class_basename($model) . "Factory";
                if (! class_exists($factoryClass)) {
                    $this->command->info("Seeder skipped {$model}: no factory found.");
                    return;
                }

                try {
                    EloquentFactory::factoryForModel($model)
                        ->count(150)
                        ->create();
                    $this->command->info("Seeded 150 × " . class_basename($model));
                } catch (\Throwable $e) {
                    $this->command->error("Error on {$model}: " . $e->getMessage());
                }
            });
        });

        // 3) ensure Admin user (events OK here)
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name'     => 'Admin User',
                'password' => bcrypt('password'),
                'role'     => 'ADMIN',
            ]
        );
        $this->command->info("Ensured Admin user exists.");
    }
}
