<?php

namespace App\Mail;

use App\Models\Mantenimiento;
use App\Models\MantenimientoPurchaseProposal;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MantenimientoOrdenGeneradaMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param array<int, array{absolute_path:string,filename:string}> $attachments
     */
    public function __construct(
        public readonly Mantenimiento $mantenimiento,
        public readonly ?MantenimientoPurchaseProposal $purchaseProposal,
        public readonly array $pdfAttachments = []
    ) {
    }

    public function build(): self
    {
        $mail = $this->subject('Orden de mantenimiento #' . $this->mantenimiento->id_mantenimiento)
            ->view('emails.mantenimiento-orden-generada', [
                'mantenimiento' => $this->mantenimiento,
                'purchaseProposal' => $this->purchaseProposal,
            ]);

        foreach ($this->pdfAttachments as $file) {
            $path = (string) ($file['absolute_path'] ?? '');
            $name = (string) ($file['filename'] ?? basename($path));
            if ($path !== '' && is_file($path)) {
                $mail->attach($path, [
                    'as' => $name,
                    'mime' => 'application/pdf',
                ]);
            }
        }

        return $mail;
    }
}
