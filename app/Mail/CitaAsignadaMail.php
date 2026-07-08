<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class CitaAsignadaMail extends Mailable
{
    public function __construct(
        public string $cliente,
        public string $abogado,
        public string $especialidad,
        public string $fecha,
        public string $hora
    ) {
    }

    public function build(): self
    {
        return $this->subject('LexBot AI · Confirmación de cita')
            ->view('emails.cita-asignada');
    }
}
