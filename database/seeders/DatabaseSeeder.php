<?php

namespace Database\Seeders;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Group;
use App\Models\Message;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Conversation;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'John Smith',
            'email' => 'John@example.com',
            'password' => bcrypt('password'), // Mengenkripsi kata sandi
            'is_admin' => true                // Menandai pengguna ini sebagai admin
        ]);

        User::factory()->create([
            'name' => 'Jean Smith',
            'email' => 'Jean@example.com',
            'password' => bcrypt('password'), // Mengenkripsi kata sandi
        ]);

        // Membuat 10 pengguna tambahan secara acak menggunakan factory User.
        User::factory(10)->create();

        // Membuat 5 grup dan menambahkan anggota secara acak ke dalam setiap grup.
        for ($i = 0; $i < 5; $i++) {
            // Membuat grup baru dengan ID pemilik adalah 1 (John Smith).
            $group = Group::factory()->create([
                'owner_id' => 1,
            ]);

            // Memilih 2 hingga 5 pengguna secara acak dari daftar pengguna yang ada.
            $users = User::inRandomOrder()->limit(rand(2, 5))->pluck('id');
            // Menambahkan pengguna yang dipilih secara acak ke dalam grup bersama dengan pemilik grup (ID 1).
            $group->users()->attach(array_unique([1, ...$users]));
        }

        Message::factory(1000)->create();

        // Mengambil semua pesan yang tidak terkait dengan grup dan mengurutkannya berdasarkan waktu pembuatan.
        $messages = Message::whereNull('group_id')->orderBy('created_at')->get();

        // Mengelompokkan pesan berdasarkan kombinasi pengirim dan penerima untuk membentuk percakapan.
        $conversations = $messages->groupBy(function ($message) {
            // Membuat kunci yang unik untuk setiap pasangan pengirim-penerima dengan mengurutkan ID mereka.
            return collect([$message->sender_id, $message->receiver_id])->sort()->implode('_');
        })->map(function ($groupedMessages) {
            // Membuat array dengan informasi tentang percakapan, termasuk dua ID pengguna, ID pesan terakhir, dan timestamp.
            return [
                'user_id1' => $groupedMessages->first()->sender_id,   // ID pengguna pertama dalam percakapan
                'user_id2' => $groupedMessages->first()->receiver_id, // ID pengguna kedua dalam percakapan
                'last_message_id' => $groupedMessages->last()->id,    // ID dari pesan terakhir dalam percakapan
                'created_at' => new Carbon(),                         // Waktu pembuatan percakapan
                'updated_at' => new Carbon(),                         // Waktu terakhir diperbarui
            ];
        })->values();

        // Menyisipkan percakapan ke dalam tabel Conversation tanpa duplikasi (menggunakan insertOrIgnore).
        Conversation::insertOrIgnore($conversations->toArray());
    }

}
