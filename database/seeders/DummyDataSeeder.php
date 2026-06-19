<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Publicaciones;
use App\Models\Foro;
use App\Models\UsuarioCampusMarket;
use App\Models\categorias_articulos;
use App\Models\Categoria_foros;

class DummyDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Asegurarse de que exista al menos un usuario vendedor/creador
        $vendedor = UsuarioCampusMarket::first();
        if (!$vendedor) {
            $this->command->error('No hay usuarios en usuarios_campus_markets. Por favor crea uno primero.');
            return;
        }

        // Obtener todas las categorías de artículos para asignar de forma aleatoria
        $categoriasIds = categorias_articulos::pluck('Cod_Categoria')->toArray();
        if (empty($categoriasIds)) {
            // Si no hay categorías, crear algunas básicas
            $nombres = ['Electrónica', 'Libros', 'Ropa', 'Papelería', 'Otros'];
            foreach ($nombres as $nombre) {
                $cat = categorias_articulos::create([
                    'Nombre_Categoria' => $nombre,
                    'Descripcion_Categoria' => "Categoría de $nombre"
                ]);
                $categoriasIds[] = $cat->Cod_Categoria;
            }
        }

        $catForo = Categoria_foros::first();
        // Nota: asumiendo que el modelo Categoria_foros existe y se puede crear si no hay.
        // En tu sistema ya deberías tener categorías creadas por otros seeders.

        $this->command->info('Creando 100 Publicaciones Variadas...');

        $adjetivos = ['Nuevo', 'Poco Uso', 'Casi Nuevo', 'Excelente Estado', 'Caja Sellada', 'Ganga', 'Remate', 'Imperdible', 'Vintage', 'Edición Limitada', 'Original', 'Genérico', 'Para repuestos'];
        $items = ['Calculadora Científica', 'Libro de Cálculo', 'Audífonos Bluetooth', 'Mochila Impermeable', 'Tablet Samsung', 'Apuntes de Ecuaciones', 'Guardapolvo M', 'Monitor 24"', 'Kit Arduino', 'Teclado Mecánico', 'Mouse Gamer', 'Silla Ergonómica', 'Termo de Acero', 'Lámpara de escritorio', 'Cuaderno Universitario', 'Juego de Escuadras', 'Bata de Laboratorio', 'Microscopio Estudiantil', 'Lentes de Descanso', 'Disco Duro 1TB', 'Pendrive 64GB', 'Cargador de Laptop', 'Estuche para Lápices', 'Marcadores Resaltadores', 'Calculadora Financiera', 'Libro de Anatomía', 'Libro de Física', 'Calculadora Gráfica', 'Pizarra Magnética', 'Auriculares In-Ear'];
        $marcas = ['Casio', 'Sony', 'HP', 'Dell', 'Logitech', 'Samsung', 'Apple', 'Targus', 'Keychron', 'Razer', 'Corsair', 'Lenovo', 'Asus', 'Acer', 'Xiaomi'];

        for ($i = 0; $i < 100; $i++) {
            $titulo = $items[array_rand($items)] . ' ' . $marcas[array_rand($marcas)] . ' (' . $adjetivos[array_rand($adjetivos)] . ')';

            Publicaciones::create([
                'Titulo_Publicacion' => substr($titulo, 0, 50),
                'Descripcion_Publicacion' => "Vendo $titulo. Ideal para la universidad. Precio charlable. Aprovechen antes de que se venda.",
                'Estado_Publicacion' => true, // Activo
                'Precio_Publicacion' => rand(15, 900) + 0.99,
                'Imagen_Publicacion' => null, // Las cargarás manualmente
                'Cod_Categoria' => $categoriasIds[array_rand($categoriasIds)],
                'ID_Vendedor' => $vendedor->id,
            ]);
        }

        $this->command->info('Creando 30 Foros Variados...');
        $temasForo = [
            '¿Qué profesor recomiendan para Física II?',
            'Grupo de estudio para Cálculo III - Fin de Semana',
            '¿Alguien tiene el temario del examen de programación?',
            'Debate: ¿Cuál es el mejor lenguaje para empezar?',
            'Consejos para sobrevivir a la semana de parciales',
            'Buscando compañeros para el proyecto final de Sistemas',
            '¿Qué tal es la comida en el nuevo comedor universitario?',
            'Ayuda con un problema de Álgebra Lineal',
            'Mejores lugares cerca de la facultad para sacar copias',
            '¿Vale la pena comprar el libro de la materia o con PDF basta?',
            'Reseña de la nueva actualización de Moodle',
            'Recomendaciones de laptops para diseño gráfico',
            'Experiencias haciendo pasantías en empresas tecnológicas',
            '¿Cómo organizan su tiempo con el método Pomodoro?',
            'Dudas sobre el proceso de inscripción del próximo semestre',
            'Debate sobre inteligencia artificial en la educación',
            'Torneo de ajedrez en la biblioteca: Inscripciones',
            '¿Dónde conseguir materiales de arquitectura más baratos?',
            'Club de lectura universitaria: Votación del mes',
            '¿Cómo manejar el estrés y la ansiedad académica?',
            'Buscando roomie cerca del campus principal',
            'Ayuda para instalar Linux en mi portátil',
            'Recomendaciones de cafeterías para estudiar',
            '¿Alguien que haya hecho intercambio estudiantil a España?',
            'Taller gratuito de redacción de tesis (Inscripciones)',
            '¿Qué electivas recomiendan que sean fáciles?',
            'Objetos perdidos: Encontré unas llaves en el aula 205',
            'Debate: ¿Clases virtuales vs presenciales?',
            '¿Cómo conseguir beca de alimentación?',
            'Organizando un torneo de eSports en la universidad'
        ];

        foreach ($temasForo as $tema) {
            Foro::create([
                'Titulo_Foro' => substr($tema, 0, 50),
                'Descripcion_Foro' => "Abro este hilo para hablar sobre: $tema. ¿Qué opinan ustedes? Dejen sus comentarios abajo.",
                'ID_Creador' => $vendedor->id,
                'Estado_Foro' => 1, // Activo
                'Imagen_Foro' => null, // Las cargarás manualmente
                'Cod_Categoria' => $catForo ? ($catForo->Cod_Categoria ?? $catForo->id) : 1, // Usa 1 como fallback si no encuentra la llave
                'tipo_acceso' => 'abierto'
            ]);
        }

        $this->command->info('¡Publicaciones y Foros creados exitosamente!');
    }
}
