<?php

namespace Controllers;

use Model\Proyecto;
use Model\Usuario;
use MVC\Router;

class DashboardController
{

    public static function index(Router $router)
    {


        session_start();
        isAuth();
        $id = $_SESSION['id'];
        $proyectos = Proyecto::belongsTo('propietarioId', $id);
        //renderizar la vista
        $router->render('dashboard/index', [
            'titulo' => 'Proyectos',
            'proyectos' => $proyectos
        ]);
    }
    public static function crear_proyecto(Router $router)
    {
        session_start();
        isAuth();
        $alertas = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $proyecto = new Proyecto($_POST);

            //validacion
            $alertas = $proyecto->validarProyecto();
            if (empty($alertas)) {
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

    public static function proyecto(Router $router)
    {
        session_start();
        isAuth();
        $token = $_GET['id'];


        if (!$token) header('Location: /dashboard');
        //revisar que la persona que visita el proyecto, es quien lo creo
        $proyecto = Proyecto::where('url', $token);
        if ($proyecto->propietarioId !== $_SESSION['id']) {
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
        $usuario = Usuario::find($_SESSION['id']);
        $alertas = [];

        // debuguear($usuario);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario->sincronizar($_POST);
            $alertas = $usuario->validarPerfil();
            $alertas = $usuario->validarEmail();
            if (empty($alertas)) {
                //guardar usuario
                $existeUsuario = Usuario::where('email', $usuario->email);
                if ($existeUsuario && $existeUsuario->id !== $usuario->id) {
                    //Mensaje de error
                    Usuario::setAlerta('error', 'Email no disponible');
                    $alertas = $usuario->getAlertas();
                } else {
                    //guardar el registro
                    $usuario->guardar();
                    Usuario::setAlerta('exito', 'Cambios guardados correctamente');
                    $alertas = $usuario->getAlertas();
                    //Asignar el nombre nuevo a la barra
                    $_SESSION['nombre'] = $usuario->nombre;
                }
            }
        }
        //renderizar la vista
        $router->render('dashboard/perfil', [
            'titulo' => 'Perfil',
            'alertas' => $alertas,
            'usuario' => $usuario
        ]);
    }

    public static function cambiar_password(Router $router)
    {
        session_start();
        isAuth();
        $alertas = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario = Usuario::find($_SESSION['id']);
            //Sincronizamos los datos 
            $usuario->sincronizar($_POST);
            $alertas = $usuario->nuevo_password();
            
            if(empty($alertas)){
                $resultado = $usuario->comprobar_password();
                if($resultado){
                    //asignar el nuevo password
                    $usuario->password = $usuario->password_nuevo;

                    //Eliminar info no relevante
                    unset($usuario->password_nuevo);
                    unset($usuario->password_actual);

                    //hashear el nuevo password
                    $usuario->hashPassword();

                    //Actualizar
                    $res = $usuario->guardar();
                    if($res){
                        Usuario::setAlerta('exito', 'Password cambiado correctamente');
                        $alertas = $usuario->getAlertas();
                    }
                }else{
                    Usuario::setAlerta('error', 'Password Incorrecto');
                    $alertas = $usuario->getAlertas();
                }
            }
        }

        $router->render('dashboard/cambiar-password', [
            'titulo' => 'Cambiar Password',
            'alertas' => $alertas
        ]);
    }
}
