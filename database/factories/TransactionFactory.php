<?php

namespace Database\Factories;

use App\Models\Transaction;
use App\Models\BoothSession;
use App\Enums\TransactionStatusEnum;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        return [
            'id' => uniqid('trx_'),
            'session_id' => null, // diisi dari seeder
            'amount' => $this->faker->randomElement([15000, 20000, 25000]),
            'discount' => 0,
            'status' => TransactionStatusEnum::PAID,
        ];
    }

}
