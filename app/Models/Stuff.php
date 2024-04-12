<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Stuff extends Model
{
    //jika di migrationnya menggunakan $table->softdeletes
    use SoftDeletes;
    //fillable /guarded
    protected $fillable = ["name", "category"];
    //protected $guarded = ['id'] 

    //relasi
    //nama function = samain kaya nama model kata yg petamahuruf kecil
    //model yg pk = hasOne/hasMany
    //panggil nama model relasi::class
    public function stuffStock(){
    return $this->hasOne(StuffStock::class);
    }
    //relasi hasMany : nama func nya jamak
    public function inboundStuffs(){
        return $this->hasMany(inboundStuff::class);
    }
    public function lendings(){
        return $this->hasMany(Lending::class);
    }
    

}
