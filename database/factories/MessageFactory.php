<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Group;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Message>
 */
class MessageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
{
    // Pilih secara acak antara 0 atau 1 untuk menentukan senderId awal
    $senderId = $this->faker->randomElement([0, 1]);

    // Jika senderId adalah 0, pilih sender dari user yang bukan ID 1, dan tetapkan receiverId sebagai 1
    if ($senderId === 0) {
        // Ambil ID acak dari pengguna yang bukan ID 1
        $senderId = $this->faker->randomElement(User::where('id', '!=', 1)->pluck('id')->toArray());
        // Tetapkan receiverId menjadi 1
        $receiverId = 1;
    } else {
        // Jika senderId bukan 0 (berarti 1), pilih ID penerima secara acak dari semua pengguna
        $receiverId = $this->faker->randomElement(User::pluck('id')->toArray());
    }

    // Inisialisasi groupId sebagai null karena pesan bisa jadi tidak terkait dengan grup
    $groupId = null;

    // Ada kemungkinan 50% bahwa pesan terkait dengan grup
    if ($this->faker->boolean(50)) {
        // Pilih ID grup secara acak dari semua grup yang ada
        $groupId = $this->faker->randomElement(Group::pluck('id')->toArray());

        // Temukan grup berdasarkan ID yang dipilih
        $group = Group::find($groupId);

        // Pilih ID pengirim secara acak dari pengguna yang merupakan anggota grup ini
        $senderId = $this->faker->randomElement($group->users->pluck('id')->toArray());
        // Jika pesan terkait grup, receiverId diatur menjadi null karena pesan grup tidak memiliki penerima individu
        $receiverId = null;
    }


    return [
        'sender_id' => $senderId,           // ID pengirim pesan
        'receiver_id' => $receiverId,       // ID penerima pesan (null jika pesan grup)
        'group_id' => $groupId,             // ID grup jika pesan dikirim dalam grup, null jika tidak
        'message' => $this->faker->realText(200),  // Pesan acak dengan panjang hingga 200 karakter
        'created_at' => now(),              // Waktu pembuatan pesan, diatur ke waktu saat ini
    ];
}

}
