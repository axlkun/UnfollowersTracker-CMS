<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Usuario;
use App\Models\Data;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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

                $targetPath = 'connections/followers_and_following/';

                // Lista blanca de archivos permitidos
                $expectedFiles = [
                    'close_friends.json',
                    'followers_1.json', // obligatorio
                    'following.json',   // obligatorio
                    'hide_story_from.json',
                    'pending_follow_requests.json',
                    'recent_follow_requests.json',
                    'recently_unfollowed_accounts.json',
                    'removed_suggestions.json'
                ];

                $requiredFiles = ['followers_1.json', 'following.json'];

                // Validar presencia de los archivos obligatorios
                foreach ($requiredFiles as $file) {
                    $fullPath = $targetPath . $file;

                    if ($zip->locateName($fullPath) === false) {
                        throw new \Exception("ZIP File Incomplete. Missing file: '$file'");
                    }

                    $contenido = $zip->getFromName($fullPath);
                    $jsonDecoded = json_decode($contenido, true);

                    if (is_null($jsonDecoded)) {
                        throw new \Exception("Invalid JSON in required file: '$file'");
                    }
                }

                // Procesar archivos opcionales
                foreach ($expectedFiles as $file) {

                    $fullPath = $targetPath . $file;

                    if ($zip->locateName($fullPath) !== false) {
                        $contenido = $zip->getFromName($fullPath);
                        $jsonDecoded = json_decode($contenido, true);

                        if (!is_null($jsonDecoded)) {
                            $nombreCampo = pathinfo($file, PATHINFO_FILENAME);
                            $data[$nombreCampo] = $jsonDecoded;
                        }
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

    public function getPendingFollowRequests($user)
    {
        try {
            $usuario = Usuario::where('username', $user)->first();

            if (!$usuario) {
                Log::warning('Error in getPendingFollowRequests: User not found', ['username' => $user]);
                return response()->json([
                    'status' => 404,
                    'message' => 'User not found'
                ], 404);
            }

            $pending = optional($usuario->api_data)->pending_follow_requests;

            // Validación 1: Campo null
            if (is_null($pending)) {
                Log::info('No pending follow requests found', ['username' => $user]);
                return response()->json([
                    'status' => 500,
                    'message' => 'You are all set! You have no pending follow requests waiting to be accepted'
                ], 500);
            }

            // Validación 2: Campo presente pero con estructura inválida
            if (!is_array($pending) || !isset($pending['relationships_follow_requests_sent'])) {
                Log::warning('ZIP data has invalid structure', ['username' => $user, 'pending' => $pending]);
                return response()->json([
                    'status' => 422,
                    'message' => 'ZIP data has invalid structure'
                ], 422);
            }

            // Procesamiento normal
            $array_pendientes = [];

            foreach ($pending['relationships_follow_requests_sent'] as $data) {
                if (!isset($data['string_list_data'][0])) {
                    continue;
                }

                $value = $data['string_list_data'][0]['value'] ?? null;
                $timestamp = isset($data['string_list_data'][0]['timestamp'])
                    ? date('Y-m-d', $data['string_list_data'][0]['timestamp'])
                    : null;
                $link = $data['string_list_data'][0]['href'] ?? null;

                $array_pendientes[] = [
                    "user_name" => $value,
                    "enlace" => $link,
                    "date" => $timestamp
                ];
            }

            return response()->json([
                'status' => 200,
                'pending_requests' => $array_pendientes
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error in getPendingFollowRequests', [
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
