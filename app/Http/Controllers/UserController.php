<?php

namespace App\Http\Controllers;

use App\Helpers\Apiformater;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{

    
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index()
    {
        try {
            $data = User::all()->toArray();

            return Apiformater::sendResponse(200, 'success', $data);
        } catch (\Exception $err) {
            return Apiformater::sendResponse(400, 'bad request', $err->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {   
            $this->validate($request, [
                'username' => 'required|min:4|unique:users,username',
                'email' => 'required|unique:users,email',
                'password' => 'required|min:6',
                'role' => 'required'
            ]);

            $prosesData = User::create([
                'username' => $request->username, 
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role'=> $request->role
            ]);
            
            if ($prosesData) { // Memeriksa apakah $prosesData adalah instance model yang valid
                return Apiformater::sendResponse(200, 'success', $prosesData);
            } else {
                return Apiformater::sendResponse(400, 'bad_request', 'Gagal menambahkan data, silahkan coba lagi !');
            }
        } catch (\Exception $err){
            return Apiformater::sendResponse(400, 'bad_request', $err->getMessage());
        }
    }
    public function show($id)
    {
        try {
            $data = User::where('id', $id)->first();
            return Apiformater::sendResponse(200, 'success', $data);
        } catch (\Exception $err) {
            return Apiformater::sendResponse(400, 'bad request', $err->getMessage());
        }
    }

    public function update(Request $Request, $id)
{
    try {
        $this->validate($Request, [
            'username' => 'required|min:4|unique:users,username,' . $id,
            'email' => 'required|unique:users,email,' . $id,
            'password' => 'required|min:6',
            'role' => 'required'
        ]);

        $checkProses = User::where('id', $id)->update([
            'username' => $Request->username,
            'email' => $Request->email,
            'password' => hash::make($Request->password),
            'role' => $Request->role
        ]);

        if ($checkProses) {
            $data = User::where('id', $id)->first();

            return Apiformater::sendResponse(200, 'success', $data);
        }
    } catch (\Exception $err) {
        return Apiformater::sendResponse(400, 'bad request', $err->getMessage());
    }
}

    public function destroy($id)
    {
        try {
            $checkproses = User::where('id', $id)->delete();

            if ($checkproses) {
                return
                    Apiformater::sendResponse(200, 'succes', 'berhasil hapus data User!');
            }
        } catch (\Exception $err) {
            return
                Apiformater::sendResponse(400, 'bad request', $err->getMessage());
        }
    }

    public function trash()
    {
        try {
            $data = User::onlyTrashed()->get();

            return
                Apiformater::sendResponse(200, 'succes', $data);
        } catch (\Exception $err) {
            return
                Apiformater::sendResponse(400, 'bad request', $err->getMessage());
        }
    }

    public function restore($id)
    {
        try {
            $checkRestore = User::onlyTrashed()->where('id',$id)->restore();

            if ($checkRestore) {
                $data = User::where('id', $id)->first();
                return Apiformater::sendResponse(200, 'succes', $data);
            }
        }catch (\Exception $err) {
            return Apiformater::sendResponse(400, 'bad request', $err->getMessage());
        }
    }

    public function permanentDelete($id)
    {
        try{
            $cekPermanentDelete = User::onlyTrashed()->where('id', $id)->forceDelete();

            if ($cekPermanentDelete) {
                return
                Apiformater::sendResponse(200, 'success','Berhasil menghapus data secara permanen' );
            }
        } catch (\Exception $err) {
            return
            Apiformater::sendResponse(400,'bad_request', $err->getMessage());
        }

    }

   

}