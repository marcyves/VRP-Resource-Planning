<?php

namespace App\Support;

use Illuminate\Database\DetectsLostConnections;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Throwable;

class DatabaseAvailability
{
    use DetectsLostConnections;

    public function isAvailable(): bool
    {
        try {
            DB::connection()->getPdo();

            return true;
        } catch (Throwable $e) {
            if ($this->isConnectionError($e)) {
                return false;
            }

            throw $e;
        }
    }

    public function isConnectionError(Throwable $e): bool
    {
        if ($this->causedByLostConnection($e)) {
            return true;
        }

        if ($e instanceof QueryException && $e->getPrevious() instanceof Throwable) {
            return $this->isConnectionError($e->getPrevious());
        }

        return false;
    }
}
