<?php

namespace Controllers;

use Model\Proyecto;
use MVC\Router;

class DashboardController {

    public static function index(Router $router){


        session_start();
        isAuth();
        $id = $_SESSION['id'];
        $proyectos = Proyecto::belongsTo('propietarioId', $id);
        //renderizar la vista
        $router->render('dashboard/index',[
            'titulo' => 'Proyectos',
            'proyectos' => $proyectos
        ]);
    }
    public static function crear_proyecto(Router $router)
    {
        session_start();
        isAuth();
        $alertas = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST'){
            $proyecto = new Proyecto($_POST);
            
            //validacion
            $alertas = $proyecto->validarProyecto();
            if(empty($alertas)){
                //generar url unica
                $hash = md5(uniqid());
                $proyecto->url = $hash;
                //almacenar creador del proyecto
                $proyecto->propietarioId = $_SESSION['id'];
                //guardar proyecto
                $proyecto->guardar();

                //redireccionar
                header('Location: /proyecto?id=' . $proyecto->url);

            }
        }


        //renderizar la vista
        $router->render('dashboard/crear-proyecto', [
            'titulo' => 'Crear Proyecto',
            'alertas' => $alertas
        ]);
    }

    public static function proyecto(Router $router){
        session_start();
        isAuth();
        $token = $_GET['id'];

        
        if(!$token) header('Location: /dashboard');
        //revisar que la persona que visita el proyecto, es quien lo creo
        $proyecto = Proyecto::where('url', $token);
        if($proyecto->propietarioId !== $_SESSION['id']){
            header('Location: /dashboard');
        }

        $router->render('dashboard/proyecto', [
            'titulo' => $proyecto->proyecto
        ]);
    }

    public static function perfil(Router $router)
    {


        session_start();
        isAuth();
        //renderizar la vista
        $router->render('dashboard/perfil', [
            'titulo' => 'Perfil'
        ]);
    }
}   