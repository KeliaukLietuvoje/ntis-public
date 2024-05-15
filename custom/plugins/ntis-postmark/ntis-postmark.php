<?php
/**
 * Plugin Name:       NTIS Postmark
 * Plugin URI:        https://www.ntis.lt
 * Description:       Postmark email sender
 * Version:           1.0.0
 * Author:            Petras Pauliūnas
 * Author URI:        mailto:petras.pauliunas@gmail.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       ntis
 * Domain Path:       /languages
 */

class NTIS_Postmark
{
    public function __construct()
    {
    }
    public static function send_email($to, $template_id, $template_model)
    {
        $url = 'https://api.postmarkapp.com/email/withTemplate';
        $data = array(
            'From' => 'neatsakyti@ntis.lt',
            'To' => $to,
            'TemplateId' => $template_id,
            'TemplateModel' => $template_model
        );
        $jsonData = json_encode($data);

        $args = array(
            'body'        => $jsonData,
            'headers'     => array(
                'Accept'                => 'application/json',
                'Content-Type'          => 'application/json',
                'X-Postmark-Server-Token' => POSTMARK_API_TOKEN
            ),
            'timeout'     => 15,
            'redirection' => 5,
            'blocking'    => true,
            'httpversion' => '1.0',
            'sslverify'   => false
        );
        $response = wp_remote_post($url, $args);

        if (is_wp_error($response)) {
            return $response->get_error_message();
        } else {
            return wp_remote_retrieve_body($response);
        }
    }
}

new NTIS_Postmark();
