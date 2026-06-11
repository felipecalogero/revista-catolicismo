<?php

namespace App\Http\Controllers\Internal;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Endpoint HTTP para processar a fila de jobs.
 *
 * Pensado para ser chamado por um cron externo (ex.: cron-job.org) em hospedagens
 * compartilhadas (Hostinger etc.) que não suportam processos longos nem cron CLI.
 * Cada chamada processa todos os jobs disponíveis até esvaziar a fila ou bater
 * o limite de tempo, então retorna.
 */
class QueueTickController extends Controller
{
    public function __invoke(Request $request, string $token): JsonResponse
    {
        $expected = (string) config('app.queue_tick_token');

        if ($expected === '' || ! hash_equals($expected, $token)) {
            Log::warning('queue-tick: token invalido', [
                'ip' => $request->ip(),
                'ua' => substr((string) $request->userAgent(), 0, 200),
            ]);
            abort(403);
        }

        $pendingBefore = (int) DB::table('jobs')->count();

        $exit = Artisan::call('queue:work', [
            '--queue' => 'default',
            '--stop-when-empty' => true,
            '--tries' => 3,
            '--max-time' => 50,
            '--timeout' => 60,
            '--sleep' => 0,
        ]);

        $pendingAfter = (int) DB::table('jobs')->count();

        return response()->json([
            'ok' => $exit === 0,
            'exit_code' => $exit,
            'pending_before' => $pendingBefore,
            'pending_after' => $pendingAfter,
            'processed' => max(0, $pendingBefore - $pendingAfter),
            'at' => now()->toIso8601String(),
        ]);
    }
}
