<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ImportLocInfo extends Mailable
{
    use Queueable, SerializesModels;

    public $filename;
    public $totalRows;
    public $successCount;
    public $updatedCount;
    public $skippedCount;
    public $failedCount;
    public $failedRows;
    public $error_msg;

    public function __construct(
        $filename = '-',
        $totalRows = 0,
        $successCount = 0,
        $updatedCount = 0,
        $skippedCount = 0,
        $failedCount = 0,
        $failedRows = null,
        $error_msg = null
    ) {
        $this->filename = $filename;
        $this->totalRows = (int) $totalRows;
        $this->successCount = (int) $successCount;
        $this->updatedCount = (int) $updatedCount;
        $this->skippedCount = (int) $skippedCount;
        $this->failedCount = (int) $failedCount;
        $this->failedRows = $failedRows;
        $this->error_msg = $error_msg;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        if ($this->error_msg && $this->totalRows === 0) {
            $subject = "[ERROR] Gagal Memproses File Import LOC: {$this->filename}";
        } elseif ($this->failedCount > 0 && ($this->successCount > 0 || $this->skippedCount > 0)) {
            $subject = "[PERINGATAN] Import LOC Sebagian Gagal ({$this->failedCount} Baris Error) - {$this->filename}";
        } else {
            $subject = "[GAGAL] Seluruh Baris Import LOC Gagal Diproses ({$this->failedCount} Baris) - {$this->filename}";
        }

        return $this->subject($subject)->view('emails.loc-info');
    }
}
