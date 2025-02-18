<?php

namespace App\Events;

use App\Models\FinancialData;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FinancialDataProcessed implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public FinancialData $financialData;

    public function __construct(FinancialData $financialData)
    {
        $this->financialData = $financialData;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('financial-data'),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->financialData->id,
            'pair' => "{$this->financialData->base_currency}/{$this->financialData->target_currency}",
            'rate' => $this->financialData->rate,
            'timestamp' => $this->financialData->timestamp
        ];
    }
} 