<?php

namespace Model;

class Tarea extends ActiveRecord {
    protected static $tabla = 'tareas';
    protected static $columnasDB = ['id', 'nombre', 'proyectoId', 'estado'];

    public $id;
    public $nombre;
    public $proyectoId;
    public $estado;

    public function __construct($args = [])
    {
        $this->id = $args['id'] ?? null;
        $this->nombre = $args['nombre'] ?? '';
        $this->proyectoId = $args['proyectoId'] ?? '';
        $this->estado = $args['estado'] ?? 0;
    }
}