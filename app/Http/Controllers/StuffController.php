<?php

namespace App\Http\Controllers;

use App\Helpers\Apiformater;
use App\Models\Stuff;
use Illuminate\Http\Request;

class StuffController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }
    public function index()
    {
        try {
            $data = Stuff::all()->toArray();

            return Apiformater::sendResponse(200, 'success', $data);
        } catch (\Exception $err) {
            return Apiformater::sendResponse(400, 'bad request', $err->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'name' => 'required',
                'category' => 'required',
            ]);

            $prosesData = Stuff::create([
                'name' => $request->name,
                'category' => $request->category,
            ]);

            if ($prosesData) {
                return Apiformater::sendResponse(200, 'success', $prosesData);
            } else {
                return Apiformater::sendResponse(400, 'bad request', 'Gagal memproses data stuff!
                 Silahkan coba lagi.');
            }
        } catch (\Exception $err) {
            return Apiformater::sendResponse(400, 'bad request', $err->getMessage());
        }
    }

    public function show($id)
{
    try {
        $data = Stuff::where('id', $id)->first();
        //first() : kalau gada, tetep succes data kosong
        //firstOrFail() : kalau data nya gada tapi minta di munculkan jadi error
        //find() : mencari primary key
        //where() : mencari column yg lebih spesifik
        return
            Apiformater::sendResponse(200, 'success', $data);
    } catch (\Exception $err) {
        return Apiformater::sendResponse(400, 'bad request', $err->getMessage());
    }
    }    

    public function update(Request $request,$id)
    {
        try{
            $this->validate($request,[
                'name' => 'required',
                'category'=> 'required',
            ]);

        $checkProses = Stuff::where('id', $id)->update([
            'name'=> $request->name,
            'category'=>$request->category,
        ]);

        if ($checkProses) {
            //::create([]): menghasilkan data yg ditambah
            //::update([]):menghasilkan boolean, jd buat ambil data terbaru di cari lg
            $data = Stuff::where('id', $id)->first();
            return
            Apiformater::sendResponse(200,'succes',$data);
        }
        } catch(\Exception $err) {
            return
            Apiformater::sendResponse(400,'bad request',$err->getMessage());
        }
    }
    
    public function destroy($id)
  {
      try{
          $stuff = Stuff::where('id', $id)->first();

              if($stuff->inboundStuffs()->exists()) {
                  return Apiformater::sendResponse(400,'bad request', 'Tidak dapat menghapus data stuff karena sudah terdapat data inbound!');
                }

                elseif($stuff->stuffStock()->exists()) {
                return Apiformater::sendResponse(400,'bad request', 'Tidak dapat menghapus data stuff karena sudah terdapat data inbound!');
                }

                elseif($stuff->lending()->exists()) {
                return Apiformater::sendResponse(400,'bad request', 'Tidak dapat menghapus data stuff karena sudah terdapat data inbound!');
                }
                
              $checkProsess = $stuff->delete();

          if ($checkProsess) {
              return Apiformater::sendResponse(200, 'succes', 'Berhasil hapus data stuff');
            }
      }catch (\Exception $err) {
          return Apiformater::sendResponse(400, 'bad request', $err->getMessage());
  }
}

    public function trash()
    {
        try {
        $data = Stuff::onlyTrashed()->get();

        return 
        ApiFormater::sendResponse(200, 'succes',$data);
        } catch (\Exception $err) {
            return ApiFormater::sendResponse(400, 'bad request', $err->getMessage());
        }
    }
    public function restore($id)
    {
        try {
            $checkRestore = Stuff::onlyTrashed()->restore();
    
            if ($checkRestore) {
                $data = Stuff::where('id', $id)->first();
                return ApiFormater::sendResponse(200, 'success', $data);
            }
        } catch (\Exception $err) {
            return ApiFormater::sendResponse(400, 'bad request', $err->getMessage());
        }
    }

    public function permanentDelete($id)
    {
        try{
            $checkPermanentDelete = Stuff::onlyTrashed()->where('id', $id)->forceDelete();

        if ($checkPermanentDelete) {
            return Apiformater::sendResponse(200,'succes','Berhasil menghapus permanent data stuff');
        }
        } catch (\Exception $err){
            return Apiformater::sendResponse(400,'bad request', $err->getMessage());
        }

    }
}    