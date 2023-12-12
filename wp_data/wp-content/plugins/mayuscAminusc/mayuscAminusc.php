<?php
/*
Plugin Name: Conversor Mayúsculas a Minúsculas
Description: Un plugin que permite ingresar, ver y convertir palabras de mayúsculas a minúsculas.
Version: 1.0
Author: Angi Casella
*/

// Acción para agregar el menú del conversor en el panel de administración
add_action('admin_menu', 'agregar_menu_conversor');

function agregar_menu_conversor() {
    add_menu_page('Conversor Mayúsculas a Minúsculas', 'Conversor Mayúsculas a Minúsculas', 'manage_options', 'conversor-mayus-minus', 'mostrar_pagina_conversor');
}

function mostrar_pagina_conversor() {
    global $wpdb;
    $tabla_conversor = $wpdb->prefix . 'conversor_palabras';

    // Procesar el formulario de conversión
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
        $palabras = sanitize_text_field($_POST['palabras']);

        // Convertir a minúsculas
        $palabras_minusculas = strtolower($palabras);

        // Insertar en la base de datos
        $wpdb->insert($tabla_conversor, array('palabras' => $palabras_minusculas));
    }

    // Mostrar formulario de conversión
    ?>
    <div class="wrap">
        <h2>Conversor Mayúsculas a Minúsculas</h2>
        <form method="post" action="">
            <label for="palabras">Palabras:</label>
            <input type="text" name="palabras" required>
            <br>
            <input type="submit" name="submit" value="Convertir a Minúsculas">
        </form>
    </div>

    <?php

    // Mostrar palabras convertidas almacenadas
    $palabras_convertidas = $wpdb->get_results("SELECT * FROM $tabla_conversor", ARRAY_A);
    ?>
    <div class="wrap">
        <h2>Palabras Convertidas Almacenadas</h2>
        <ul>
            <?php foreach ($palabras_convertidas as $entrada) : ?>
                <li><strong><?= esc_html($entrada['palabras']); ?></strong></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php
}

// Acción para crear las tablas en la base de datos al activar el plugin
register_activation_hook(__FILE__, 'crear_tablas');

function crear_tablas() {
    global $wpdb;
    $tabla_conversor = $wpdb->prefix . 'conversor_palabras';

    // Crear tabla del conversor
    $consulta_crear_tabla_conversor = "CREATE TABLE IF NOT EXISTS $tabla_conversor (
        id INT AUTO_INCREMENT PRIMARY KEY,
        palabras TEXT NOT NULL
    )";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($consulta_crear_tabla_conversor);
}

// Agregar filtro para el contenido del post
add_filter('the_content', 'aplicar_conversor_en_contenido');

function aplicar_conversor_en_contenido($content) {
    global $wpdb;
    $tabla_conversor = $wpdb->prefix . 'conversor_palabras';

    // Obtener palabras convertidas almacenadas
    $palabras_convertidas = $wpdb->get_col("SELECT palabras FROM $tabla_conversor");

    // Aplicar conversor en el contenido del post
    foreach ($palabras_convertidas as $palabras) {
        $content = str_ireplace($palabras, strtolower($palabras), $content);
    }

    return $content;
}

// Agregar filtro para el título del post
add_filter('the_title', 'aplicar_conversor_en_titulo');

function aplicar_conversor_en_titulo($title) {
    global $wpdb;
    $tabla_conversor = $wpdb->prefix . 'conversor_palabras';

    // Obtener palabras convertidas almacenadas
    $palabras_convertidas = $wpdb->get_col("SELECT palabras FROM $tabla_conversor");

    // Aplicar conversor en el título del post
    foreach ($palabras_convertidas as $palabras) {
        $title = str_ireplace($palabras, strtolower($palabras), $title);
    }

    return $title;
}
?>
