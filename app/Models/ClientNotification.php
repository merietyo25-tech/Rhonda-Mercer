<?php

namespace App\Models;

use App\Services\PushService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientNotification extends Model
{
    protected $fillable = ['user_id', 'type', 'icon', 'title', 'body', 'data', 'read_at'];

    protected $casts = [
        'data'     => 'array',
        'read_at'  => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isUnread(): bool
    {
        return is_null($this->read_at);
    }

    public static function notifyUser(User $user, string $type, string $titleKey, string $bodyKey, array $params = [], array $data = []): self
    {
        $locale = $user->locale ?? config('app.locale', 'fr');
        $data['_t'] = ['title' => $titleKey, 'body' => $bodyKey, 'params' => $params];

        return self::forUser($user->id, $type, __($titleKey, $params, $locale), __($bodyKey, $params, $locale), $data);
    }

    /**
     * Les notifications créées à partir de clés de traduction sont retraduites à l'affichage :
     * le client les lit dans la langue qu'il utilise maintenant, et plus dans celle de leur création.
     */
    public function getTitleAttribute($value): string
    {
        return $this->translated('title', (string) $value);
    }

    public function getBodyAttribute($value): string
    {
        return $this->translated('body', (string) $value);
    }

    private function translated(string $part, string $stored): string
    {
        $t = $this->data['_t'] ?? null;
        if (! is_array($t) || empty($t[$part])) {
            return $stored;
        }
        $text = __($t[$part], $t['params'] ?? []);

        return $text === $t[$part] ? $stored : $text;
    }

    public static function forUser(int $userId, string $type, string $title, string $body, array $data = []): self
    {
        $iconMap = [
            'transfer'    => 'paper-plane',
            'credit'      => 'circle-plus',
            'debit'       => 'circle-minus',
            'system'      => 'bell',
        ];

        $notification = self::create([
            'user_id' => $userId,
            'type'    => $type,
            'icon'    => $iconMap[$type] ?? 'bell',
            'title'   => $title,
            'body'    => $body,
            'data'    => $data ?: null,
        ]);

        // Envoyer une push notification si l'utilisateur a des subscriptions
        try {
            $user = $notification->user;
            if ($user && PushSubscription::where('user_id', $userId)->exists()) {
                $url = $data['url'] ?? '/app/notifications';
                (new PushService())->sendToUser($user, $title, $body, $url, $type);
            }
        } catch (\Throwable $e) {
            Log::error('[Push] sendToUser exception for user ' . $userId . ': ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file'      => $e->getFile() . ':' . $e->getLine(),
            ]);
        }

        return $notification;
    }
}
