<?php

namespace App\Mail;

use App\Models\Barang;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class StokAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public $barang;
    public $statusAlert;
    public $stokSaatIni;

    public function __construct(Barang $barang, string $statusAlert, int $stokSaatIni)
    {
        $this->barang = $barang;
        $this->statusAlert = $statusAlert;
        $this->stokSaatIni = $stokSaatIni;
    }

    public function build()
    {
        $subject = $this->statusAlert === 'habis'
            ? 'Peringatan Stok Habis - Logista'
            : 'Peringatan Stok Menipis - Logista';

        return $this->subject($subject)
            ->view('emails.stok-alert');
    }
}