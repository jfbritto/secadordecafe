<?php

namespace App\Http\Controllers;

use App\Services\Asaas\AsaasWebhookHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AsaasWebhookController extends Controller
{
    public function __invoke(Request $request, AsaasWebhookHandler $handler): JsonResponse
    {
        $token = (string) config('asaas.webhook_token');
        $headerToken = (string) $request->header('asaas-access-token');

        if ($token === '' || ! hash_equals($token, $headerToken)) {
            return response()->json(['error' => 'unauthorized'], 401);
        }

        $payload = $request->all();
        if (empty($payload)) {
            return response()->json(['error' => 'empty payload'], 400);
        }

        $handler->handle($payload);

        return response()->json(['ok' => true]);
    }
}
