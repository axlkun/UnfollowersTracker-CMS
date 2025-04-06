<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Usuario;
use App\Models\Data;
use Illuminate\Support\Facades\Log;

class DataController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:zip|max:15360',
            'username' => 'required|string|max:191'
        ]);

        if ($validator->fails()) {

            Log::error('Validation failed', [
                'file' => $request->file('file') ? $request->file('file')->getClientOriginalName() : 'No file uploaded',
                'errors' => $validator->messages()
            ]);

            return response()->json([
                'status' => 422,
                'errors' => $validator->messages()
            ], 422);
        }

        $archivo = $request->file('file');
        $nombreUsuario = strtolower(trim($request->username));
        $zip = new \ZipArchive;

        if ($zip->open($archivo)) {
            DB::beginTransaction();

            try {
                $usuario = Usuario::updateOrCreate(
                    ['username' => $nombreUsuario],
                    [] 
                );

                $usuarioId = $usuario->id;
                $data = [];

                $requiredFiles = [
                    'connections/followers_and_following/followers_1.json',
                    'connections/followers_and_following/following.json',
                ];

                // Verificar que los archivos requeridos existan en el ZIP
                foreach ($requiredFiles as $requiredFile) {
                    if ($zip->locateName($requiredFile) === false) {
                        throw new \Exception("ZIP File Incomplete. Missing file: '$requiredFile' ");
                    }

                    $contenido = $zip->getFromName($requiredFile);
                    $jsonDecoded = json_decode($contenido, true);

                    if (is_null($jsonDecoded)) {
                        throw new \Exception("Check your ZIP File. Error in File '$requiredFile' ");
                    }
                }

                // Si los archivos obligatorios existen y son válidos, ahora procesamos todo
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $nombreDocumento = $zip->getNameIndex($i);

                    // Solo procesar archivos JSON
                    if (pathinfo($nombreDocumento, PATHINFO_EXTENSION) !== 'json') {
                        continue;
                    }

                    $nombreCampo = pathinfo($nombreDocumento, PATHINFO_FILENAME);
                    $contenidoJSON = $zip->getFromName($nombreDocumento);
                    $datos = json_decode($contenidoJSON, true);

                    // Validar si el JSON se pudo parsear correctamente
                    if (!is_null($datos)) {
                        $data[$nombreCampo] = $datos;
                    }
                }

                // Guardar los datos en la base de datos
                Data::updateOrCreate(
                    ['usuario_id' => $usuarioId],
                    [
                        'close_friends' => $data['close_friends'] ?? null,
                        'followers' => $data['followers_1'] ?? null,
                        'following' => $data['following'] ?? null,
                        'hide_story_from' => $data['hide_story_from'] ?? null,
                        'pending_follow_requests' => $data['pending_follow_requests'] ?? null,
                        'recent_follow_requests' => $data['recent_follow_requests'] ?? null,
                        'recently_unfollowed_accounts' => $data['recently_unfollowed_accounts'] ?? null,
                        'removed_suggestions' => $data['removed_suggestions'] ?? null
                    ]
                );

                DB::commit();

                return response()->json([
                    'status' => 200,
                    'message' => 'Data stored successfully'
                ], 200);
            } catch (\Exception $e) {
                DB::rollback();

                Log::error('Transaction failed', [
                    'file' => $archivo->getClientOriginalName(),
                    'message' => $e->getMessage(),
                    'file_location' => $e->getFile(),
                    'line' => $e->getLine()
                ]);

                return response()->json([
                    'status' => 400,
                    'message' => $e->getMessage()
                ], 400);
            }
        } else {
            Log::error('Failed to open ZIP file', ['file' => $archivo->getClientOriginalName()]);

            return response()->json([
                'status' => 500,
                'message' => 'Failed to open ZIP file'
            ], 500);
        }
    }


    public function getDataFollowing($user)
    {
        try {
            $usuario = Usuario::where('username', $user)->first();

            if (!$usuario) {
                Log::warning('Error in getDataFollowing: User not found', ['username' => $user]);
                return false;
            }

            $following = $usuario->api_data->following;
            $array_seguidos = array();

            // validacion aqui
            foreach ($following['relationships_following'] as $data) {
                $value = $data['string_list_data'][0]['value'];
                $timestamp = date('Y-m-d', $data['string_list_data'][0]['timestamp']);
                $link = $data['string_list_data'][0]['href'];

                $array_seguidos[] = array(
                    "user_name" => $value,
                    "enlace" => $link,
                    "date" => $timestamp
                );
            }

            return $array_seguidos;
        } catch (\Exception $e) {
            Log::error('Error in getDataFollowing', [
                'username' => $user,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return false;
        }
    }

    public function getDataFollowers($user)
    {
        try {
            $usuario = Usuario::where('username', $user)->first();

            if (!$usuario) {
                Log::warning('Error in getDataFollowers: User not found', ['username' => $user]);
                return false;
            }

            $followers = $usuario->api_data->followers;
            $array_seguidores = array();

            foreach ($followers as $data) {
                $value = $data['string_list_data'][0]['value'];
                $timestamp = date('Y-m-d', $data['string_list_data'][0]['timestamp']);
                $link = $data['string_list_data'][0]['href'];

                $array_seguidores[] = array(
                    "user_name" => $value,
                    "enlace" => $link,
                    "date" => $timestamp
                );
            }

            return $array_seguidores;
        } catch (\Exception $e) {
            Log::error('Error in getDataFollowers', [
                'username' => $user,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return false;
        }
    }

    public function getUnfollowers($user)
    {
        try {
            $following = $this->getDataFollowing($user);
            $followers = $this->getDataFollowers($user);

            if ($following && $followers) {
                $unfollowers_users = array_diff(array_column($following, 'user_name'), array_column($followers, 'user_name'));
                $unfollowers_data = array_filter($following, function ($item) use ($unfollowers_users) {
                    return in_array($item['user_name'], $unfollowers_users);
                });

                $unfollowers = array_values($unfollowers_data);

                return response()->json([
                    'status' => 200,
                    'unfollowers' => $unfollowers
                ], 200);
            } else {
                Log::warning('Following or followers data not found. ', ['username' => $user]);
                return response()->json([
                    'status' => 500,
                    'message' => 'Something went wrong!'
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Error in getUnfollowers', [
                'username' => $user,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return response()->json([
                'status' => 500,
                'message' => 'Something went wrong!'
            ], 500);
        }
    }

    public function getNotFollowing($user)
    {
        try {
            $following = $this->getDataFollowing($user);
            $followers = $this->getDataFollowers($user);

            if ($following && $followers) {
                $unfollowing_users = array_diff(array_column($followers, 'user_name'), array_column($following, 'user_name'));
                $unfollowing_data = array_filter($followers, function ($item) use ($unfollowing_users) {
                    return in_array($item['user_name'], $unfollowing_users);
                });

                $unfollowing = array_values($unfollowing_data);

                return response()->json([
                    'status' => 200,
                    'unfollowing' => $unfollowing
                ], 200);
            } else {
                Log::warning('Following or followers data not found', ['username' => $user]);
                return response()->json([
                    'status' => 404,
                    'message' => 'No such files found!'
                ], 404);
            }
        } catch (\Exception $e) {
            Log::error('Error in getNotFollowing', [
                'username' => $user,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return response()->json([
                'status' => 500,
                'message' => 'Something went wrong!'
            ], 500);
        }
    }
}
