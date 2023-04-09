/*
Plugin Name: Plugin RUGATOR Shortcode
Plugin URI: https://www.torrijos.me
Description: Función personalizada de estadísticas de WordPress con ShortCode.
Author: Rubén Gámez Torrijos
Author URI: https://www.torrijos.me
Version: 1.0.0
Text Domain: plugin-rugator-shortcode
Domain Path: /languages/
*/

// Define la constante con la URL base del repositorio en GitHub
define( 'PLUGIN_RUGATOR_SHORTCODE_GITHUB_URL', 'https://github.com/RubenGamezTorrijos/plugin-rugator-shortcode' );

// Agrega el filtro que comprueba las actualizaciones
add_filter( 'pre_set_site_transient_update_plugins', 'plugin_rugator_shortcode_check_for_updates' );

// Función que comprueba si hay actualizaciones disponibles en GitHub
function plugin_rugator_shortcode_check_for_updates( $transient ) {
    if ( empty( $transient->checked ) ) {
        return $transient;
    }

    // Comprueba si hay actualizaciones en GitHub
    $plugin_data = get_plugin_data( __FILE__ );
    $github_url = PLUGIN_RUGATOR_SHORTCODE_GITHUB_URL . '/releases/latest/download/plugin-rugator-shortcode.zip';
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
add_filter( 'plugin_row_meta', 'plugin_rugator_shortcode_add_update_link', 10, 2 );

// Función que agrega el enlace de actualización a la lista de plugins
function plugin_rugator_shortcode_add_update_link( $links, $file ) {
    if ( $file === plugin_basename( __FILE__ ) ) {
        $plugin_data = get_plugin_data( __FILE__ );
        $github_url = PLUGIN_RUGATOR_SHORTCODE_GITHUB_URL . '/releases/latest';
        $links[] = '<a href="' . $github_url . '">Actualizar a la versión ' . $plugin_data['Version'] . ' desde GitHub</a>';
    }

    return $links;
}
