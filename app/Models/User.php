<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Notifications\CustomResetPasswordNotification;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'cpf',
        'address',
        'address_number',
        'complement',
        'neighborhood',
        'city',
        'state',
        'zip_code',
        'phone',
        'profession',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Override o envio do e-mail de redefinição de senha para usar nosso template em português.
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new CustomResetPasswordNotification($token));
    }

    /**
     * Override o envio do e-mail de verificação para usar nosso template em português.
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new \App\Notifications\CustomVerifyEmailNotification());
    }

    /**
     * Verifica se o usuário é administrador
     */
    public function getFormattedCpfAttribute()
    {
        if (!$this->cpf) return '';
        $cpf = preg_replace('/[^0-9]/', '', $this->cpf);
        $len = strlen($cpf);
        
        if ($len === 11) {
            return sprintf('%s.%s.%s-%s',
                substr($cpf, 0, 3),
                substr($cpf, 3, 3),
                substr($cpf, 6, 3),
                substr($cpf, 9, 2)
            );
        } elseif ($len === 14) {
            return sprintf('%s.%s.%s/%s-%s',
                substr($cpf, 0, 2),
                substr($cpf, 2, 3),
                substr($cpf, 5, 3),
                substr($cpf, 8, 4),
                substr($cpf, 12, 2)
            );
        }
        
        return $this->cpf;
    }

    public function getFormattedPhoneAttribute()
    {
        if (!$this->phone) return '';
        $phone = preg_replace('/[^0-9]/', '', $this->phone);
        $len = strlen($phone);
        
        if ($len === 11) {
            return sprintf('(%s) %s-%s',
                substr($phone, 0, 2),
                substr($phone, 2, 5),
                substr($phone, 7, 4)
            );
        } elseif ($len === 10) {
            return sprintf('(%s) %s-%s',
                substr($phone, 0, 2),
                substr($phone, 2, 4),
                substr($phone, 6, 4)
            );
        }
        
        return $this->phone;
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Verifica se o usuário é um usuário comum
     */
    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    /**
     * Relação com assinaturas
     */
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Obtém a assinatura ativa do usuário
     */
    public function activeSubscription()
    {
        return $this->subscriptions()
            ->where('status', 'active')
            ->where('end_date', '>=', now())
            ->latest()
            ->first();
    }

    /**
     * Verifica se o usuário tem assinatura ativa
     */
    public function hasActiveSubscription(): bool
    {
        $activeSubscription = $this->activeSubscription();
        return $activeSubscription !== null;
    }

    /**
     * Verifica se o usuário pode acessar edições
     */
    public function canAccessEditions(): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return $this->hasActiveSubscription();
    }

    /**
     * Verifica se o usuário pode acessar um artigo específico
     * Assinantes têm acesso a todos os artigos
     * Não-assinantes só têm acesso a artigos publicados há mais de 5 meses
     */
    public function canAccessArticle(\App\Models\Article $article): bool
    {
        // Administradores sempre têm acesso
        if ($this->isAdmin()) {
            return true;
        }

        // Assinantes têm acesso a todos os artigos
        if ($this->hasActiveSubscription()) {
            return true;
        }

        // Não-assinantes só têm acesso a artigos publicados há mais de 5 meses
        return $article->canBeAccessedByNonSubscribers();
    }

    /**
     * Verifica se o usuário pode acessar uma edição específica
     * Assinantes têm acesso a todas as edições
     * Não-assinantes só têm acesso a edições publicadas há mais de 5 meses
     */
    public function canAccessEdition(\App\Models\Edition $edition): bool
    {
        // Administradores sempre têm acesso
        if ($this->isAdmin()) {
            return true;
        }

        // Assinantes têm acesso a todas as edições
        if ($this->hasActiveSubscription()) {
            return true;
        }

        // Não-assinantes só têm acesso a edições publicadas há mais de 5 meses
        return $edition->canBeAccessedByNonSubscribers();
    }

    /**
     * Verifica se o usuário tem senha definida
     */
    public function hasPassword(): bool
    {
        return !empty($this->password);
    }
}
