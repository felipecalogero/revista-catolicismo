<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Services\PagBankService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SubscriptionController extends Controller
{
    /**
     * Exibe os planos de assinatura disponíveis
     */
    public function plans()
    {
        $plans = [
            'physical' => [
                'name' => 'Assinatura Física Anual',
                'description' => 'Acesso digital completo + revista física mensal',
                'amount' => config('subscriptions.physical_price', 299.90),
            ],
            'virtual' => [
                'name' => 'Assinatura Virtual Anual',
                'description' => 'Acesso digital completo a todas as edições',
                'amount' => config('subscriptions.virtual_price', 199.90),
            ],
        ];

        return view('subscriptions.plans', compact('plans'));
    }

    /**
     * Cria uma nova assinatura e redireciona para o PagBank
     */
    public function create(Request $request)
    {
        $request->validate([
            'plan_type' => 'required|in:physical,virtual',
        ]);

        $user = Auth::user();
        $planType = $request->plan_type;

        // Verifica se o usuário já tem uma assinatura ativa
        if ($user->hasActiveSubscription()) {
            return redirect()->route('subscriptions.show')
                ->with('info', 'Você já possui uma assinatura ativa.');
        }

        // Cria uma assinatura pendente
        $subscription = Subscription::create([
            'user_id' => $user->id,
            'plan_type' => $planType,
            'status' => 'pending',
            'purchase_date' => Carbon::now(),
            'amount' => $planType === 'physical' 
                ? config('subscriptions.physical_price', 299.90)
                : config('subscriptions.virtual_price', 199.90),
        ]);

        // Usa API completa do PagBank (com webhooks)
        return $this->createCheckoutViaApi($subscription, $user, $planType);
    }

    /**
     * Exibe a assinatura do usuário logado
     */
    public function show()
    {
        $user = Auth::user();
        $subscription = $user->subscriptions()->latest()->first();

        return view('subscriptions.show', compact('subscription'));
    }

    /**
     * Ativa uma assinatura (após confirmação de pagamento)
     */
    public function activate(Request $request)
    {
        $user = Auth::user();
        $subscription = $user->subscriptions()
            ->where('status', 'suspended')
            ->latest()
            ->first();

        if (!$subscription) {
            return redirect()->route('subscriptions.show')
                ->with('error', 'Nenhuma assinatura suspensa encontrada.');
        }

        $subscription->activate();

        return redirect()->route('subscriptions.show')
            ->with('success', 'Assinatura reativada com sucesso!');
    }

    /**
     * Suspende uma assinatura
     */
    public function suspend(Request $request)
    {
        $user = Auth::user();
        $subscription = $user->activeSubscription();

        if (!$subscription) {
            return redirect()->route('subscriptions.show')
                ->with('error', 'Nenhuma assinatura ativa encontrada.');
        }

        $subscription->suspend();

        return redirect()->route('subscriptions.show')
            ->with('success', 'Assinatura suspensa com sucesso.');
    }

    /**
     * Cancela uma assinatura
     */
    public function cancel(Request $request)
    {
        $user = Auth::user();
        $subscription = $user->subscriptions()
            ->whereIn('status', ['active', 'suspended'])
            ->latest()
            ->first();

        if (!$subscription) {
            return redirect()->route('subscriptions.show')
                ->with('error', 'Nenhuma assinatura ativa encontrada.');
        }

        $subscription->cancel();

        return redirect()->route('subscriptions.show')
            ->with('success', 'Assinatura cancelada com sucesso.');
    }

    /**
     * Página de retorno do PagBank após pagamento
     */
    public function returnFromPagBank(Request $request)
    {
        // Tenta pegar o ID da assinatura da URL ou sessão
        $subscriptionId = $request->get('subscription_id') 
            ?? $request->get('ref') 
            ?? $request->session()->get('last_subscription_id');

        if (!$subscriptionId) {
            return redirect()->route('subscriptions.plans')
                ->with('error', 'Não foi possível identificar a assinatura.');
        }

        $subscription = Subscription::find($subscriptionId);
        $user = Auth::user();

        if (!$subscription) {
            return redirect()->route('subscriptions.plans')
                ->with('error', 'Assinatura não encontrada.');
        }

        // Verifica se a assinatura pertence ao usuário
        if ($subscription->user_id !== $user->id) {
            return redirect()->route('subscriptions.show')
                ->with('error', 'Acesso negado.');
        }

        // Se já estiver ativa, redireciona
        if ($subscription->status === 'active') {
            return redirect()->route('subscriptions.show')
                ->with('success', 'Sua assinatura está ativa!');
        }

        // Se ainda estiver pendente, mostra mensagem
        return redirect()->route('subscriptions.show')
            ->with('info', 'Pagamento em processamento. Verifique o status da sua assinatura no painel do PagBank ou aguarde a confirmação.');
    }

    /**
     * Cria checkout via API completa do PagBank
     */
    private function createCheckoutViaApi($subscription, $user, string $planType)
    {
        $amount = $planType === 'physical'
            ? config('subscriptions.physical_price', 299.90)
            : config('subscriptions.virtual_price', 199.90);

        $planName = $planType === 'physical'
            ? 'Assinatura Física Anual'
            : 'Assinatura Virtual Anual';

        $pagbankService = new PagBankService();
        $checkout = $pagbankService->createSimpleCheckout(
            $subscription->id,
            $amount,
            $user->name,
            $user->email,
            $planName
        );

        if (!$checkout) {
            \Log::error('PagBank API: Failed to create checkout', [
                'subscription_id' => $subscription->id,
            ]);
            return redirect()->route('subscriptions.plans')
                ->with('error', 'Erro ao criar checkout. Tente novamente ou entre em contato com o suporte.');
        }

        // Salva o ID do checkout na assinatura
        $subscription->update([
            'pagbank_transaction_id' => $checkout['id'] ?? null,
        ]);

        // Obtém a URL de pagamento
        // A API do PagBank pode retornar a URL em diferentes formatos:
        // 1. payment_url (direto)
        // 2. links array com rel="PAY" ou similar
        // 3. links array com href (mas pode ser URL da API, não de pagamento)
        
        $paymentUrl = null;
        
        if (isset($checkout['payment_url'])) {
            $paymentUrl = $checkout['payment_url'];
        } elseif (isset($checkout['links']) && is_array($checkout['links'])) {
            // Procura por link com rel="PAY" ou similar
            foreach ($checkout['links'] as $link) {
                if (isset($link['rel']) && strtoupper($link['rel']) === 'PAY') {
                    $paymentUrl = $link['href'] ?? null;
                    break;
                }
            }
            
            // Se não encontrou, tenta o primeiro link que não seja da API
            if (!$paymentUrl && isset($checkout['links'][0]['href'])) {
                $href = $checkout['links'][0]['href'];
                // Se não for URL da API (não contém /checkouts/), usa
                if (strpos($href, '/checkouts/') === false) {
                    $paymentUrl = $href;
                }
            }
        }

        if (!$paymentUrl) {
            \Log::error('PagBank API: No payment URL in response', [
                'checkout_id' => $checkout['id'] ?? null,
            ]);
            return redirect()->route('subscriptions.plans')
                ->with('error', 'Erro ao obter URL de pagamento. Tente novamente.');
        }


        // Redireciona para a página de pagamento do PagBank
        return response()->view('subscriptions.redirect', [
            'pagbank_url' => $paymentUrl,
            'subscription_id' => $subscription->id
        ]);
    }

}
