<?php

define('VISUALISIERUNG_DEV', false);
define('VISUALISIERUNG_DEV_PORT', 8081);

/**
 * Plugin Name: Visualisierung Statistik rechte Gewalt
 * Description: Injects the visualisation of right violence widget made by Afeefa Kollektiv
 */
function inject_statsviz($content)
{
    $vueCode = '';

    // $data_file_url = plugins_url('./data.json', __FILE__ );
    // $data = file_get_contents($data_file_url);

    $fileName = "stats_data.json";
    $pluginDirectory = plugin_dir_path( __FILE__ );
    $filePath = $pluginDirectory . $fileName;
    $data = file_get_contents($filePath);

    $data = 'test';

    if (VISUALISIERUNG_DEV) {
        $VISUALISIERUNG_DEV_PORT = VISUALISIERUNG_DEV_PORT;
        $vueCode .= <<<EOF
                <script defer src="http://localhost:{$VISUALISIERUNG_DEV_PORT}/vue/js/app.js"></script><link href="http://localhost:{$VISUALISIERUNG_DEV_PORT}/vue/css/app.css" rel="stylesheet">
            EOF;
    }

    $vueCode .= <<<EOF
        <div id="statsViz" class="statsVizContainer">
            <raa-stats-viz teaser-button-color="#f0861b" fullscreen-margin-top="0"></raa-stats-viz>
        </div>
        <script>
            window.addEventListener('load', function() {
                new Vue({
                    el: document.getElementById('statsViz')
                })
            }, false);
        </script>
        EOF;

    $content = preg_replace('/\[Statistik-Visualisierung\]/', $vueCode, $content);
    return $content;
}

add_filter('the_content', 'inject_statsviz', 0);

function visualisierung_statistik_enqueue_script()
{
    wp_enqueue_script('vue_js', plugin_dir_url(__FILE__) . 'vue.min.js');
    wp_enqueue_script('visualisierung_statistik_js', plugin_dir_url(__FILE__) . 'visualisierung-statistik.js');
    wp_enqueue_style('style-name', plugin_dir_url(__FILE__) . 'visualisierung-statistik.css');
}

if (!VISUALISIERUNG_DEV) {
    add_action('wp_enqueue_scripts', 'visualisierung_statistik_enqueue_script');
}
