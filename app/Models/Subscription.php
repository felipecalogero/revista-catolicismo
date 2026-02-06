<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'plan_type',
        'status',
        'purchase_date',
        'start_date',
        'end_date',
        'renewal_date',
        'pagbank_transaction_id',
        'pagbank_subscription_id',
        'payment_method',
        'amount',
        'notes',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
        'renewal_date' => 'date',
        'amount' => 'decimal:2',
    ];

    /**
     * Relação com o usuário
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Verifica se a assinatura está ativa
     */
    public function isActive(): bool
    {
        return $this->status === 'active' 
            && $this->end_date 
            && Carbon::now()->lte($this->end_date);
    }

    /**
     * Verifica se a assinatura está pendente
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Verifica se a assinatura está expirada
     */
    public function isExpired(): bool
    {
        return $this->status === 'expired' 
            || ($this->end_date && Carbon::now()->gt($this->end_date));
    }

    /**
     * Verifica se a assinatura está cancelada
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Verifica se a assinatura está suspensa
     */
    public function isPaused(): bool
    {
        return $this->status === 'suspended';
    }

    /**
     * Ativa a assinatura
     */
    public function activate(): void
    {
        $this->update([
            'status' => 'active',
            'start_date' => $this->start_date ?? Carbon::now(),
            'end_date' => $this->end_date ?? Carbon::now()->addYear(),
        ]);
    }

    /**
     * Cancela a assinatura
     */
    public function cancel(): void
    {
        $this->update(['status' => 'cancelled']);
    }

    /**
     * Suspende a assinatura
     */
    public function suspend(): void
    {
        $this->update(['status' => 'suspended']);
    }

    /**
     * Renova a assinatura por mais um ano
     */
    public function renew(): void
    {
        $newEndDate = $this->end_date 
            ? Carbon::parse($this->end_date)->addYear()
            : Carbon::now()->addYear();

        $this->update([
            'status' => 'active',
            'end_date' => $newEndDate,
            'renewal_date' => $newEndDate,
            'purchase_date' => Carbon::now(),
        ]);
    }

    /**
     * Obtém o nome do plano formatado
     */
    public function getPlanNameAttribute(): string
    {
        return $this->plan_type === 'physical' 
            ? 'Assinatura Física Anual' 
            : 'Assinatura Virtual Anual';
    }

    /**
     * Scope para assinaturas ativas
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where('end_date', '>=', Carbon::now());
    }

    /**
     * Scope para assinaturas do usuário
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
