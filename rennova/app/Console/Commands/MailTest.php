<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class MailTest extends Command
{
    protected $signature = 'mail:test {to?}';
    protected $description = 'Envía un correo de prueba usando la configuración actual de mail';

    public function handle()
    {
        $to = $this->argument('to') ?? config('mail.admin_email', 'admin@example.com');
        $from = config('mail.from.address');
        $mailer = config('mail.default');

        try {
            Mail::raw("Este es un correo de prueba desde Rennova.\nMailer: {$mailer}\nFrom: {$from}", function($message) use ($to) {
                $message->to($to)->subject('Prueba de correo - Rennova');
            });
            $this->info("Correo de prueba enviado a {$to}");
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Error enviando correo: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
