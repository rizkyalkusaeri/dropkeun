<?php

namespace App\Jobs;

use App\Imports\ProductImport;
use App\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

class ProductJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $category;
    protected $file_name;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($category, $file_name)
    {
        $this->category = $category;
        $this->file_name = $file_name;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //KEMUDIAN KITA GUNAKAN PRODUCTIMPORT YANG MERUPAKAN CLASS YANG AKAN DIBUAT SELANJUTNYA
        //IMPORT DATA EXCEL TADI YANG SUDAH DISIMPAN DI STORAGE, KEMUDIAN CONVERT MENJADI ARRAY
        $files = (new ProductImport)->toArray(storage_path('app/public/uploads/' . $this->file_name));

        foreach ($files[0] as $row) {
            $explodeURL = explode('/', $row[4]);
            $explodeExtension = explode('.', end($explodeURL));
            $file_name = time() . Str::random(6) . '.' . end($explodeExtension);

            //DOWNLOAD GAMBAR TERSEBUT DARI URL TERKAIT
            file_put_contents(storage_path('app/public/products') . '/' . $file_name, file_get_contents($row[4]));

            Product::create([
                'name' => $row[0],
                'slug' => $row[0],
                'category_id' => $this->category,
                'description' => $row[1],
                'price' => $row[2],
                'weight' => $row[3],
                'image' => $file_name,
                'status' => true
            ]);

            //JIKA PROSESNYA SUDAH SELESAI MAKA FILE YANG ADA DISTORAGE AKAN DIHAPUS
            File::delete(storage_path('app/public/uploads/' . $this->filename));
        }
    }
}