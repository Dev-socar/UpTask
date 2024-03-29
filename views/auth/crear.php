<div class="contenedor crear">
    <?php include_once __DIR__ . '/../templates/nombre-sitio.php'; ?>
    <div class="contenedor-sm">
        <p class="descripcion-pagina">Crear cuenta</p>
        <?php include_once __DIR__ . '/../templates/alertas.php'; ?>
        <form action="/crear-cuenta" method="POST" class="formulario">
            <div class="campo">
                <label for="nombre">Nombre</label>
                <input type="text" id="nombre" placeholder="Tu Nombre" name="nombre" value="<?php echo $usuario->nombre ?>">
            </div>
            <div class="campo">
                <label for="email">Email</label>
                <input type="email" id="email" placeholder="Tu Email" name="email" value="<?php echo $usuario->email ?>">
            </div>
            <div class="campo">
                <label for="password">Password</label>
                <input type="password" id="password" placeholder="Tu Password" name="password">
            </div>
            <div class="campo">
                <label for="password2">Repetir password</label>
                <input type="password" id="password2" placeholder="Repite tu Password" name="password2">
            </div>
            <input type="submit" class="boton" value="Crear cuenta">
        </form>
        <div class="acciones">
            <a href="/crear-cuenta">¿Ya tienes una cuenta? Iniciar Sesión</a>
            <a href="/olvide">¿Olvidaste tu password?</a>
        </div>
    </div><!--.contenedor-sm-->
</div>