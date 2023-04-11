<?php
/**
 * Plugin Name: Plugin Rugator Statistics ShortCode
 * Plugin URI: https://rugator.com/
 * Description: Plugin para contar estadísticas de Plugins y Temas de WordPress.
 * Version: 1.0.1
 * Author: Rubén Gámez Torrijos
 * Author URI: https://torrijos.me/
 * Text Domain: plugin-rugator-statistics
 * Domain Path: /languages/
 */

// Define la constante con la URL base del repositorio en GitHub
define( 'PLUGIN_RUGATOR_STATISTICS_GITHUB_URL', 'https://github.com/RubenGamezTorrijos/plugin-rugator-statistics' );

// Agrega el filtro que comprueba las actualizaciones
add_filter( 'pre_set_site_transient_update_plugins', 'plugin_rugator_statistics_check_for_updates' );

// Función que comprueba si hay actualizaciones disponibles en GitHub
function plugin_rugator_shortcode_check_for_updates( $transient ) {
    if ( empty( $transient->checked ) ) {
        return $transient;
    }

    // Comprueba si hay actualizaciones en GitHub
    $plugin_data = get_plugin_data( __FILE__ );
    $github_url = PLUGIN_RUGATOR_STATISTICS_GITHUB_URL . '/releases/latest/download/plugin-rugator-statistics.zip';
    $github_response = wp_remote_get( $github_url, array( 'timeout' => 10 ) );

    if ( is_wp_error( $github_response ) ) {
        return $transient;
    }

    $github_version = str_replace( 'v', '', $plugin_data['Version'] );
    $github_zipball = json_decode( wp_remote_retrieve_body( $github_response ), true );

    if ( version_compare( $github_zipball['tag_name'], $github_version, '>' ) ) {
        $transient->response[ plugin_basename( __FILE__ ) ] = array(
            'new_version' => $github_zipball['tag_name'],
            'url' => $github_zipball['html_url'],
            'package' => $github_zipball['zipball_url'],
        );
    }

    return $transient;
}

// Agrega el filtro que agrega el enlace de actualización a la lista de plugins
add_filter( 'plugin_row_meta', 'plugin_rugator_statistics_add_update_link', 10, 2 );

// Función que agrega el enlace de actualización a la lista de plugins
function plugin_rugator_shortcode_add_update_link( $links, $file ) {
    if ( $file === plugin_basename( __FILE__ ) ) {
        $plugin_data = get_plugin_data( __FILE__ );
        $github_url = PLUGIN_RUGATOR_STATISTICS_GITHUB_URL . '/releases/latest';
        $links[] = '<a href="' . $github_url . '">Actualizar a la versión ' . $plugin_data['Version'] . ' desde GitHub</a>';
    }

    return $links;
}

// Agregamos la página del menú
add_action( 'admin_menu', 'plugin_rugator_statistics_menu' );

function plugin_rugator_statistics_menu() {
    add_menu_page(
        __('Plugin Rugator Statistics', 'plugin-rugator-statistics'),
        __('Plugin Rugator Statistics', 'plugin-rugator-statistics'),
        'manage_options',
        'plugin-rugator-statistics',
        'plugin_rugator_statistics_pagina',
        'dashicons-chart-pie'
    );
}

// Agregamos los shortcodes
add_shortcode( 'count_users', 'plugin_rugator_statistics_contar_usuarios' );
add_shortcode( 'count_administrators', 'plugin_rugator_statistics_contar_administradores' );
add_shortcode( 'count_subscribers', 'plugin_rugator_statistics_contar_suscriptores' );
add_shortcode( 'count_editors', 'plugin_rugator_statistics_contar_editores' );
// Agrega los shortcodes para los demás roles de usuario que quieras contar

// Función para contar todos los usuarios
function plugin_rugator_statistics_contar_usuarios() {
    $cuentas = count_users();
    return $cuentas['total_users'];
}

// Función para contar administradores
function plugin_rugator_statistics_contar_administradores() {
    $cuentas = count_users();
    return $cuentas['avail_roles']['administrator'];
}

// Función para contar suscriptores
function plugin_rugator_statistics_contar_suscriptores() {
    $cuentas = count_users();
    return $cuentas['avail_roles']['subscriber'];
}

// Función para contar editores
function plugin_rugator_statistics_contar_editores() {
    $cuentas = count_users();
    return $cuentas['avail_roles']['editor'];
}

// Función para agregar la página del plugin
function plugin_rugator_statistics_pagina() {
    echo '<h2>' . __('Cantidad de usuarios por rol', 'plugin-rugator-statistics') . '</h2>';
    echo '<ul>';
    foreach ( get_editable_roles() as $rol => $details ) {
        echo '<li>' . translate_user_role( $details['name'] ) . ': ' . $details['count'] . '</li>';
    }
    echo '</ul>';
    
    // Mostramos el formulario de opciones del plugin
    plugin_rugator_statistics_mostrar_opciones();
}

// Función para mostrar el formulario de opciones del plugin
function plugin_rugator_statistics_mostrar_opciones() {
    ?>
    <h2><?php _e( 'Opciones del plugin', 'plugin-rugator-statistics' ); ?></h2>
    <form method="post" action="options.php">
        <?php settings_fields( 'plugin-rugator-statistics-opciones' ); ?>
        <?php do_settings_sections( 'plugin-rugator-statistics-opciones' ); ?>
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row"><?php _e( 'Contar todos los usuarios', 'plugin-rugator-statistics' ); ?></th>
                    <td>
                        <?php $opciones = get_option( 'plugin_rugator_statistics_opciones' ); ?>
                        <label>
                            <input type="checkbox" name="plugin_rugator_statistics_opciones[count_all]" value="1" <?php checked( isset( $opciones['count_all'] ) ); ?>>
                            <?php _e( 'Activar', 'plugin-rugator-statistics' ); ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e( 'Contar administradores', 'plugin-rugator-statistics' ); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="plugin_rugator_statistics_opciones[count_administrators]" value="1" <?php checked( isset( $opciones['count_administrators'] ) ); ?>>
                            <?php _e( 'Activar', 'plugin-rugator-statistics' ); ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e( 'Contar suscriptores', 'plugin-rugator-statistics' ); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="plugin_rugator_statistics_opciones[count_subscribers]" value="1" <?php checked( isset( $opciones['count_subscribers'] ) ); ?>>
                            <?php _e( 'Activar', 'plugin-rugator-statistics' ); ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e( 'Contar editores', 'plugin-rugator-statistics' ); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="plugin_rugator_statistics_opciones[count_editors]" value="1" <?php checked( isset( $opciones['count_editors'] ) ); ?>>
                            <?php _e( 'Activar', 'plugin-rugator-statistics' ); ?>
                        </label>
                    </td>
                </tr>
                <!-- Agrega más opciones para los demás roles de usuario que quieras contar -->
            </tbody>
        </table>
        <?php submit_button(); ?>
    </form>
    <?php
}

// Registramos las opciones del plugin
add_action( 'admin_init', 'plugin_rugator_statistics_registrar_opciones' );

function plugin_rugator_statistics_registrar_opciones() {
    register_setting( 'plugin-rugator-statistics-opciones', 'plugin_rugator_statistics_opciones' );
}

// Función para obtener las opciones activadas en el panel de administración
function plugin_rugator_statistics_opciones_activadas() {
    $opciones = get_option( 'plugin_rugator_statistics_opciones' );
    $opciones_activadas = array();

    if ( isset( $opciones['count_all'] ) && $opciones['count_all'] == 1 ) {
        $opciones_activadas['count_all'] = true;
    }

    if ( isset( $opciones['count_administrators'] ) && $opciones['count_administrators'] == 1 ) {
        $opciones_activadas['count_administrators'] = true;
    }

    if ( isset( $opciones['count_subscribers'] ) && $opciones['count_subscribers'] == 1 ) {
        $opciones_activadas['count_subscribers'] = true;
    }

    if ( isset( $opciones['count_editors'] ) && $opciones['count_editors'] == 1 ) {
        $opciones_activadas['count_editors'] = true;
    }

    // Agrega más opciones para los demás roles de usuario que quieras contar

    return $opciones_activadas;
}


// Función de desactivación del plugin
function plugin_rugator_statistics_desactivar() {
    // Eliminar opciones guardadas en la tabla wp_options
    delete_option('plugin_rugator_statistics_opciones');
}
register_deactivation_hook(__FILE__, 'plugin_rugator_statistics_desactivar');

// Función de desinstalación del plugin
function plugin_rugator_statistics_desinstalar() {
    // Eliminar las opciones guardadas en la tabla wp_options
    delete_option('plugin_rugator_statistics_opciones');
}

// Registrar la función de desinstalación
register_uninstall_hook(__FILE__, 'plugin_rugator_statistics_desinstalar');
