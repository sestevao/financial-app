<?php

use App\Models\User;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can view transactions page', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get('/transactions');

    $response->assertStatus(200);
});

test('user can create transaction', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->post('/transactions', [
            'name' => 'Test Transaction',
            'amount' => 100.00,
            'date' => '2023-01-01',
            'type' => 'expense',
            'status' => 'Completed',
            'category' => 'Food',
        ]);

    $response->assertRedirect('/transactions');
    $this->assertDatabaseHas('transactions', [
        'name' => 'Test Transaction',
        'user_id' => $user->id,
    ]);
});

test('user can delete transaction', function () {
    $user = User::factory()->create();
    $transaction = Transaction::factory()->create([
        'user_id' => $user->id,
    ]);

    $response = $this
        ->actingAs($user)
        ->delete("/transactions/{$transaction->id}");

    $response->assertRedirect('/transactions');
    $this->assertDatabaseMissing('transactions', [
        'id' => $transaction->id,
    ]);
});

test('user cannot delete others transaction', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $transaction = Transaction::factory()->create([
        'user_id' => $otherUser->id,
    ]);

    $response = $this
        ->actingAs($user)
        ->delete("/transactions/{$transaction->id}");

    $response->assertStatus(403);
    $this->assertDatabaseHas('transactions', [
        'id' => $transaction->id,
    ]);
});
