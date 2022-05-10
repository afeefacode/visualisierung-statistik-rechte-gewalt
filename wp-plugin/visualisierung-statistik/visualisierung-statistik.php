<?php
/**
 * Plugin Name: Visualisierung Statistik rechte Gewalt
 * Description: Injects the visualisation of right violence widget made by Afeefa Kollektiv
 */
function inject_statsviz($content)
{
    $content = preg_replace('/\[Statistik-Visualisierung\]/', 'WIDGET HERE', $content);
    return $content;
}

add_filter('the_content', 'inject_statsviz');
