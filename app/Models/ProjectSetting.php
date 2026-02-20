<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectSetting extends Model
{
    use HasFactory;
    protected $fillable = [
        'project_id',
        'price_per_session',
        'copy_prices',
        'copies',
        'max_retakes',
        'countdown_seconds',
        'auto_print',
    ];

    protected $casts = [
        'copy_prices' => 'array',
    ];

    /**
     * Get price for given copy count. Falls back to price_per_session if copy_prices not set.
     */
    public function getPriceForCopies(int $copies): float
    {
        $copyPrices = $this->copy_prices;
        if (is_array($copyPrices) && isset($copyPrices[(string) $copies])) {
            return (float) $copyPrices[(string) $copies];
        }
        if ($copies === 1) {
            return (float) ($this->price_per_session ?? 0);
        }
        return (float) ($this->price_per_session ?? 0) * $copies;
    }

    /**
     * Get available copy options with prices. Returns e.g. [1 => 10000, 2 => 18000].
     */
    public function getCopyPriceOptions(): array
    {
        $copyPrices = $this->copy_prices;
        if (is_array($copyPrices) && !empty($copyPrices)) {
            $options = [];
            foreach ($copyPrices as $n => $price) {
                $n = (int) $n;
                if ($n >= 1) {
                    $options[$n] = (float) $price;
                }
            }
            ksort($options);
            return $options;
        }
        $base = (float) ($this->price_per_session ?? 0);
        return $base > 0 ? [1 => $base] : [1 => 0];
    }

}
