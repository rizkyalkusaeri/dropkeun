<?php

namespace App\Http\Controllers;

use App\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $category = Category::with(['parent'])->orderBy('created_at', 'DESC')->paginate(10);
        $parent = Category::getParent()->orderBy('name', 'ASC')->get();

        return view('categories.index', compact('category', 'parent'));
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|max:50|unique:categories'
        ]);

        $request->request->add(['slug' => $request->name]);
        Category::create($request->except('_token'));

        return redirect(route('category.index'))->with(['success' => 'Kategori Baru ditambahkan']);
    }

    public function edit($id)
    {
        $category = Category::find($id);
        $parent = Category::getParent()->orderBy('name', 'ASC')->get();

        return view('categories.edit', compact('category', 'parent'));
    }

    public function update(Request $request, $id)
    {
        //VALIDASI FIELD NAME
        //YANG BERBEDA ADA TAMBAHAN PADA RULE UNIQUE
        //FORMATNYA ADALAH unique:nama_table,nama_field,id_ignore
        //JADI KITA TETAP MENGECEK UNTUK MEMASTIKAN BAHWA NAMA CATEGORYNYA UNIK
        //AKAN TETAPI KHUSUS DATA DENGAN ID YANG AKAN DIUPDATE DATANYA DIKECUALIKAN
        $this->validate($request, [
            'name' => 'required|string|max:50|unique:categories,name,' . $id
        ]);

        $category = Category::find($id); //QUERY UNTUK MENGAMBIL DATA BERDASARKAN ID
        //KEMUDMIAN PERBAHARUI DATANYA
        //POSISI KIRI ADALAH NAMA FIELD YANG ADA DITABLE CATEGORIES
        //POSISI KANAN ADALAH VALUE DARI FORM EDIT
        $category->update([
            'name' => $request->name,
            'parent_id' => $request->parent_id
        ]);

        //REDIRECT KE HALAMAN LIST KATEGORI
        return redirect(route('category.index'))->with(['success' => 'Kategori Diperbaharui!']);
    }

    public function destroy($id)
    {
        //Buat query untuk mengambil category berdasarkan id menggunakan method find()
        //ADAPUN withCount() SERUPA DENGAN EAGER LOADING YANG MENGGUNAKAN with()
        //HANYA SAJA withCount() RETURNNYA ADALAH INTEGER
        //JADI NNTI HASIL QUERYNYA AKAN MENAMBAHKAN FIELD BARU BERNAMA child_count YANG BERISI JUMLAH DATA ANAK KATEGORI
        $category = Category::withCount(['child', 'product'])->find($id);
        //JIKA KATEGORI INI TIDAK DIGUNAKAN SEBAGAI PARENT ATAU CHILDNYA = 0
        if ($category->child_count == 0 && $category->product_count == 0) {
            //MAKA HAPUS KATEGORI INI
            $category->delete();
            //DAN REDIRECT KEMBALI KE HALAMAN LIST KATEGORI
            return redirect(route('category.index'))->with(['success' => 'Kategori Dihapus!']);
        }
        //SELAIN ITU, MAKA REDIRECT KE LIST TAPI FLASH MESSAGENYA ERROR YANG BERARTI KATEGORI INI SEDANG DIGUNAKAN
        return redirect(route('category.index'))->with(['error' => 'Kategori Ini Memiliki Anak Kategori!']);
    }
}