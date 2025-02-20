<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Throwable;

class FinancialDataProcessingFailed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $type;
    public Throwable $error;

    public function __construct(string $type, Throwable $error)
    {
        $this->type = $type;
        $this->error = $error;
    }
}