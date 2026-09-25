<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * A row used to be a card an admin typed in. It becomes a booking: it holds one numbered spot in
 * one rail, it knows what was paid for it and it carries a token so the buyer can fill in their
 * own card afterwards. A booking with no spot yet is waiting for the first one to come free.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sponsor_slots', function (Blueprint $table) {
            // Which card in the rail: 1 is the top one. Null means it is in the queue.
            $table->unsignedTinyInteger('position')->nullable()->after('side');
            // pending → paid → live → ended. Cancelled covers refunds and expired holds.
            $table->string('status', 20)->default('live')->after('position');
            // A checkout in progress holds its spot until this moment, then anyone may take it.
            $table->timestamp('reserved_until')->nullable()->after('status');
            $table->timestamp('paid_at')->nullable()->after('reserved_until');
            $table->timestamp('queued_at')->nullable()->after('paid_at');
            $table->string('buyer_email')->nullable()->after('queued_at');
            $table->string('checkout_session_id')->nullable()->after('buyer_email');
            $table->string('payment_reference')->nullable()->after('checkout_session_id');
            // Minor units, the way the payment provider counts them.
            $table->unsignedInteger('amount')->nullable()->after('payment_reference');
            $table->string('currency', 3)->nullable()->after('amount');
            // The buyer's own link to their card. Never guessable, never expires.
            $table->string('token', 64)->nullable()->unique()->after('currency');
            $table->timestamp('reminded_at')->nullable()->after('token');
            $table->timestamp('ending_notice_at')->nullable()->after('reminded_at');

            $table->index(['status', 'position']);
        });

        // A booking exists before its card does, so the card's own fields start out empty.
        Schema::table('sponsor_slots', function (Blueprint $table) {
            $table->string('name')->nullable()->change();
            $table->string('tagline', 120)->nullable()->change();
            $table->string('url', 2048)->nullable()->change();
        });

        // Everything booked by hand keeps running: give each one the next free spot on its side.
        foreach (['left', 'right'] as $side) {
            $position = 1;

            foreach (DB::table('sponsor_slots')->where('side', $side)->orderBy('sort_order')->orderBy('id')->pluck('id') as $id) {
                DB::table('sponsor_slots')->where('id', $id)->update(['position' => $position++]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('sponsor_slots', function (Blueprint $table) {
            $table->dropIndex(['status', 'position']);
            $table->dropColumn([
                'position', 'status', 'reserved_until', 'paid_at', 'queued_at', 'buyer_email',
                'checkout_session_id', 'payment_reference', 'amount', 'currency', 'token',
                'reminded_at', 'ending_notice_at',
            ]);
        });
    }
};
