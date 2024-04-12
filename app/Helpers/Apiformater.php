<?php

namespace App\Helpers;
//namespace untuk menentukan lokasi folder dari file ini
// nama class == nama file

class Apiformater {
    //variabel struktur datta yg akan di tampilkan di response postman
    protected  static $response = [
        "status" => NULL,
        "message" => NULL,
        "data" => NULL,
    ];

    public static function sendResponse($status = NULL, $message = NULL, $data = [])
    {
        self::$response['status'] = $status;
        self::$response['message'] = $message;
        self::$response['data'] = $data;
        return response()->json(self::$response, self::$response['status']);
        //status : http status code (200,400,500)
        //message : desc http status code ('succes','bad request'.'server error')
        //data : hasil yg diambil dari db
    }
}