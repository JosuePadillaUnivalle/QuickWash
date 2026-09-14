<?php
namespace App\Console\Commands;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
class CreateStaff extends Command
{
    protected $signature = 'quickwash:personal {name?} {email?} {password?}';
    protected $description = 'Crear una cuenta de personal de Quick Wash';
    public function handle(): int
    {
        $data = [
            'name' => $this->argument('name') ?: $this->ask('Nombre completo'),
            'email' => mb_strtolower(trim((string) ($this->argument('email') ?: $this->ask('Correo electrónico')))),
            'password' => $this->argument('password') ?: $this->secret('Contraseña (mínimo 8 caracteres)'),
        ];
        $validator = Validator::make($data, ['name' => 'required|string|max:100', 'email' => 'required|email|max:200|unique:users', 'password' => 'required|string|min:8']);
        if ($validator->fails()) { foreach ($validator->errors()->all() as $error) $this->error($error); return self::FAILURE; }
        $user = new User($data);
        $user->role = 'personal';
        $user->save();
        $this->info('Cuenta de personal creada.');
        return self::SUCCESS;
    }
}
