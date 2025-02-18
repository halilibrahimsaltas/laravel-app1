<?php

namespace App\Events;

use App\Models\DataSource;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Throwable;

class FinancialDataProcessingFailed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public DataSource $source;
    public Throwable $error;

    public function __construct(DataSource $source, Throwable $error)
    {
        $this->source = $source;
        $this->error = $error;
    }
} 