<?php

namespace App\Http\Controllers;

use App\Helpers\Apiformater;
use App\Models\stuffStock;
use Illuminate\Http\Request;
use App\Models\Lending;


class LendingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index()
    {
        try {
            //kalo ada with cek nya itu di relasinya yg ada di model sebelum with, ambil nama functionnya
            $data = Lending::with('stuff', 'user', 'restoration')->get();
            return Apiformater::sendResponse(200, 'succes', $data);
        } catch (\Exception $err) {
            return Apiformater::sendResponse(400, 'bad request', $err->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'stuff_id' => 'required',
                'date_time' => 'required',
                'name' => 'required',
                'total_stuff' => 'required',
            ]);
            //user_id tidak masuk ke validasi karena valuenya bukan bersumber dari luar (dipilih user)

            //cek total_available stuff terkait
            $totalAvailable = stuffStock::where('stuff_id', $request->stuff_id)->value('total_available');

            if (is_null($totalAvailable)) {
                return Apiformater::sendResponse(400, 'bad request', 'Belum ada data inbound !');
            } elseif ((int) $request->total_stuff > (int) $totalAvailable) {
                return Apiformater::sendResponse(400, 'bad request', 'Stock tidak tersedia !');
            } else {
                $lending = Lending::create([
                    'stuff_id' => $request->stuff_id,
                    'date_time' => $request->date_time,
                    'name' => $request->name,
                    'notes' => $request->notes ? $request->notes : '-',
                    'total_stuff' => $request->total_stuff,
                    'user_id' => auth()->user()->id,
                ]);

                $totalAvailableNow = (int) $totalAvailable - (int) $request->total_stuff;
                $stuffStock = stuffStock::where('stuff_id', $request->stuff_id)->update(['total_available' => $totalAvailableNow]);

                $dataLending = Lending::where('id', $lending['id'])->with('user', 'stuff', 'stuff.stuffStock')->first();

                return Apiformater::sendResponse(200, 'succes', $dataLending);
            }
        } catch (\Exception $err) {
            return Apiformater::sendResponse(400, 'bad request', $err->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $delete = Lending::where('id', $id)->first();
            $stuffid = $delete['stuff_id'];
            $totallending = $delete['totall'];
            $delete->delete();

            $dataStock = StuffStock::where('stuff_id', $delete['stuff_id'])->first();
            $total_available = (int) $dataStock['total_available'] + (int) $delete['total_stuff'];
            $updatedTotalLending = StuffStock::where('stuff_id', $delete['stuff_id'])->update(['total_available' => $total_available]);

            if ($updatedTotalLending) {
                $updatedStuffidTotalLending = Lending::where('id', $delete['stuff_id'])->with('Lending', 'stuffStock')->first();

                $delete->delete();
                return Apiformater::sendResponse(200, 'success', 'Berhasil hapus data peminjaman!');
            }
        } catch (\Exception $err) {
            return Apiformater::sendResponse(400, 'bad request', $err->getMessage());
        }



    }
}