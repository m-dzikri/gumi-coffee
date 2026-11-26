<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class Order extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_PREPARING = 'preparing';

    public const STATUS_READY = 'ready';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    public const METHOD_CASH = 'cash';

    public const METHOD_QRIS_DUMMY = 'qris_dummy';

    public const PAYMENT_UNPAID = 'unpaid';

    public const PAYMENT_PAID = 'paid';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'order_code',
        'user_id',
        'total',
        'status',
        'payment_method',
        'payment_status',
        'paid_at',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'total' => 'integer',
            'paid_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    public function payQrisByCustomer(): self
    {
        return DB::transaction(function (): self {
            $locked = self::whereKey($this->id)->lockForUpdate()->firstOrFail();

            if (
                $locked->payment_method !== self::METHOD_QRIS_DUMMY
                || $locked->status !== self::STATUS_PENDING
                || $locked->payment_status !== self::PAYMENT_UNPAID
            ) {
                throw ValidationException::withMessages([
                    'payment_status' => 'Pembayaran simulasi QRIS hanya dapat dilakukan untuk pesanan QRIS yang masih berstatus menunggu (pending) dan belum dibayar.',
                ]);
            }

            $locked->payment_status = self::PAYMENT_PAID;
            $locked->paid_at = now();
            $locked->save();

            $this->setRawAttributes($locked->getRawOriginal(), true);

            return $this;
        });
    }

    public function cancelByCustomer(): self
    {
        return DB::transaction(function (): self {
            $locked = self::whereKey($this->id)->lockForUpdate()->firstOrFail();

            if ($locked->status !== self::STATUS_PENDING || $locked->payment_status !== self::PAYMENT_UNPAID) {
                throw ValidationException::withMessages([
                    'status' => 'Pesanan hanya dapat dibatalkan oleh pelanggan ketika masih berstatus menunggu (pending) dan belum dibayar.',
                ]);
            }

            $locked->status = self::STATUS_CANCELLED;
            $locked->save();

            $this->setRawAttributes($locked->getRawOriginal(), true);

            return $this;
        });
    }

    public function canMarkCashPaidByAdmin(): bool
    {
        return $this->payment_method === self::METHOD_CASH
            && $this->payment_status === self::PAYMENT_UNPAID
            && in_array($this->status, [
                self::STATUS_PENDING,
                self::STATUS_PREPARING,
                self::STATUS_READY,
            ], true);
    }

    public function canTransitionToByAdmin(string $targetStatus): bool
    {
        return match ($targetStatus) {
            self::STATUS_PREPARING => $this->status === self::STATUS_PENDING
                && ($this->payment_method === self::METHOD_CASH || $this->payment_status === self::PAYMENT_PAID),
            self::STATUS_READY => $this->status === self::STATUS_PREPARING,
            self::STATUS_COMPLETED => $this->status === self::STATUS_READY
                && $this->payment_status === self::PAYMENT_PAID,
            self::STATUS_CANCELLED => $this->payment_status === self::PAYMENT_UNPAID
                && in_array($this->status, [self::STATUS_PENDING, self::STATUS_PREPARING, self::STATUS_READY], true),
            default => false,
        };
    }

    /**
     * @return list<string>
     */
    public function allowedAdminTargetStatuses(): array
    {
        $targets = [
            self::STATUS_PREPARING,
            self::STATUS_READY,
            self::STATUS_COMPLETED,
            self::STATUS_CANCELLED,
        ];

        return array_values(array_filter(
            $targets,
            fn (string $target): bool => $this->canTransitionToByAdmin($target)
        ));
    }

    public function markCashPaidByAdmin(): self
    {
        return DB::transaction(function (): self {
            $locked = self::whereKey($this->id)->lockForUpdate()->firstOrFail();

            if (! $locked->canMarkCashPaidByAdmin()) {
                throw ValidationException::withMessages([
                    'payment_status' => 'Penandaan lunas tunai hanya berlaku untuk pesanan cash yang belum dibayar pada status pending, preparing, atau ready.',
                ]);
            }

            $locked->payment_status = self::PAYMENT_PAID;
            $locked->paid_at = now();
            $locked->save();

            $this->setRawAttributes($locked->getRawOriginal(), true);

            return $this;
        });
    }

    public function transitionStatusByAdmin(string $targetStatus): self
    {
        return DB::transaction(function () use ($targetStatus): self {
            $locked = self::whereKey($this->id)->lockForUpdate()->firstOrFail();

            if (! $locked->canTransitionToByAdmin($targetStatus)) {
                throw ValidationException::withMessages([
                    'status' => "Transisi status pesanan dari '{$locked->status}' ke '{$targetStatus}' dengan status pembayaran '{$locked->payment_status}' tidak diizinkan.",
                ]);
            }

            $locked->status = $targetStatus;
            $locked->save();

            $this->setRawAttributes($locked->getRawOriginal(), true);

            return $this;
        });
    }
}
