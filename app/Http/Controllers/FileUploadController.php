<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Log, Exception;

use App\Models\FileUpload;
use App\Models\User;
use Illuminate\Http\Request;

class FileUploadController extends Controller
{

    # Upload file
    public function upload($request){
        try {
            # Read file 
            $response = [];
            $userId = $request->user_id;
            $originalName = $request->file('file')->getClientOriginalName();
            $extention = $request->file('file')->getClientOriginalExtension();
            $name = pathinfo($originalName, PATHINFO_FILENAME).'_'.date('d-m-Y h:i:s');
            $slugifyFileName = Str::of($name)->slug('_').'.'.$extention;
            $path = public_path('storage/uploads/'.$userId);

            # Check existing file
            if(!Storage::exists($path)){
                $directoryStatus = Storage::makeDirectory('public/uploads/'.$userId, 0777, true, true);
                if(!$directoryStatus){
                    $response['status'] = 221;
                    $response['message'] = 'Fail to create files uploaded directory';
                }
            }
            # Move file
            $uploadStatus = Storage::putFileAs('public/uploads/'.$userId, $request->file('file'), $slugifyFileName);            
            if(!$uploadStatus){
                $response['status'] = 221;
                $response['message'] = 'Fail to upload files in the specified directory';
            }
            
            # Return response
            if(empty($response)){
                return $response['slugifyFileName'] = $slugifyFileName;
            }else{
                return $response;
            }

        } catch (Exception $ex) {
            Log::debug($ex);
        }
    }

    # Display all the uploaded 
    public function index($id){
        try {
            # Global variables
            $message = 'Something went wrong, please try again later';
            $status = 404;
            $fileRecords = 0;
            $response = [];

            # Get files
            $users = User::with('files')->where('users.id', $id)->first();
            if(!empty($users) && count($users->files) > 0){
                $status = 200;
                $message = 'List of files';
                $fileRecords = count($users->files);
            }else{
                $status = 200;
                $message = 'No records found';
            }

            # Generate response
            $response['status'] = $status;
            $response['message'] = $message;
            if(!empty($fileRecords)){
                $response['total_records'] = $fileRecords;
                $response['files'] = $users->files;
            }
            return response()->json($response);

        } catch (Exception $ex) {
            Log::debug($ex);
        }
    }

    # Store newly uploaded file
    public function store(Request $request){
        try {
            # Validation 
            $validator = Validator::make($request->all(), [
                'file' => 'required|file|max:500',
                'user_id'=> 'required|numeric',
            ]);
            if ($validator->fails()) {
                return $validator->errors();
            }

            # Status variable
            $status = 404;
            $message = 'Something went wrong, please try again later';
            $userId = $request->user_id;
            
            # Upload file
            $result = $this->upload($request);
            if(!empty($result['status']) && !empty($result['message'])){
                $status = $result['status'];
                $message = $result['message'];
            }else{
                # Save file
                $saveStatus = FileUpload::create([
                    'file_name'=>$result,
                    'user_id'=>$userId
                ]);
                # Check status
                $link = null;
                if($saveStatus){
                    $status = 200;
                    $message = 'File uploaded successfully';
                    $link = asset('storage/uploads/'.$userId.'/'.$result);
                }else{
                    $status = 221;
                    $message = 'Fail to save uploaded file';
                }
            }
            
            # Generate response
            $response['status'] = $status;
            $response['message'] = $message;
            if(!empty($link)){
                $response['download'] = $link;
            }
            return response()->json($response);

        } catch (Exception $ex) {
            Log::debug($ex);
        }
    }

    # Display the specific file
    public function show(User $user, FileUpload $file){
        try {
            # Global variable
            $status = 404;
            $message = 'Something went wrong, please try again later';
            $response = [];
            $link = null;

            # Create link
            if(!empty($file)){
                $status = 200;
                $message = 'Records found';
                $link = asset('storage/uploads/'.$user->id.'/'.$file->file_name);
            }

            # Generate response
            $response['status'] = $status;
            $response['message'] = $message;
            $response['records'] = $file;
            if(!empty($link)){
                $response['download'] = $link;
            } 
            return response()->json($response);

        } catch (Exception $ex) {
            Log::debug($ex);
        }
    }

    # Update the specific file
    public function update(Request $request, User $user, FileUpload $file){
        try {
            # Validation 
            $validator = Validator::make($request->all(), [
                'file' => 'required|file|max:500',
            ]);
            if ($validator->fails()) {
                return $validator->errors();
            }
            # Global variable
            $status = 404;
            $message = 'Something went wrong, please try again later';
            $response = [];
            $link = null;
            $userId = $file->user_id;
            $request->merge(['user_id'=>$userId]);


            if(!empty($file)){
                # Upload file
                $result = $this->upload($request);
                if(!empty($result['status']) && !empty($result['message'])){
                    $status = $result['status'];
                    $message = $result['message'];
                }else{
                    # Update existing file
                    $oldFile = $file->file_name;
                    $saveStatus = $file->update([
                        'file_name'=>$result
                    ]);
                    # Check status
                    if($saveStatus){
                        $status = 200;
                        $message = 'File updated successfully';
                        unlink('storage/uploads/'.$userId.'/'.$oldFile);
                        $link = asset('storage/uploads/'.$userId.'/'.$file->file_name);
                    }else{
                        $status = 221;
                        $message = 'Fail to update uploaded file';
                    }
                }
            }
            
            # Generate response
            $response['status'] = $status;
            $response['message'] = $message;
            if(!empty($link)){
                $response['download'] = $link;
            }
            return response()->json($response);

        } catch (Exception $ex) {
            Log::debug($ex);
        }
    }

    # Remove the specific file
    public function destroy(User $user, FileUpload $file){
        try {
            $message = 'Something went wrong, please tyr again later';
            $status = 404;
            if(!empty($file)){
                $fileName = $file->file_name;
                $status = $file->delete();
                if($status){
                    $message = 'File deleted successfully';
                    $status = 200;
                    unlink('storage/uploads/'.$user->id.'/'.$fileName);
                }else{
                    $message = 'Fail to delete file';
                    $status = 221;
                }
            }

            # Generate response
            $response['status'] = $status;
            $response['message'] = $message;
            return response()->json($response);

        } catch (Exception $th) {
            Log::debug($ex);
        }
    }
}
