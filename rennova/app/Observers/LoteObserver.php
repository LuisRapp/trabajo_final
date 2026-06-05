<?php

namespace App\Observers;

use App\Jobs\ProcessAllocationProposal;
use App\Models\Lote;
use Illuminate\Support\Facades\DB;

class LoteObserver
{
    /**
     * Handle the Lote "saved" event.
     */
    public function saved(Lote $lote): void
    {
        if ($lote->wasRecentlyCreated || $lote->wasChanged(['estado', 'especie', 'superficie', 'main_task_type'])) {
            DB::afterCommit(function () use ($lote) {
                ProcessAllocationProposal::dispatch($lote->id_lote);
            });
        }
    }

    /**
     * Handle the Lote "created" event.
     */
    public function created(Lote $lote): void
    {
        //
    }

    /**
     * Handle the Lote "updated" event.
     */
    public function updated(Lote $lote): void
    {
        //
    }

    /**
     * Handle the Lote "deleted" event.
     */
    public function deleted(Lote $lote): void
    {
        //
    }

    /**
     * Handle the Lote "restored" event.
     */
    public function restored(Lote $lote): void
    {
        //
    }

    /**
     * Handle the Lote "force deleted" event.
     */
    public function forceDeleted(Lote $lote): void
    {
        //
    }
}
