<?php 

namespace Controllers;

use Classes\Email;
use Model\Usuario;
use MVC\Router;

class LoginController {

    public static function login(Router $router){
        $alertas = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario = new Usuario($_POST);

            $alertas = $usuario->validarLogin();

            if(empty($alertas)){
                //verificar si el usuario existe
                $usuario = Usuario::where('email', $usuario->email);
                if(!$usuario){
                    Usuario::setAlerta('error','El usuario no existe');
                }else{
                    //El usuario existe pero no esta confirmado
                    if (!$usuario->confirmado) {
                        Usuario::setAlerta('error', 'El usuario no está confirmado');
                    }else{
                        //El usuario existe y está confirmado
                        if(password_verify($_POST['password'], $usuario->password)){
                            //Iniciar sesion
                            session_start();
                            $_SESSION['id'] = $usuario->id;
                            $_SESSION['nombre'] = $usuario->nombre;
                            $_SESSION['email'] = $usuario->email;
                            $_SESSION['login'] = true;

                            //redireccionar
                            header('Location: /dashboard');
                            debuguear($_SESSION);
                        }else{
                            Usuario::setAlerta('error', 'Password incorrecto');
                        }
                    }
                }
                

            }
        }

        $alertas = Usuario::getAlertas();
        //render a la vista
        $router->render('auth/login',[
            'titulo' => 'Iniciar Sesión',
            'alertas' => $alertas
        ]);
    }
    public static function logout()
    {
        session_start();
        $_SESSION = [];
        header('Location: /');
    }

    public static function crear(Router $router)
    {
        $alertas = [];
        $usuario = new Usuario;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario->sincronizar($_POST);
            $alertas = $usuario->validarNuevaCuenta();

            if(empty($alertas)){
                $existeUsuario = Usuario::where('email', $usuario->email);
                if ($existeUsuario) {
                    Usuario::setAlerta('error', 'El usuario ya está registrado');
                    $alertas = Usuario::getAlertas();
                }else{
                    //Hashear password
                    $usuario->hashPassword();

                    //Eliminar password2
                    unset($usuario->password2);

                    //Generar token
                    $usuario->crearToken();

                    //Crear usuario
                   $resultado =  $usuario->guardar();

                   //Enviar email
                   $email = new Email($usuario->email, $usuario->nombre, $usuario->token);
                   $email->enviarConfirmacion();
                   if($resultado){
                    header('Location: /mensaje');
                   }
                }
            }

        }
        //render a la vista
        $router->render('auth/crear', [
            'titulo' => 'Crear cuenta',
            'usuario' => $usuario,
            'alertas' => $alertas
        ]);
    }

    public static function olvide(Router $router)
    {
        $alertas = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario = new Usuario($_POST);
            $alertas = $usuario->validarEmail();

            if(empty($alertas)){
                //Buscar usuario
                $usuario = Usuario::where('email', $usuario->email);
                if($usuario && $usuario->confirmado){
                    //Encontre usuario

                    //generar el token nuevo
                    $usuario->crearToken();
                    unset($usuario->password2);

                    //Actualizar usuario
                    $usuario->guardar();

                    //enviar email
                    $email = new Email($usuario->email, $usuario->nombre, $usuario->token);
                    $email->enviarInstrucciones();
                    //alerta de exito
                    Usuario::setAlerta('exito','Hemos enviado las instrucciones a tu correo');
                }else{
                    Usuario::setAlerta('error','El usuario no existe o no está confirmado');
                }
            }
        }
        $alertas = Usuario::getAlertas();
        //Renderizar vista
        $router->render('auth/olvide', [
            'titulo' => 'Recuperar cuenta',
            'alertas' => $alertas
        ]);
    }

    public static function reestablecer(Router $router)
    {
        $token = s($_GET['token']);
        $mostrar = true;
        if(!$token){
            header('Location: /');
        }
        //identificar usaurio con el token
        $usuario = Usuario::where('token', $token);
        if(empty($usuario)){
             Usuario::setAlerta('error', 'Token no válido');
             $mostrar = false;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            //Añadir el nuevo password
            $usuario->sincronizar($_POST);
            
            //valdiar password
            $alertas = $usuario->validarPassword();
            if(empty($alertas)){
                //hashear password
                $usuario->hashPassword();
                unset($usuario->password2);
                //eliminar token
                $usuario->token = '';
                //guardar
                $resultado = $usuario->guardar();
                //redireccionar
                if($resultado){
                    header('Location: /');
                }
            }
        }

        $alertas = Usuario::getAlertas();
        //Renderizar vista
        $router->render('auth/reestablecer', [
            'titulo' => 'Reestablecer password',
            'alertas' => $alertas,
            'mostrar' => $mostrar
        ]);
    }
    public static function mensaje(Router $router)
    {
        $router->render('auth/mensaje', [
            'titulo' => 'Cuenta creada exitosamente'
        ]);
    }
    public static function confirmar(Router $router)
    {
        $token = s($_GET['token']);
        if(!$token) header('Location: /');

        //encontrar usuario por token
        $usuario = Usuario::where('token', $token);
        if(empty($usuario)){
            //No hay usuario con ese token
            Usuario::setAlerta('error', 'Token no válido');
        }else{
            //confirmar cuenta
            $usuario->confirmado = 1;
            $usuario->token = '';
            unset($usuario->password2);

            //Guardar en la base de datos
            $usuario->guardar();
            Usuario::setAlerta('exito', 'Cuenta comprobada exitosamente');
        }
        $alertas = Usuario::getAlertas();
        $router->render('auth/confirmar', [
            'titulo' => 'Confirma tu cuenta UpTask',
            'alertas' => $alertas
        ]);
    }
}