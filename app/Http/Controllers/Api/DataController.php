<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Usuario;
use App\Models\Data;

class DataController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:zip|max:15360',
            'username' => 'required|string|max:191'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'errors' => $validator->messages()
            ], 422);
        } else {
            $archivo = $request->file('file');
            $nombreUsuario = $request->username;

            // Procesar el archivo ZIP y guardar los datos en la tabla "data"
            $zip = new \ZipArchive;

            if ($zip->open($archivo)) {
                DB::beginTransaction(); // Iniciar una transacción de base de datos

                try {
                    // Crear un nuevo registro en la tabla "usuarios"
                    $usuario = Usuario::updateOrCreate([
                        'username' => $nombreUsuario
                    ]);

                    // Asociar el registro "data" al usuario recien creado
                    $usuarioId = $usuario->id;

                    $data = []; // array para almacenar todos los campos de "data"

                    for ($i = 0; $i < $zip->numFiles; $i++) {
                        $nombreDocumento = $zip->getNameIndex($i);

                        // quitar la extension al nombre de los JSON
                        $nombreCampo = pathinfo($nombreDocumento, PATHINFO_FILENAME);

                        $contenidoJSON = $zip->getFromName($nombreDocumento);

                        // Decodificar el JSON y convertirlo en un array asociativo
                        $datos = json_decode($contenidoJSON, true);

                        // Agregar los datos al array
                        $data[$nombreCampo] = $datos;
                    }

                    // Crear un nuevo registro en la tabla "data" con todos los campos
                    Data::updateOrCreate(
                        ['usuario_id' => $usuarioId], // Condición para buscar un registro existente
                        [ // Nuevos datos para actualizar
                            'close_friends' => isset($data['close_friends']) ? $data['close_friends'] : null,
                            'followers' => isset($data['followers_1']) ? $data['followers_1'] : null,
                            'following' => isset($data['following']) ? $data['following'] : null,
                            'hide_story_from' => isset($data['hide_story_from']) ? $data['hide_story_from'] : null,
                            'pending_follow_requests' => isset($data['pending_follow_requests']) ? $data['pending_follow_requests'] : null,
                            'recent_follow_requests' => isset($data['recent_follow_requests']) ? $data['recent_follow_requests'] : null,
                            'recently_unfollowed_accounts' => isset($data['recently_unfollowed_accounts']) ? $data['recently_unfollowed_accounts'] : null,
                            'removed_suggestions' => isset($data['removed_suggestions']) ? $data['removed_suggestions'] : null
                        ]
                    );

                    DB::commit(); // Confirmar la transacción si todo fue exitoso

                    return response()->json([
                        'status' => 200,
                        'message' => 'Data stored successfully'
                    ], 200);
                } catch (\Exception $e) {
                    DB::rollback(); // Revertir la transacción en caso de error

                    return response()->json([
                        'status' => 500,
                        'message' => 'Something went wrong!',
                        'error' => [
                            'message' => $e->getMessage(),   // Mensaje de error
                            'file' => $e->getFile(),         // Archivo donde ocurrió la excepción
                            'line' => $e->getLine()          // Línea en la que ocurrió la excepción
                        ]
                    ], 500);
                }
            } else {
                return response()->json([
                    'status' => 500,
                    'message' => 'Something went wrong!'
                ], 500);
            }
        }
    }

    public function getDataFollowing($user)
    {
        $usuario = Usuario::where('username', $user)->first();

        if (!$usuario) {
            return false;
        }

        $following = $usuario->api_data->following;

        $array_seguidos = array();

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
    }

    public function getDataFollowers($user)
    {
        $usuario = Usuario::where('username', $user)->first();

        if (!$usuario) {
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
    }
    
    public function getUnfollowers($user)
    {
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
            return response()->json([
                'status' => 500,
                'message' => 'Something went wrong!'
            ], 500);
        }
    }

    public function getNotFollowing($user)
    {
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
            return response()->json([
                'status' => 404,
                'message' => 'No such files found!'
            ], 404);
        }
    }
}
