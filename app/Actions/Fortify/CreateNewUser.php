<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Models\UsuarioCampusMarket;
use App\Models\Categorias;
use App\Models\Carrera;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => $this->passwordRules(),
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',
        ])->validate();

        // Crear el usuario
        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
        ]);

        // Intentar asignar universidad/carrera y categoría por defecto si vienen en el input
        // Se espera que el formulario envíe 'Cod_Universidad' y/o 'Cod_Carrera' o 'carrera' (nombre)
        $codCarrera = $input['Cod_Carrera'] ?? null;
        $codUniversidad = $input['Cod_Universidad'] ?? null;

        $categoriaDefaultId = null;

        // Intentar mapear primero por Cod_Carrera (ID) usando config
        $mapping = config('categories_by_carrera', []);
        if (!empty($codCarrera) && isset($mapping[$codCarrera])) {
            $mapped = $mapping[$codCarrera];
            if (is_array($mapped)) {
                $mapped = $mapped[0];
            }
            // Si el mapping por ID devuelve un entero, lo interpretamos como Cod_Categoria
            if (is_int($mapped) || ctype_digit((string) $mapped)) {
                $categoriaDefaultId = (int) $mapped;
            } elseif (is_string($mapped)) {
                // mapping devuelve nombre de categoria
                $categoria = Categorias::where('Nombre_Categoria', $mapped)->first();
                if ($categoria) {
                    $categoriaDefaultId = $categoria->Cod_Categoria;
                }
            }
        }

        // Si no se resolvió por ID, intentar usar el nombre de la carrera (campo 'carrera' o buscando en la tabla carreras)
        if (empty($categoriaDefaultId)) {
            $carreraName = $input['carrera'] ?? null;
            if (empty($carreraName) && !empty($codCarrera)) {
                $carreraModel = Carrera::find($codCarrera);
                $carreraName = $carreraModel->Nombre_Carrera ?? null;
            }

            if (!empty($carreraName)) {
                // Buscar en el config por clave exacta; también intentamos slug
                if (isset($mapping[$carreraName])) {
                    $catName = $mapping[$carreraName];
                } else {
                    // probar con slug
                    $slug = Str::slug($carreraName, ' ');
                    foreach ($mapping as $key => $val) {
                        if (is_string($key) && Str::slug($key, ' ') === $slug) {
                            $catName = $val;
                            break;
                        }
                    }
                }

                if (!empty($catName)) {
                    // Si el mapping devuelve un array tomamos el primero
                    if (is_array($catName)) {
                        $catName = $catName[0];
                    }
                    // Si catName es un entero (string numeric), usamos como id
                    if (ctype_digit((string) $catName)) {
                        $categoriaDefaultId = (int) $catName;
                    } else {
                        $categoria = Categorias::where('Nombre_Categoria', $catName)->first();
                        if ($categoria) {
                            $categoriaDefaultId = $categoria->Cod_Categoria;
                        }
                    }
                }
            }
        }

        // Crear registro perfil/usuario campus market
        UsuarioCampusMarket::create([
            'user_id' => $user->id,
            'Cod_Carrera' => $codCarrera,
            'Cod_Universidad' => $codUniversidad,
            'Cod_Categoria_Default' => $categoriaDefaultId,
        ]);

        return $user;
    }
}
