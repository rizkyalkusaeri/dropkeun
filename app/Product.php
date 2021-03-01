<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
  public function getStatusLabelAttribute()
  {
    if ($this->status == 0) {
      return '<span class="badge badge-secondary"> Draft </span>';
    }
    return '<span class="badge badge-success">Aktif</span>';
  }

  public function category()
  {
    return $this->belongsTo(Category::class);
  }

  //JIKA FILLABLE AKAN MENGIZINKAN FIELD APA SAJA YANG ADA DIDALAM ARRAYNYA
  //MAKA GUARDED AKAN MEMBLOK FIELD APA SAJA YANG ADA DIDALAM ARRAY-NYA
  //JADI APABILA FIELDNYA BANYAK MAKA KITA BISA MANFAATKAN DENGAN HANYA MENULISKAN ARRAY KOSONG
  //YANG BERARTI TIDAK ADA FIELD YANG DIBLOCK SEHINGGA SEMUA FIELD TERSEBUT SUDAH DIIZINAKAN
  //HAL INI MEMUDAHKAN KITA KARENA TIDAK PERLU MENULISKANNYA SATU PERSATU
  protected $guarded = [];

  //SEDANGKAN INI ADALAH MUTATORS, PENJELASANNYA SAMA DENGAN ARTIKEL SEBELUMNYA
  public function setSlugAttribute($value)
  {
    $this->attributes['slug'] = Str::slug($value);
  }
}