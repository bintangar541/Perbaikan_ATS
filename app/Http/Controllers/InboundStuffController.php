<?php

namespace App\Http\Controllers;

use App\Helpers\Apiformater;
use App\Models\Stuff;
use App\Models\InboundStuff;
use App\Models\StuffStock;                                                      
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class InboundStuffController extends Controller
{

    
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index(request $request)
    {
        try{
            if($request->filter_id){
               $data = InboundStuff::where('stuff_id', $request->filter_id)->with('stuff','stuff.stuffStock')->get();
            }else{
                $data = InboundStuff::all();
            }
            return Apiformater::sendResponse(200, 'succes', $data);
           }catch(\Exception $err){
            return Apiformater::sendResponse(400, 'bad request',$err->getMessage());
           }
    }
    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'stuff_id' => 'required',
                'total' => 'required',
                'date' => 'required',
                'proof_file' => 'required|mimes:jpeg,png,jpg,pdf|max:2048',
            ]);

            if($request->hasFile('proof_file')) {
                $proof = $request->file('proof_file');
                $destinationPath = 'proof/';
                $proofName = date('YmdHis') . "." . $proof->getClientOriginalExtension();
                $proof->move($destinationPath, $proofName);
            }
            $createStock = InboundStuff::create([
                'stuff_id' => $request->stuff_id,
                'total' => $request->total,
                'date' => $request->date,
                'proof_file' => $proofName,
            ]);

            if ($createStock){
                $getStuff = Stuff::where('id', $request->stuff_id)->first();
                $getStuffStock = StuffStock::where('stuff_id', $request->stuff_id)->first();

                if (!$getStuffStock){
                    $updateStock = StuffStock::create([
                        'stuff_id' => $request->stuff_id,
                        'total_available' => $request->total,
                        'total_defec' => 0,
                    ]);
                } else {
                    $updateStock = $getStuffStock->update([
                        'stuff_id' => $request->stuff_id,
                        'total_available' =>$getStuffStock['total_available'] + $request->total,
                        'total_defec' => $getStuffStock['total_defec'],
                    ]);
                }

                if ($updateStock) {
                    $getStock = StuffStock::where('stuff_id', $request->stuff_id)->first();
                    $stuff = [
                        'stuff' => $getStuff,
                        'InboundStuff' => $createStock,
                        'stuffStock' => $getStock
                    ];

                    return Apiformater::sendResponse(200, 'Successfully Create A Inbound Stuff Data', $stuff);
                } else {
                    return Apiformater::sendResponse(400, false, 'Failed To Update A Stuff Stock Data');
                }
            } else {
            }
        } catch (\Exception $e) {
            return Apiformater::sendResponse(400, false, $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $inboundData = Inboundstuff::where('id', $id)->first();
            $stuffId = $inboundData['stuff_id'];
            $totalInbound = $inboundData['total'];

            $dataStock = StuffStock::where('stuff_id',$inboundData['stuff_id'])->first();
            $total_available = (int)$dataStock['total_available'] - (int)$totalInbound;

            if ($total_available < 0) {
                return Apiformater::sendResponse(400,'bad request','Jumlah total imbound yang akan dihapus lebih besar dari total available stuff saat ini!');
            }
            
            $inboundData->delete();

            $minusTotalStock = StuffStock::where('stuff_id', $inboundData['stuff_id'])->update(['total_available' => $total_available]);

            if ($minusTotalStock){
                $updatedStuffWithInboundAndStock = Stuff::where('id', $inboundData['staff_id'])->with('inboundStuffs','stuffStock')->first();
                
                $inboundData->delete();
                return Apiformater::sendResponse(200,'Success',$updatedStuffWithInboundAndStock);
            }
        }catch (\Exception $err){
            return Apiformater::sendResponse(400,'bad request',$err->getMessage());
        }
    }
    

    public function trash()
    {
        try{
            $data= InboundStuff::onlyTrashed()->get();

            return Apiformater::sendResponse(200, 'success', $data);
        }catch(\Exception $err){
            return Apiformater::sendResponse(400, 'bad request', $err->getMessage());
        }
    }
    
    public function restore(InboundStuff $id)
    {
        try {
            // Memulihkan data dari tabel 'inbound_stuffs'
            $checkProses = InboundStuff::onlyTrashed()->where('id', $id)->restore();
    
            if ($checkProses) {
                // Mendapatkan data yang dipulihkan
                $restoredData = InboundStuff::find($id);
    
                // Mengambil total dari data yang dipulihkan
                $totalRestored = $restoredData->total;
    
                // Mendapatkan stuff_id dari data yang dipulihkan
                $stuffId = $restoredData->stuff_id;
    
                // Memperbarui total_available di tabel 'stuff_stocks'
                $stuffStock = StuffStock::where('stuff_id', $stuffId)->first();
                
                if ($stuffStock) {
                    // Menambahkan total yang dipulihkan ke total_available
                    $stuffStock->total_available += $totalRestored;
    
                    // Menyimpan perubahan pada stuff_stocks
                    $stuffStock->save();
                }
    
                return Apiformater::sendResponse(200, 'success', $restoredData);
            } else {
                return Apiformater::sendResponse(400, 'bad request', 'Gagal mengembalikan data!');
            }
        } catch (\Exception $err) {
            return Apiformater::sendResponse(400, 'bad request', $err->getMessage());
        }
    }

    public function deletePermanent(InboundStuff $inboundStuff, Request $request, $id)
    {
        try {
            $getInbound = InboundStuff::onlyTrashed()->where('id',$id)->first();

            unlink(base_path('public/proof/'.$getInbound->proof_file));
            // Menghapus data dari database
            $checkProses = InboundStuff::where('id', $id)->forceDelete();
    
            // Memberikan respons sukses
            return Apiformater::sendResponse(200, 'success', 'Data inbound-stuff berhasil dihapus permanen');
        } catch(\Exception $err) {
            // Memberikan respons error jika terjadi kesalahan
            return Apiformater::sendResponse(400, 'bad request', $err->getMessage());
        }
    }   

    
    
}